<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;

class ExtractArticleController extends Controller
{
    /**
     * Extrae información y metadatos de un archivo PDF
     */
    public function extract(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pdf' => ['required', 'file', 'mimetypes:application/pdf', 'max:25600'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo PDF inválido',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            if (!$request->hasFile('pdf')) {
                throw new \Exception('No se recibió el archivo PDF en la solicitud.');
            }

            $file = $request->file('pdf');
            if (!$file->isValid()) {
                $msg = method_exists($file, 'getErrorMessage') ? $file->getErrorMessage() : ('Código: ' . $file->getError());
                throw new \Exception('El archivo PDF no es válido: ' . $msg);
            }

            // Extraer texto utilizando el parser de PDF
            $parser   = new Parser();
            $pathname = method_exists($file, 'getPathname') ? $file->getPathname() : null;

            if (!empty($pathname) && is_file($pathname)) {
                $pdf = $parser->parseFile($pathname);
            } else {
                $contents = $file->get();
                if (empty($contents)) {
                    throw new \Exception('No se pudo acceder al contenido del PDF.');
                }
                $pdf = $parser->parseContent($contents);
            }

            $text = $pdf->getText();
            if (empty($text)) {
                throw new \Exception('No se pudo extraer texto del PDF. Puede ser un PDF escaneado (basado en imágenes).');
            }

            // Recortar texto para optimizar tokens en la API de OpenAI
            $text = substr($text, 0, 15000);
            Log::info('📄 Texto extraído del PDF', ['length' => strlen($text)]);

            // Obtener catálogos de la base de datos para mapeo semántico en el backend
            $catalogos = $this->getCatalogos();

            // Obtener credenciales de OpenAI
            $openaiKey = config('services.openai.key');
            if (!$openaiKey) {
                throw new \Exception('API Key de OpenAI no configurada. Define OPENAI_API_KEY en .env y services.php.');
            }

            $prompt = $this->buildPrompt($text, $catalogos);

            try {
                $content = $this->callOpenAIWithRetry($prompt, $openaiKey);
                $extracted = $this->parseAndValidate($content, $catalogos);

                return response()->json([
                    'success'    => true,
                    'data'       => $extracted['data'],
                    'confidence' => $extracted['confidence'],
                    'issues'     => $extracted['issues'],
                    'message'    => 'Metadata extraída exitosamente y mapeada con la base de datos',
                ]);
            } catch (\Exception $aiEx) {
                // Fallback heurístico si la API falla (ej. cuota excedida) o devuelve formato incorrecto
                Log::warning('⚠️ Fallback heurístico activado', ['reason' => $aiEx->getMessage()]);

                $heuristic = $this->heuristicFallback($text, $catalogos);
                return response()->json([
                    'success'    => true,
                    'data'       => $heuristic['data'],
                    'confidence' => $heuristic['confidence'],
                    'issues'     => array_merge(
                        ['Se usó extracción heurística por indisponibilidad del servicio de IA (p. ej. cuota agotada o fallo en la comunicación).'],
                        $heuristic['issues']
                    ),
                    'message'    => 'Extracción heurística aplicada como respaldo',
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('❌ Error en proceso de extracción', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el documento: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene todos los listados/catálogos de referencia de la base de datos
     */
    private function getCatalogos(): array
    {
        $areas = DB::table('area_investigacion')
            ->select('ID_AREA as id', 'NOMBRE_AREA as nombre')
            ->orderBy('NOMBRE_AREA')
            ->get()
            ->toArray();

        $tipos = DB::table('tipos_proyecto')
            ->select('ID_TIPO as id', 'NOMBRE_TIPO as nombre')
            ->orderBy('NOMBRE_TIPO')
            ->get()
            ->toArray();

        $estados = DB::table('estado_proyecto')
            ->select('ID_ESTADO as id', 'NOMBRE_ESTADO as nombre')
            ->orderBy('NOMBRE_ESTADO')
            ->get()
            ->toArray();

        $grupos = DB::table('grupos_investigacion')
            ->select('ID_GRUPO as id', 'NOMBRE_GRUPO as nombre')
            ->orderBy('NOMBRE_GRUPO')
            ->get()
            ->toArray();

        $investigadores = DB::table('investigador as i')
            ->join('persona as p', 'p.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->select('i.ID_INVESTIGADOR as id', DB::raw('CONCAT(p.NOMBRES, " ", p.APELLIDOS) as nombre'))
            ->orderBy('nombre')
            ->get()
            ->toArray();

        return [
            'areas'          => $areas,
            'tipos'          => $tipos,
            'estados'        => $estados,
            'grupos'         => $grupos,
            'investigadores' => $investigadores,
        ];
    }

    /**
     * Realiza la llamada a la API de OpenAI con reintentos exponenciales
     */
    private function callOpenAIWithRetry(string $prompt, string $openaiKey): string
    {
        $http = Http::withHeaders([
            'Authorization' => 'Bearer ' . $openaiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(120);

        // Descomentar si hay problemas de certificados SSL en el entorno local de desarrollo (Windows/Laragon)
        // $http = $http->withOptions(['verify' => false]);

        $maxAttempts = 3;
        $backoffMs   = 400; // exponencial

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $response = $http->post('https://api.openai.com/v1/chat/completions', [
                'model'           => 'gpt-4o-mini',
                'temperature'     => 0.1, // temperatura baja para mayor exactitud y menor creatividad
                'max_tokens'      => 1500,
                'response_format' => ['type' => 'json_object'],
                'messages'        => [
                    [
                        'role'    => 'system',
                        'content' => 'Eres un agente experto que extrae y clasifica metadatos de proyectos académicos. Debes responder estrictamente con un objeto JSON válido.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

            if ($response->successful()) {
                $json = $response->json();
                if (!isset($json['choices'][0]['message']['content'])) {
                    throw new \Exception('Respuesta vacía o inválida de OpenAI.');
                }
                return $json['choices'][0]['message']['content'];
            }

            $status = $response->status();
            $body   = substr($response->body(), 0, 1000);
            Log::error('❌ Error API OpenAI', ['intento' => $attempt, 'status' => $status, 'body' => $body]);

            // Reintentar si el error es de servidor o límite de tasa de llamadas
            if (in_array($status, [429, 500, 502, 503, 504])) {
                if ($attempt < $maxAttempts) {
                    usleep($backoffMs * 1000);
                    $backoffMs *= 2;
                    continue;
                }

                $msg = $status === 429
                    ? 'Límite de solicitudes de OpenAI alcanzado (429).'
                    : 'Servicio de OpenAI no disponible temporalmente (' . $status . ').';

                throw new \Exception($msg);
            }

            // Errores del lado del cliente no recuperables
            $msg = match ($status) {
                400 => 'Solicitud inválida enviada a OpenAI (400).',
                401 => 'No autorizado (401). Verifica tu API Key de OpenAI.',
                404 => 'El modelo de OpenAI no se encontró (404).',
                default => 'Fallo al comunicarse con OpenAI: Código de estado ' . $status,
            };
            throw new \Exception($msg);
        }

        throw new \Exception('Sin respuesta exitosa de OpenAI tras múltiples intentos.');
    }

    /**
     * Construye el prompt de OpenAI incluyendo los catálogos reales de la base de datos
     */
    private function buildPrompt(string $text, array $catalogos): string
    {
        $areasJson   = json_encode($catalogos['areas'], JSON_UNESCAPED_UNICODE);
        $tiposJson   = json_encode($catalogos['tipos'], JSON_UNESCAPED_UNICODE);
        $estadosJson = json_encode($catalogos['estados'], JSON_UNESCAPED_UNICODE);
        $gruposJson  = json_encode($catalogos['grupos'], JSON_UNESCAPED_UNICODE);
        $investigadoresJson = json_encode($catalogos['investigadores'], JSON_UNESCAPED_UNICODE);

        return "Analiza el siguiente fragmento de texto extraído de un documento PDF de investigación y realiza las siguientes tareas:

1. Extrae los metadatos generales (Título, Año del proyecto, Fecha de Inicio, Fecha de Fin, Resumen/Descripción, Presupuesto, Palabras Clave).
   - Para el Resumen/Descripción, extrae el abstract/resumen de la investigación (preferiblemente en español).
   - Para el Presupuesto, busca cifras presupuestales o montos de financiamiento. Si no se detalla en el texto, escribe null.
   - Para las Palabras Clave (Keywords), extrae la lista de palabras clave del documento. Devuélvelas como una sola cadena de texto separadas por comas (ej. 'inteligencia artificial, educación, minería de datos'). Si no se mencionan en el texto, escribe null.

2. Realiza un mapeo semántico inteligente entre las entidades del documento y las opciones reales del catálogo del sistema que se presentan a continuación. Compara la información extraída con el listado y devuelve exclusivamente los IDs correctos si existe una coincidencia clara (confianza > 80%). Tolera pequeñas diferencias en el formato de nombres, tildes, abreviaturas académicas (como 'Dr.', 'Mg.') u orden de apellidos.

CATÁLOGOS DEL SISTEMA (Formato JSON [id, nombre]):
- Áreas de Investigación:
{$areasJson}

- Tipos de Proyecto:
{$tiposJson}

- Estados de Proyecto:
{$estadosJson}

- Grupos de Investigación:
{$gruposJson}

- Investigadores Registrados (Úsalo para mapear tanto el 'autor_principal' como los 'coautores'):
{$investigadoresJson}

REGLAS ESPECÍFICAS DE MAPEO:
- 'id_area': Selecciona el ID de la lista de Áreas de Investigación. Si no se encuentra un área relacionada, pon null.
- 'id_tipo': Selecciona el ID de la lista de Tipos de Proyecto. Si no se indica o no coincide, pon null.
- 'id_estado': Selecciona el ID de la lista de Estados de Proyecto. Si no coincide, pon null.
- 'id_autor': Identifica al autor principal de la investigación y mapea su ID del listado de Investigadores Registrados. Si el autor principal del documento NO se encuentra en este listado, pon null en 'id_autor' y coloca su nombre completo en el campo 'raw_autor_principal'. Si sí está registrado, pon 'raw_autor_principal' en null.
- 'id_grupo': Selecciona el ID de la lista de Grupos de Investigación si se menciona alguno. Si no, pon null.
- 'coautores': Identifica a los coautores en el texto y devuelve una matriz con sus respectivos IDs del listado de Investigadores Registrados. Si un coautor no está en la lista de Investigadores Registrados, NO agregues su ID a este arreglo.
- 'coautores_no_registrados': Si identificas coautores en el documento que NO se encuentran en la lista de Investigadores Registrados, agrega sus nombres completos a este arreglo.

Devuelve estrictamente un objeto JSON válido con la siguiente estructura:
{
  \"data\": {
    \"titulo\": \"Título exacto de la investigación (string)\",
    \"anio_proyecto\": 2024,
    \"fecha_inicio\": \"YYYY-MM-DD o null\",
    \"fecha_fin\": \"YYYY-MM-DD o null\",
    \"descripcion\": \"Texto de resumen o abstract (string)\",
    \"presupuesto\": 15000.00,
    \"keywords\": \"palabra clave 1, palabra clave 2, palabra clave 3 o null\",
    \"id_area\": 1,
    \"id_tipo\": 2,
    \"id_estado\": 3,
    \"id_autor\": 5,
    \"raw_autor_principal\": \"Nombre del Autor No Registrado o null\",
    \"id_grupo\": null,
    \"coautores\": [2, 7],
    \"coautores_no_registrados\": [\"Nombre Coautor No Registrado 1\"]
  },
  \"confidence\": {
    \"titulo\": 0.95,
    \"autor\": 0.90
  },
  \"issues\": []
}

No agregues explicaciones adicionales, no uses bloques de código ```json o markdown. Devuelve solo el JSON puro.

Texto del documento:
----------------------------------
{$text}
----------------------------------";
    }

    /**
     * Parsea, limpia y valida el JSON de OpenAI, asegurando integridad de IDs
     */
    private function parseAndValidate(string $content, array $catalogos): array
    {
        $clean = trim($content);
        $clean = preg_replace('/^```json\s*/i', '', $clean);
        $clean = preg_replace('/```\s*$/', '', $clean);

        $data = json_decode($clean, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('El formato devuelto por la IA no es un JSON válido: ' . json_last_error_msg());
        }

        if (!isset($data['data'])) {
            $data = ['data' => $data, 'confidence' => [], 'issues' => []];
        }

        if (!isset($data['confidence'])) {
            $data['confidence'] = [];
        }
        if (!isset($data['issues'])) {
            $data['issues'] = [];
        }

        // Listar los IDs reales de la base de datos para verificación
        $validAreas   = collect($catalogos['areas'])->pluck('id')->toArray();
        $validTipos   = collect($catalogos['tipos'])->pluck('id')->toArray();
        $validEstados = collect($catalogos['estados'])->pluck('id')->toArray();
        $validGrupos  = collect($catalogos['grupos'])->pluck('id')->toArray();
        $validInvestigadores = collect($catalogos['investigadores'])->pluck('id')->toArray();

        // Extraer los IDs devueltos por la IA
        $idArea   = isset($data['data']['id_area']) ? (int) $data['data']['id_area'] : null;
        $idTipo   = isset($data['data']['id_tipo']) ? (int) $data['data']['id_tipo'] : null;
        $idEstado = isset($data['data']['id_estado']) ? (int) $data['data']['id_estado'] : null;
        $idAutor  = isset($data['data']['id_autor']) ? (int) $data['data']['id_autor'] : null;
        $idGrupo  = isset($data['data']['id_grupo']) ? (int) $data['data']['id_grupo'] : null;
        $coautores = is_array($data['data']['coautores'] ?? null) ? array_map('intval', $data['data']['coautores']) : [];
        $coautoresNoRegistrados = is_array($data['data']['coautores_no_registrados'] ?? null) ? $data['data']['coautores_no_registrados'] : [];
        $rawAutorPrincipal      = $this->str($data['data']['raw_autor_principal'] ?? null, 150);
        $keywords               = $this->str($data['data']['keywords'] ?? null, 500);

        // Sanear y verificar la existencia de los IDs contra nuestros catálogos para evitar alucinaciones
        if ($idArea !== null && !in_array($idArea, $validAreas)) $idArea = null;
        if ($idTipo !== null && !in_array($idTipo, $validTipos)) $idTipo = null;
        if ($idEstado !== null && !in_array($idEstado, $validEstados)) $idEstado = null;
        if ($idAutor !== null && !in_array($idAutor, $validInvestigadores)) $idAutor = null;
        if ($idGrupo !== null && !in_array($idGrupo, $validGrupos)) $idGrupo = null;

        // Filtrar coautores no válidos
        $coautores = array_filter($coautores, fn($id) => in_array($id, $validInvestigadores));
        $coautores = array_values(array_unique($coautores));

        // Generar alertas inteligentes basadas en los investigadores no emparejados
        $issues = $data['issues'] ?? [];

        if ($idAutor === null) {
            if (!empty($rawAutorPrincipal)) {
                $issues[] = "El autor principal '{$rawAutorPrincipal}' no está registrado en el sistema. Asegúrate de registrarlo o seleccionarlo manualmente.";
            } else {
                $issues[] = "No se pudo identificar un autor principal registrado en el sistema.";
            }
        }

        if (!empty($coautoresNoRegistrados)) {
            $nombresCoautores = implode(', ', $coautoresNoRegistrados);
            $issues[] = "Se detectaron coautores en el documento que no están en el sistema: {$nombresCoautores}.";
        }

        $normalized = [
            'data' => [
                'titulo'                    => $this->str($data['data']['titulo'] ?? null, 200) ?: 'Sin título',
                'anio_proyecto'             => (int) ($data['data']['anio_proyecto'] ?? date('Y')),
                'fecha_inicio'              => $this->date($data['data']['fecha_inicio'] ?? null),
                'fecha_fin'                 => $this->date($data['data']['fecha_fin'] ?? null),
                'descripcion'               => $this->str($data['data']['descripcion'] ?? null, 5000),
                'presupuesto'               => isset($data['data']['presupuesto']) ? (float) $data['data']['presupuesto'] : null,
                'keywords'                  => $keywords,
                'id_area'                   => $idArea,
                'id_tipo'                   => $idTipo,
                'id_estado'                 => $idEstado,
                'id_autor'                  => $idAutor,
                'raw_autor_principal'       => $rawAutorPrincipal,
                'id_grupo'                  => $idGrupo,
                'coautores'                 => $coautores,
                'coautores_no_registrados'  => $coautoresNoRegistrados,
            ],
            'confidence' => $data['confidence'] ?? [],
            'issues'     => array_values(array_unique($issues)),
        ];

        return $normalized;
    }

    /**
     * Extracción heurística de respaldo inteligente basada en análisis semántico de catálogos y expresiones regulares (sin IA)
     */
    private function heuristicFallback(string $text, array $catalogos): array
    {
        $textFirstPart = mb_substr($text, 0, 4000); // Tomar los primeros 4000 caracteres (donde se concentran los metadatos)
        $lines = preg_split('/\r\n|\r|\n/', $textFirstPart);
        $lines = array_map('trim', $lines);
        $lines = array_values(array_filter($lines, fn($l) => $l !== ''));

        // 1. ESCANEO SEMÁNTICO CONTRA CATÁLOGOS DE LA BASE DE DATOS
        $idAutor = null;
        $coautores = [];
        $coautoresNoRegistrados = [];
        $rawAutorPrincipal = null;

        // Buscar investigadores de la BD presentes en el texto del PDF
        $investigadoresEncontrados = [];
        foreach ($catalogos['investigadores'] as $inv) {
            $nombre = trim($inv->nombre);
            // Creamos un patrón regex insensible a mayúsculas/minúsculas y tildes
            $nombreRegex = $this->prepareNameRegex($nombre);
            
            if (preg_match($nombreRegex, $textFirstPart, $matches, PREG_OFFSET_CAPTURE)) {
                $pos = $matches[0][1];
                $investigadoresEncontrados[] = [
                    'id'     => $inv->id,
                    'nombre' => $nombre,
                    'pos'    => $pos
                ];
            }
        }

        // Ordenar los investigadores según su posición de aparición en el documento
        usort($investigadoresEncontrados, fn($a, $b) => $a['pos'] <=> $b['pos']);

        if (!empty($investigadoresEncontrados)) {
            // El primero en aparecer se asume como el autor principal
            $autorPrincipalData = array_shift($investigadoresEncontrados);
            $idAutor = $autorPrincipalData['id'];
            
            // Los investigadores restantes se consideran coautores
            foreach ($investigadoresEncontrados as $invEnc) {
                $coautores[] = $invEnc['id'];
            }
        }

        // Escaneo inteligente de Áreas de Investigación en el texto
        $idArea = null;
        foreach ($catalogos['areas'] as $area) {
            $nombreArea = trim($area->nombre);
            if (stripos($textFirstPart, $nombreArea) !== false) {
                $idArea = $area->id;
                break; // Tomar la primera coincidencia
            }
        }

        // Escaneo inteligente de Grupos de Investigación en el texto
        $idGrupo = null;
        foreach ($catalogos['grupos'] as $grupo) {
            $nombreGrupo = trim($grupo->nombre);
            if (stripos($textFirstPart, $nombreGrupo) !== false) {
                $idGrupo = $grupo->id;
                break;
            }
        }

        // Escaneo inteligente de Tipos de Proyecto en el texto
        $idTipo = null;
        foreach ($catalogos['tipos'] as $tipo) {
            $nombreTipo = trim($tipo->nombre);
            if (stripos($textFirstPart, $nombreTipo) !== false) {
                $idTipo = $tipo->id;
                break;
            }
        }

        // Escaneo inteligente de Estados de Proyecto
        $idEstado = null;
        foreach ($catalogos['estados'] as $estado) {
            $nombreEstado = trim($estado->nombre);
            if (stripos($textFirstPart, $nombreEstado) !== false) {
                $idEstado = $estado->id;
                break;
            }
        }
        if (!$idEstado && !empty($catalogos['estados'])) {
            // Asignar el primer estado del catálogo por defecto
            $idEstado = $catalogos['estados'][0]->id;
        }

        // 2. EXTRACCIÓN DE METADATOS MEDIANTE EXPRESIONES REGULARES
        // Título: buscar la primera línea que no corresponda a ruido de encabezados universitarios
        $titulo = null;
        $palabrasRuido = ['universidad', 'facultad', 'vicerrectorado', 'issn', 'vol.', 'no.', 'doi', 'revista', 'página', 'http', 'www.', 'editorial', 'researchgate', 'anales', 'científica'];
        
        foreach ($lines as $index => $l) {
            $len = mb_strlen($l);
            if ($len >= 15 && $len <= 200) {
                $esRuido = false;
                foreach ($palabrasRuido as $ruido) {
                    if (stripos($l, $ruido) !== false) {
                        $esRuido = true;
                        break;
                    }
                }
                if (!$esRuido) {
                    $titulo = $l;
                    // Intentar unir con la línea inmediata siguiente para títulos de dos líneas
                    if (isset($lines[$index + 1])) {
                        $nextLine = $lines[$index + 1];
                        $nextLen  = mb_strlen($nextLine);
                        if ($nextLen >= 10 && $nextLen <= 120 && !preg_match('/^(autor|resumen|abstract|introducción|e-mail|correo)/i', $nextLine)) {
                            $esRuidoSiguiente = false;
                            foreach ($palabrasRuido as $ruido) {
                                if (stripos($nextLine, $ruido) !== false) {
                                    $esRuidoSiguiente = true;
                                    break;
                                }
                            }
                            if (!$esRuidoSiguiente) {
                                $titulo .= ' ' . $nextLine;
                            }
                        }
                    }
                    break;
                }
            }
        }

        // Año: escanear el primer número de 4 dígitos congruente (ej. 1980 - Año actual + 1)
        $anio = (int) date('Y');
        $anioActual = (int) date('Y');
        if (preg_match_all('/\b(19\d{2}|20\d{2})\b/', $textFirstPart, $matchesAnio)) {
            foreach ($matchesAnio[0] as $posAnio) {
                $valAnio = (int) $posAnio;
                if ($valAnio >= 1970 && $valAnio <= ($anioActual + 1)) {
                    $anio = $valAnio;
                    break;
                }
            }
        }

        // Presupuesto: buscar montos monetarios después de palabras clave
        $presupuesto = null;
        if (preg_match('/(?:presupuesto|financiamiento|monto|costo|total)\s*(?:de)?\s*(?:S\/?\.?|Soles|\$)\s*(\d{1,3}(?:[.,]\d{3})*(?:\.\d{2})?)/i', $textFirstPart, $mPres)) {
            $cleanedMonto = str_replace([',', ' '], '', $mPres[1]);
            $presupuesto = (float) $cleanedMonto;
        }

        // Resumen / Abstract
        $descripcion = null;
        if (preg_match('/(?:resumen|abstract|summary)\b[\s\:\-]*([\s\S]{100,1500})/i', $textFirstPart, $mDesc)) {
            $descripcion = trim($mDesc[1]);
            $descChunks  = preg_split('/\n\s*\n/', $descripcion);
            $descripcion = $descChunks[0] ?? $descripcion;
            if (stripos($descripcion, 'abstract') !== false) {
                $descripcion = preg_split('/\babstract\b/i', $descripcion)[0];
            }
            $descripcion = mb_substr(trim($descripcion), 0, 1500);
        }

        if (empty($descripcion)) {
            $descripcion = mb_substr($textFirstPart, 0, 1000);
        }

        // Palabras Clave (Keywords): buscar secciones con regex
        $keywords = null;
        if (preg_match('/(?:palabras\s+claves?|keywords?|key\s+words?)\s*[:\-]?\s*([^\n.]{10,250})/i', $textFirstPart, $mKey)) {
            $keywords = trim($mKey[1]);
            $keywords = preg_replace('/^[:\-\s]+/', '', $keywords);
            $keywords = trim($keywords);
        }

        // Si no encontramos ningún investigador registrado en la BD, buscamos nombres de autores por regex
        if ($idAutor === null) {
            foreach ($lines as $l) {
                if (preg_match('/^(autor(es)?|authors?)\s*[:\-]?(.*)$/i', $l, $m)) {
                    $possible = trim($m[3] ?? '');
                    if ($possible !== '') {
                        $chunks = preg_split('/[,;]|\sy\s/i', $possible);
                        $rawAutorPrincipal = trim($chunks[0]);
                        $rawAutorPrincipal = preg_replace('/[*¹²³⁴\d]|\(.*?\)/', '', $rawAutorPrincipal);
                        $rawAutorPrincipal = trim($rawAutorPrincipal);
                        
                        // Agregar el resto como coautores no registrados
                        for ($k = 1; $k < count($chunks); $k++) {
                            $coautorNom = trim($chunks[$k]);
                            $coautorNom = preg_replace('/[*¹²³⁴\d]|\(.*?\)/', '', $coautorNom);
                            $coautorNom = trim($coautorNom);
                            if (!empty($coautorNom)) {
                                $coautoresNoRegistrados[] = $coautorNom;
                            }
                        }
                        break;
                    }
                }
            }
        }

        $data = [
            'titulo'                    => $this->str($titulo ?? 'Sin título', 200),
            'anio_proyecto'             => $anio,
            'fecha_inicio'              => null,
            'fecha_fin'                 => null,
            'descripcion'               => $this->str($descripcion, 2500),
            'presupuesto'               => $presupuesto,
            'keywords'                  => $this->str($keywords, 500),
            'id_area'                   => $idArea,
            'id_tipo'                   => $idTipo,
            'id_estado'                 => $idEstado,
            'id_autor'                  => $idAutor,
            'raw_autor_principal'       => $rawAutorPrincipal,
            'id_grupo'                  => $idGrupo,
            'coautores'                 => $coautores,
            'coautores_no_registrados'  => $coautoresNoRegistrados,
        ];

        $confidence = [
            'titulo'          => $titulo ? 0.6 : 0.1,
            'anio_proyecto'   => 0.5,
            'autor_principal' => ($idAutor !== null) ? 0.8 : (($rawAutorPrincipal !== null) ? 0.5 : 0.1),
            'area'            => ($idArea !== null) ? 0.7 : 0.1,
            'keywords'        => $keywords ? 0.7 : 0.1,
        ];

        // Generar issues orientadores
        $issues = [];
        if ($idAutor !== null) {
            $issues[] = "Autor principal asociado automáticamente mediante coincidencia de catálogo.";
        } else if ($rawAutorPrincipal !== null) {
            $issues[] = "Autor principal detectado '{$rawAutorPrincipal}' no registrado en el catálogo.";
        }
        
        if (!empty($coautores)) {
            $issues[] = "Se mapearon automáticamente " . count($coautores) . " coautores registrados.";
        }
        
        if (!empty($coautoresNoRegistrados)) {
            $nombresCoautores = implode(', ', $coautoresNoRegistrados);
            $issues[] = "Coautores detectados no registrados: {$nombresCoautores}.";
        }

        $issues[] = "Se aplicó la extracción heurística avanzada de respaldo. Por favor, verifica todos los campos.";

        return [
            'data'       => $data,
            'confidence' => $confidence,
            'issues'     => array_values(array_unique($issues)),
        ];
    }

    /**
     * Prepara una expresión regular para buscar un nombre ignorando tildes y mayúsculas
     */
    private function prepareNameRegex(string $name): string
    {
        $name = preg_quote($name, '/');
        // Reemplazar vocales con clases de caracteres para ignorar tildes
        $replacements = [
            'a' => '[aáàäâ]',
            'e' => '[eéèëê]',
            'i' => '[iíìïî]',
            'o' => '[oóòöô]',
            'u' => '[uúùüû]',
            'A' => '[AÁÀÄÂaáàäâ]',
            'E' => '[EÉÈËÊeéèëê]',
            'I' => '[IÍÌÏÎiíìïî]',
            'O' => '[OÓÒÖÔoóòöô]',
            'U' => '[UÚÙÜÛuúùüû]',
        ];
        
        $pattern = str_replace(array_keys($replacements), array_values($replacements), $name);
        return '/' . $pattern . '/i';
    }

    /**
     * Corta una cadena a una longitud máxima dada
     */
    private function str($value, int $maxLength)
    {
        if (!$value) return null;
        return substr(trim((string) $value), 0, $maxLength);
    }

    /**
     * Valida y formatea una cadena de fecha
     */
    private function date($value)
    {
        if (!$value) return null;
        $value = trim((string) $value);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }
        return null;
    }
}
