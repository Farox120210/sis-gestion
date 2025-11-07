<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class ExtractArticleController extends Controller
{
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

            // Extraer texto de forma robusta
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
                throw new \Exception('No se pudo extraer texto del PDF. Puede ser un PDF escaneado.');
            }

            // Recortar para no exceder
            $text = substr($text, 0, 15000);
            Log::info('📄 Texto extraído del PDF', ['length' => strlen($text)]);

            // Llamada a OpenAI con reintentos y fallback heurístico
            $openaiKey = config('services.openai.key');
            if (!$openaiKey) {
                throw new \Exception('API Key de OpenAI no configurada. Define OPENAI_API_KEY en .env y services.php.');
            }

            $prompt = $this->buildPrompt($text);

            try {
                $content = $this->callOpenAIWithRetry($prompt, $openaiKey);
                $extracted = $this->parseAndValidate($content);

                return response()->json([
                    'success'    => true,
                    'data'       => $extracted['data'],
                    'confidence' => $extracted['confidence'],
                    'issues'     => $extracted['issues'],
                    'message'    => 'Metadata extraída exitosamente',
                ]);
            } catch (\Exception $aiEx) {
                // Si fue por cuota/429 u otro fallo, aplicar heurística
                Log::warning('⚠️ Fallback heurístico activado', ['reason' => $aiEx->getMessage()]);

                $heuristic = $this->heuristicFallback($text);
                return response()->json([
                    'success'    => true,
                    'data'       => $heuristic['data'],
                    'confidence' => $heuristic['confidence'],
                    'issues'     => array_merge(
                        ['Se usó extracción heurística por indisponibilidad del servicio de IA (p. ej. cuota agotada).'],
                        $heuristic['issues']
                    ),
                    'message'    => 'Extracción heurística aplicada',
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('❌ Error en extracción', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function callOpenAIWithRetry(string $prompt, string $openaiKey): string
    {
        $http = Http::withHeaders([
            'Authorization' => 'Bearer ' . $openaiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(120);

        // Para entornos Windows con SSL en local, si necesitas: 
        // $http = $http->withOptions(['verify' => false]);

        $maxAttempts = 3;
        $backoffMs   = 400; // exponencial

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $response = $http->post('https://api.openai.com/v1/chat/completions', [
                'model'           => 'gpt-4o-mini',
                'temperature'     => 0.2,
                'max_tokens'      => 1700,
                'response_format' => ['type' => 'json_object'],
                'messages'        => [
                    [
                        'role'    => 'system',
                        'content' => 'Eres un agente que extrae metadata de proyectos académicos. Responde SOLO con JSON válido (sin markdown).',
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
                    throw new \Exception('Respuesta inválida de OpenAI (sin contenido).');
                }
                return $json['choices'][0]['message']['content'];
            }

            $status = $response->status();
            $body   = substr($response->body(), 0, 2000);
            Log::error('❌ Error OpenAI', ['status' => $status, 'body' => $body]);

            // Reintentar en códigos recuperables
            if (in_array($status, [429, 500, 502, 503, 504])) {
                if ($attempt < $maxAttempts) {
                    usleep($backoffMs * 1000);
                    $backoffMs *= 2;
                    continue;
                }

                $msg = $status === 429
                    ? 'OpenAI: límite de uso alcanzado (429).'
                    : 'OpenAI: servicio temporalmente no disponible (' . $status . ').';

                throw new \Exception($msg);
            }

            // Errores no recuperables
            $msg = match ($status) {
                400 => 'OpenAI: solicitud inválida (400). Revisa los campos enviados.',
                401 => 'OpenAI: no autorizado (401). Verifica OPENAI_API_KEY.',
                404 => 'OpenAI: modelo no encontrado (404).',
                default => 'Error al comunicarse con OpenAI: ' . $status,
            };
            throw new \Exception($msg);
        }

        throw new \Exception('OpenAI: sin respuesta satisfactoria tras reintentos.');
    }

    private function buildPrompt(string $text): string
    {
        return "Extrae la metadata del siguiente documento académico y devuelve SOLO JSON válido en este formato exacto:

{
  \"data\": {
    \"titulo\": \"string\",
    \"anio_proyecto\": 2024,
    \"fecha_inicio\": \"2024-01-01\",
    \"fecha_fin\": \"2024-12-31\",
    \"descripcion\": \"string\",
    \"presupuesto\": 100000.00,
    \"area_investigacion\": \"string\",
    \"tipo_proyecto\": \"tesis\",
    \"estado_proyecto\": \"en revision\",
    \"autor_principal\": \"string\",
    \"rol\": \"autor\",
    \"grupo_investigacion\": \"string\",
    \"coautores\": [\"nombre1\", \"nombre2\"]
  },
  \"confidence\": {
    \"titulo\": 0.95,
    \"anio_proyecto\": 0.90
  },
  \"issues\": [\"advertencia si existe\"]
}

Documento:

" . $text;
    }

    private function parseAndValidate(string $content): array
    {
        $clean = trim($content);
        $clean = preg_replace('/^```json\\s*/', '', $clean);
        $clean = preg_replace('/```\\s*$/', '', $clean);

        $data = json_decode($clean, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON inválido: ' . json_last_error_msg());
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

        $normalized = [
            'data' => [
                'titulo'              => $this->str($data['data']['titulo'] ?? null, 200) ?: 'Sin título',
                'anio_proyecto'       => (int) ($data['data']['anio_proyecto'] ?? date('Y')),
                'fecha_inicio'        => $this->date($data['data']['fecha_inicio'] ?? null),
                'fecha_fin'           => $this->date($data['data']['fecha_fin'] ?? null),
                'descripcion'         => $this->str($data['data']['descripcion'] ?? null, 5000),
                'presupuesto'         => isset($data['data']['presupuesto']) ? (float) $data['data']['presupuesto'] : null,
                'area_investigacion'  => $this->str($data['data']['area_investigacion'] ?? null, 150),
                'tipo_proyecto'       => $this->str($data['data']['tipo_proyecto'] ?? null, 80),
                'estado_proyecto'     => $this->str($data['data']['estado_proyecto'] ?? null, 80),
                'autor_principal'     => $this->str($data['data']['autor_principal'] ?? null, 150),
                'rol'                 => $this->str($data['data']['rol'] ?? null, 100),
                'grupo_investigacion' => $this->str($data['data']['grupo_investigacion'] ?? null, 150),
                'coautores'           => is_array($data['data']['coautores'] ?? null) ? $data['data']['coautores'] : [],
            ],
            'confidence' => $data['confidence'],
            'issues'     => $data['issues'],
        ];

        return $normalized;
    }

    private function heuristicFallback(string $text): array
    {
        $lines = preg_split('/\\r\\n|\\r|\\n/', $text);
        $lines = array_map('trim', $lines);
        $lines = array_values(array_filter($lines, fn($l) => $l !== ''));

        // Título: primera línea no muy larga ni todo mayúscula rara
        $titulo = null;
        foreach ($lines as $l) {
            $len = mb_strlen($l);
            if ($len >= 8 && $len <= 180) {
                $titulo = $l;
                break;
            }
        }

        // Año: buscar la primera coincidencia razonable 19xx/20xx
        $anio = (int) date('Y');
        if (preg_match('/\\b(19\\d{2}|20\\d{2})\\b/', $text, $m)) {
            $anio = (int) $m[1];
        }

        // Autor principal heurístico: línea que empiece con "Autor" o "Autores"
        $autor = null;
        foreach ($lines as $l) {
            if (preg_match('/^(autor(es)?|authors?)\\s*[:\\-]?(.*)$/i', $l, $m)) {
                $possible = trim($m[3] ?? '');
                if ($possible !== '') {
                    // tomar el primero separado por coma/; o "y"
                    $chunks = preg_split('/[,;]|\\sy\\s/i', $possible);
                    $autor = trim($chunks[0]);
                    break;
                }
            }
        }

        // Coautores simples si hay una línea con "Autores"
        $coautores = [];
        if (!$autor) {
            // tal vez hay múltiples nombres en una línea con "Autores:"
            foreach ($lines as $l) {
                if (preg_match('/^autores?\\s*[:\\-](.*)$/i', $l, $m)) {
                    $list = preg_split('/[,;]|\\sy\\s/i', $m[1]);
                    $list = array_map(fn($v) => trim($v), $list);
                    $list = array_values(array_filter($list, fn($v) => $v !== ''));
                    if (!empty($list)) {
                        $autor = array_shift($list);
                        $coautores = $list;
                        break;
                    }
                }
            }
        }

        // Resumen/Abstract
        $descripcion = null;
        if (preg_match('/\\b(resumen|abstract)\\b[\\s\\:\\-]*([\\s\\S]{100,1500})/i', $text, $m)) {
            $descripcion = trim($m[2]);
            // Cortar en el primer doble salto de línea grande
            $descripcion = preg_split('/\\n\\s*\\n/', $descripcion)[0] ?? $descripcion;
            $descripcion = substr($descripcion, 0, 1200);
        } else {
            // Tomar un bloque de ~600 chars desde el inicio como fallback
            $descripcion = substr($text, 0, 600);
        }

        // Estado/Tipo/Área: desconocidos
        $data = [
            'titulo'              => $this->str($titulo ?? 'Sin título', 200),
            'anio_proyecto'       => $anio,
            'fecha_inicio'        => null,
            'fecha_fin'           => null,
            'descripcion'         => $this->str($descripcion, 2000),
            'presupuesto'         => null,
            'area_investigacion'  => null,
            'tipo_proyecto'       => null,
            'estado_proyecto'     => null,
            'autor_principal'     => $this->str($autor, 150),
            'rol'                 => null,
            'grupo_investigacion' => null,
            'coautores'           => $coautores,
        ];

        $confidence = [
            'titulo'         => $titulo ? 0.6 : 0.3,
            'anio_proyecto'  => 0.6,
            'autor_principal'=> $autor ? 0.5 : 0.2,
            'descripcion'    => 0.7,
        ];

        $issues = [];
        if (!$titulo) $issues[] = 'No se pudo determinar con certeza el título.';
        if (!$autor)  $issues[] = 'No se ubicó el autor principal.';
        $issues[] = 'Verifica manualmente los campos sugeridos por la heurística.';

        return [
            'data'       => $data,
            'confidence' => $confidence,
            'issues'     => $issues,
        ];
    }

    private function str($value, int $maxLength)
    {
        if (!$value) return null;
        return substr(trim((string) $value), 0, $maxLength);
    }

    private function date($value)
    {
        if (!$value) return null;
        $value = trim((string) $value);
        if (preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $value)) {
            return $value;
        }
        return null;
    }
}
