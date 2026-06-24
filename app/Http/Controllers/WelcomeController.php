<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WelcomeController extends Controller
{
    // =========================================================
    // 1. MÉTODO PRINCIPAL (PÁGINA DE INICIO Y RESULTADOS)
    // =========================================================
    public function index(Request $request)
    {
        // =========================================================
        // 1. FILTRO DE SEGURIDAD GLOBAL (Cero proyectos en desarrollo)
        // =========================================================
        // Creamos una subconsulta base que solo tenga los IDs de los estados públicos
        $estadosPublicosIds = DB::table('estado_proyecto')
            ->where('NOMBRE_ESTADO', 'NOT LIKE', '%desarrollo%')
            ->pluck('ID_ESTADO');

        // =========================================================
        // 2. ESTADÍSTICAS PÚBLICAS REORGANIZADAS (3 Tarjetas principales)
        // =========================================================
        $stats = [
            'total'       => DB::table('proyecto')->whereIn('ID_ESTADO', $estadosPublicosIds)->count(),
            
            'publicados'  => DB::table('proyecto')
                                ->join('estado_proyecto', 'proyecto.ID_ESTADO', '=', 'estado_proyecto.ID_ESTADO')
                                ->where('estado_proyecto.NOMBRE_ESTADO', 'LIKE', '%publicado%')
                                ->count(),
                                
            'sustentados' => DB::table('proyecto')
                                ->join('estado_proyecto', 'proyecto.ID_ESTADO', '=', 'estado_proyecto.ID_ESTADO')
                                ->where('estado_proyecto.NOMBRE_ESTADO', 'LIKE', '%sustentado%')
                                ->count(),
                                
            'grupos_qty'  => DB::table('grupos_investigacion')->count(), // Usaremos esto para la 4ta tarjeta
        ];

        // =========================================================
        // 3. RECOLECCIÓN DE DATOS PARA LOS DROPDOWNS DEL TOP BAR
        // =========================================================
        $menuTipos   = DB::table('tipos_proyecto')->get();
        $menuGrupos  = DB::table('grupos_investigacion')->get();
        $menuAreas   = DB::table('area_investigacion')->get();
        $menuAnios   = DB::table('proyecto')->select('ANIO')->whereIn('ID_ESTADO', $estadosPublicosIds)->distinct()->orderBy('ANIO', 'desc')->get();
        
        // Autores que tienen al menos una investigación pública (para evitar listas vacías gigantes)
        $menuAutores = DB::table('proyecto')
            ->join('investigador', 'proyecto.ID_AUTOR', '=', 'investigador.ID_INVESTIGADOR')
            ->join('persona', 'investigador.ID_PERSONA', '=', 'persona.ID_PERSONA')
            ->whereIn('proyecto.ID_ESTADO', $estadosPublicosIds)
            ->select('investigador.ID_INVESTIGADOR as id', 'persona.NOMBRES', 'persona.APELLIDOS')
            ->distinct()
            ->orderBy('persona.APELLIDOS', 'asc')
            ->get();

        // =========================================================
        // 4. CONSULTA FILTRADA PARA EL PÚBLICO
        // =========================================================
        $query = DB::table('proyecto')
            ->whereIn('proyecto.ID_ESTADO', $estadosPublicosIds) // Restricción estricta
            ->leftJoin('estado_proyecto', 'proyecto.ID_ESTADO', '=', 'estado_proyecto.ID_ESTADO')
            ->leftJoin('tipos_proyecto', 'proyecto.ID_TIPO', '=', 'tipos_proyecto.ID_TIPO')
            ->leftJoin('area_investigacion', 'proyecto.ID_AREA', '=', 'area_investigacion.ID_AREA')
            ->leftJoin('grupos_investigacion', 'proyecto.ID_GRUPO', '=', 'grupos_investigacion.ID_GRUPO')
            ->leftJoin('investigador', 'proyecto.ID_AUTOR', '=', 'investigador.ID_INVESTIGADOR')
            ->leftJoin('persona', 'investigador.ID_PERSONA', '=', 'persona.ID_PERSONA')
            ->select(
                'proyecto.ID_PROYECTO as id',
                'proyecto.TITULO as titulo',
                'proyecto.DESCRIPCION_PROYECTO as resumen',
                'proyecto.KEYWORDS as keywords',
                'estado_proyecto.NOMBRE_ESTADO as estado',
                'tipos_proyecto.NOMBRE_TIPO as tipo',
                'area_investigacion.NOMBRE_AREA as area',
                'grupos_investigacion.NOMBRE_GRUPO as grupo',
                'persona.NOMBRES as autor_nombres',
                'persona.APELLIDOS as autor_apellidos',
                'proyecto.created_at'
            );

        // Captura de filtros desde la URL (Navbar o Buscador)
        $isFiltering = false;

        if ($request->filled('q')) {
            $isFiltering = true;
            $termino = trim($request->q);
            
            // Dividir en palabras individuales (de al menos 2 caracteres) para una búsqueda de coincidencia flexible
            $palabras = array_filter(explode(' ', $termino), function($word) {
                return strlen(trim($word)) >= 2;
            });
            
            $query->where(function($subQuery) use ($termino, $palabras) {
                // 1. Coincidencia por la frase completa en cualquiera de los campos clave
                $subQuery->where('proyecto.TITULO', 'LIKE', '%' . $termino . '%')
                          ->orWhere('proyecto.DESCRIPCION_PROYECTO', 'LIKE', '%' . $termino . '%')
                          ->orWhere('proyecto.KEYWORDS', 'LIKE', '%' . $termino . '%')
                          ->orWhere('persona.NOMBRES', 'LIKE', '%' . $termino . '%')
                          ->orWhere('persona.APELLIDOS', 'LIKE', '%' . $termino . '%')
                          ->orWhere('area_investigacion.NOMBRE_AREA', 'LIKE', '%' . $termino . '%')
                          ->orWhere('grupos_investigacion.NOMBRE_GRUPO', 'LIKE', '%' . $termino . '%')
                          ->orWhere('tipos_proyecto.NOMBRE_TIPO', 'LIKE', '%' . $termino . '%');
                
                // 2. Coincidencia por términos individuales para mayor aproximación
                foreach ($palabras as $palabra) {
                    $subQuery->orWhere(function($wordQuery) use ($palabra) {
                        $wordQuery->where('proyecto.TITULO', 'LIKE', '%' . $palabra . '%')
                                 ->orWhere('proyecto.DESCRIPCION_PROYECTO', 'LIKE', '%' . $palabra . '%')
                                 ->orWhere('proyecto.KEYWORDS', 'LIKE', '%' . $palabra . '%')
                                 ->orWhere('persona.NOMBRES', 'LIKE', '%' . $palabra . '%')
                                 ->orWhere('persona.APELLIDOS', 'LIKE', '%' . $palabra . '%')
                                 ->orWhere('area_investigacion.NOMBRE_AREA', 'LIKE', '%' . $palabra . '%')
                                 ->orWhere('grupos_investigacion.NOMBRE_GRUPO', 'LIKE', '%' . $palabra . '%')
                                 ->orWhere('tipos_proyecto.NOMBRE_TIPO', 'LIKE', '%' . $palabra . '%');
                    });
                }
            });
        }
        if ($request->filled('grupo')) { $isFiltering = true; $query->where('proyecto.ID_GRUPO', $request->grupo); }
        if ($request->filled('tipo')) { $isFiltering = true; $query->where('proyecto.ID_TIPO', $request->tipo); }
        if ($request->filled('area')) { $isFiltering = true; $query->where('proyecto.ID_AREA', $request->area); }
        if ($request->filled('anio')) { $isFiltering = true; $query->where('proyecto.ANIO', $request->anio); }
        if ($request->filled('autor')) { $isFiltering = true; $query->where('proyecto.ID_AUTOR', $request->autor); }

        // Si el usuario no filtra nada, traemos solo los últimos 10 para la vitrina del carrusel
        if (!$isFiltering) {
            $query->limit(10);
        }

        $publicaciones = $query->orderBy('proyecto.created_at', 'desc')->get();

        // Variables empaquetadas comunes para las vistas
        $viewData = compact('publicaciones', 'menuTipos', 'menuGrupos', 'menuAreas', 'menuAnios', 'menuAutores');

        // =========================================================
        // 5. REDIRECCIÓN INTELIGENTE DE VISTAS
        // =========================================================
        if ($isFiltering) {
            return view('portal.resultados', $viewData);
        } else {
            $recientes = $publicaciones; 
            return view('portal.inicio', array_merge(compact('stats', 'recientes'), $viewData));
        }
    }

   // =========================================================
    // 2. MÉTODO PARA LA PÁGINA DE DIRECTORIO DE AUTORES (ACTUALIZADO)
    // =========================================================
    public function autores(Request $request)
    {
        // 1. Variables necesarias para que el Top Bar funcione en esta vista
        $estadosPublicosIds = DB::table('estado_proyecto')
            ->where('NOMBRE_ESTADO', 'NOT LIKE', '%desarrollo%')
            ->pluck('ID_ESTADO');

        $menuTipos   = DB::table('tipos_proyecto')->get();
        $menuGrupos  = DB::table('grupos_investigacion')->get();
        $menuAreas   = DB::table('area_investigacion')->get();
        $menuAnios   = DB::table('proyecto')->select('ANIO')->whereIn('ID_ESTADO', $estadosPublicosIds)->distinct()->orderBy('ANIO', 'desc')->get();
        $menuAutores = DB::table('proyecto')
            ->join('investigador', 'proyecto.ID_AUTOR', '=', 'investigador.ID_INVESTIGADOR')
            ->join('persona', 'investigador.ID_PERSONA', '=', 'persona.ID_PERSONA')
            ->whereIn('proyecto.ID_ESTADO', $estadosPublicosIds)
            ->select('investigador.ID_INVESTIGADOR as id', 'persona.NOMBRES', 'persona.APELLIDOS')
            ->distinct()
            ->orderBy('persona.APELLIDOS', 'asc')
            ->get();

        // 2. CONSULTA PRINCIPAL: Traer TODOS los investigadores con su conteo de proyectos públicos
        $query = DB::table('investigador')
            ->join('persona', 'investigador.ID_PERSONA', '=', 'persona.ID_PERSONA')
            ->leftJoin('proyecto', function($join) use ($estadosPublicosIds) {
                $join->on('investigador.ID_INVESTIGADOR', '=', 'proyecto.ID_AUTOR')
                     ->whereIn('proyecto.ID_ESTADO', $estadosPublicosIds);
            })
            ->select(
                'investigador.ID_INVESTIGADOR as id',
                'persona.NOMBRES as nombres',
                'persona.APELLIDOS as apellidos',
                DB::raw('COUNT(proyecto.ID_PROYECTO) as total_proyectos')
            )
            ->groupBy('investigador.ID_INVESTIGADOR', 'persona.NOMBRES', 'persona.APELLIDOS');

        // 3. Filtro del buscador de la interfaz de autores
        if ($request->filled('search_autor')) {
            $buscar = $request->search_autor;
            $query->where(function($q) use ($buscar) {
                $q->where('persona.NOMBRES', 'LIKE', '%' . $buscar . '%')
                  ->orWhere('persona.APELLIDOS', 'LIKE', '%' . $buscar . '%');
            });
        }

       
       $todosLosAutores = $query->orderBy('persona.APELLIDOS', 'asc')->paginate(9);

        return view('portal.autores', compact('todosLosAutores', 'menuTipos', 'menuGrupos', 'menuAreas', 'menuAnios', 'menuAutores'));
    }

    // =========================================================
    // 3. MÉTODO PARA LA PÁGINA DE DETALLE DE UNA PUBLICACIÓN
    // =========================================================
    public function showPublicacion($id)
    {
        // 1. Filtro de seguridad: solo estados públicos
        $estadosPublicosIds = DB::table('estado_proyecto')
            ->where('NOMBRE_ESTADO', 'NOT LIKE', '%desarrollo%')
            ->pluck('ID_ESTADO');

        // 2. Consulta principal del proyecto con unión a la publicación para el PDF
        $pub = DB::table('proyecto')
            ->whereIn('proyecto.ID_ESTADO', $estadosPublicosIds)
            ->leftJoin('proyecto_revista as pr', 'pr.ID_PROYECTO', '=', 'proyecto.ID_PROYECTO')
            ->leftJoin('estado_proyecto', 'proyecto.ID_ESTADO', '=', 'estado_proyecto.ID_ESTADO')
            ->leftJoin('tipos_proyecto', 'proyecto.ID_TIPO', '=', 'tipos_proyecto.ID_TIPO')
            ->leftJoin('area_investigacion', 'proyecto.ID_AREA', '=', 'area_investigacion.ID_AREA')
            ->leftJoin('grupos_investigacion', 'proyecto.ID_GRUPO', '=', 'grupos_investigacion.ID_GRUPO')
            ->leftJoin('investigador', 'proyecto.ID_AUTOR', '=', 'investigador.ID_INVESTIGADOR')
            ->leftJoin('persona', 'investigador.ID_PERSONA', '=', 'persona.ID_PERSONA')
            ->where('proyecto.ID_PROYECTO', $id)
            ->select([
                'proyecto.ID_PROYECTO as id',
                'proyecto.TITULO as titulo',
                'proyecto.DESCRIPCION_PROYECTO as descripcion',
                'proyecto.KEYWORDS as keywords',
                'proyecto.ANIO as anio',
                'proyecto.FECHA_INICIO as fecha_inicio',
                'proyecto.FECHA_FIN as fecha_fin',
                'proyecto.PRESUPUESTO as presupuesto',
                'estado_proyecto.NOMBRE_ESTADO as estado',
                'tipos_proyecto.NOMBRE_TIPO as tipo',
                'area_investigacion.NOMBRE_AREA as area',
                'grupos_investigacion.NOMBRE_GRUPO as grupo',
                'pr.ID_PROYECTO_REVISTA as id_publicacion',
                'pr.ID_ARCHIVO_PRINCIPAL as id_archivo_principal',
                DB::raw("CONCAT(persona.NOMBRES, ' ', persona.APELLIDOS) as autor_principal"),
                'proyecto.created_at',
            ])
            ->first();

        if (!$pub) {
            abort(404);
        }

        // 3. Todos los autores/investigadores asociados al proyecto
        $autores = DB::table('proyecto_investigador as pi')
            ->join('investigador as i', 'i.ID_INVESTIGADOR', '=', 'pi.ID_INVESTIGADOR')
            ->join('persona as per', 'per.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->where('pi.ID_PROYECTO', $pub->id)
            ->select([
                'i.ID_INVESTIGADOR',
                'pi.ROL_EN_PROYECTO',
                DB::raw("CONCAT(per.NOMBRES, ' ', per.APELLIDOS) as nombre"),
            ])
            ->orderBy('per.APELLIDOS')
            ->get();

        // 4. Archivo principal
        $archivoPrincipal = null;
        if (!empty($pub->id_archivo_principal)) {
            $archivoPrincipal = DB::table('proyecto_revista_archivos')
                ->where('ID_ARCHIVO', $pub->id_archivo_principal)
                ->select([
                    'ID_ARCHIVO',
                    'NOMBRE_ORIGINAL',
                    'URL',
                    'PATH',
                    'MIME_TYPE',
                    'SIZE_BYTES',
                    'VERSION',
                    'FECHA_SUBIDA',
                ])
                ->first();
        }

        // 5. Variables necesarias para el Top Bar
        $menuTipos   = DB::table('tipos_proyecto')->get();
        $menuGrupos  = DB::table('grupos_investigacion')->get();
        $menuAreas   = DB::table('area_investigacion')->get();
        $menuAnios   = DB::table('proyecto')->select('ANIO')->whereIn('ID_ESTADO', $estadosPublicosIds)->distinct()->orderBy('ANIO', 'desc')->get();
        $menuAutores = DB::table('proyecto')
            ->join('investigador', 'proyecto.ID_AUTOR', '=', 'investigador.ID_INVESTIGADOR')
            ->join('persona', 'investigador.ID_PERSONA', '=', 'persona.ID_PERSONA')
            ->whereIn('proyecto.ID_ESTADO', $estadosPublicosIds)
            ->select('investigador.ID_INVESTIGADOR as id', 'persona.NOMBRES', 'persona.APELLIDOS')
            ->distinct()
            ->orderBy('persona.APELLIDOS', 'asc')
            ->get();

        return view('portal.show', compact('pub', 'autores', 'menuTipos', 'menuGrupos', 'menuAreas', 'menuAnios', 'menuAutores', 'archivoPrincipal'));
    }

    /**
     * Visualiza el PDF de la publicación de manera pública
     */
    public function verArchivoPublico($id, $file)
    {
        $path = "publicaciones/{$id}/{$file}";

        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->response($path);
    }

    /**
     * Descarga el PDF de la publicación de manera pública
     */
    public function descargarArchivoPublico($id, $file)
    {
        $path = "publicaciones/{$id}/{$file}";

        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($path);
    }
}