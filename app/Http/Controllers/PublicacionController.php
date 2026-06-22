<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PublicacionController extends Controller
{
    /**
     * Listado de publicaciones
     */
    public function index(Request $request)
    {
        // El index no lleva candado porque queremos que todos (Lector, Publicador, Admin) puedan ver la lista.
        
        $tipo     = $request->input('tipo');   // filtro por tipo de investigación (opcional)
        $busqueda = $request->input('q');      // filtro de texto general (opcional)

        $publicacionesQuery = DB::table('proyecto_revista as pr')
            ->join('proyecto as p', 'p.ID_PROYECTO', '=', 'pr.ID_PROYECTO')
            ->join('revistas as r', 'r.ID_REVISTA', '=', 'pr.ID_REVISTA')
            ->leftJoin('area_investigacion as a', 'a.ID_AREA', '=', 'p.ID_AREA')
            ->leftJoin('grupos_investigacion as g', 'g.ID_GRUPO', '=', 'p.ID_GRUPO')
            ->leftJoin('proyecto_investigador as pi', 'pi.ID_PROYECTO', '=', 'p.ID_PROYECTO')
            ->leftJoin('investigador as i', 'i.ID_INVESTIGADOR', '=', 'pi.ID_INVESTIGADOR')
            ->leftJoin('persona as per', 'per.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->leftJoin('tipos_proyecto as tp', 'tp.ID_TIPO', '=', 'p.ID_TIPO');

        // Filtro por tipo de investigación (si el usuario eligió uno)
        if (!empty($tipo)) {
            $publicacionesQuery->where('tp.NOMBRE_TIPO', $tipo);
        }

        // Buscador general (si escribieron algo en el input q)
        if (!empty($busqueda)) {
            $publicacionesQuery->where(function ($q) use ($busqueda) {
                $q->where(DB::raw("CONCAT(per.NOMBRES, ' ', per.APELLIDOS)"), 'LIKE', '%' . $busqueda . '%')
                  ->orWhere('g.NOMBRE_GRUPO', 'LIKE', '%' . $busqueda . '%')
                  ->orWhere('a.NOMBRE_AREA', 'LIKE', '%' . $busqueda . '%')
                  ->orWhere('p.TITULO', 'LIKE', '%' . $busqueda . '%')
                  ->orWhere('r.NOMBRE_REVISTA', 'LIKE', '%' . $busqueda . '%');
            });
        }

        $publicaciones = $publicacionesQuery
            ->selectRaw('
                pr.ID_PROYECTO_REVISTA as id,
                p.TITULO as titulo,
                COALESCE(g.NOMBRE_GRUPO, "—") as grupo,
                a.NOMBRE_AREA as area,
                r.NOMBRE_REVISTA as revista,
                pr.ANIO_PUBLICACION as anio_publicacion,
                COALESCE(tp.NOMBRE_TIPO, "Sin tipo") as tipo,
                COALESCE(GROUP_CONCAT(DISTINCT CONCAT(per.NOMBRES, " ", per.APELLIDOS) SEPARATOR "; "), "—") as autores
            ')
            ->groupBy(
                'pr.ID_PROYECTO_REVISTA',
                'p.TITULO',
                'g.NOMBRE_GRUPO',
                'a.NOMBRE_AREA',
                'r.NOMBRE_REVISTA',
                'pr.ANIO_PUBLICACION',
                'tp.NOMBRE_TIPO'
            )
            ->orderByDesc('pr.ID_PROYECTO_REVISTA')
            ->get();

        // 👉 ESTA es la variable que pide tu vista
        $tiposInvestigacion = DB::table('tipos_proyecto')
            ->whereNotNull('NOMBRE_TIPO')
            ->where('NOMBRE_TIPO', '!=', '')
            ->orderBy('NOMBRE_TIPO')
            ->pluck('NOMBRE_TIPO');

        return view('admin.publicaciones.index', [
            'publicaciones'      => $publicaciones,
            'tiposInvestigacion' => $tiposInvestigacion,
        ]);
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        // 👉 CANDADO: Solo Super Admin y Publicador
        Gate::authorize('gestionar-publicaciones');

        $areas = DB::table('area_investigacion')
            ->select('ID_AREA', 'NOMBRE_AREA')
            ->orderBy('NOMBRE_AREA')
            ->get();

        $estados = DB::table('estado_proyecto')
            ->select('ID_ESTADO', 'NOMBRE_ESTADO')
            ->orderBy('NOMBRE_ESTADO')
            ->get();

        $revistas = DB::table('revistas')
            ->select('ID_REVISTA', 'NOMBRE_REVISTA')
            ->orderBy('NOMBRE_REVISTA')
            ->get();

        $grupos  = DB::table('grupos_investigacion')
            ->select('ID_GRUPO', 'NOMBRE_GRUPO')
            ->orderBy('NOMBRE_GRUPO')
            ->get();

        $investigadores = DB::table('investigador as i')
            ->join('persona as p', 'p.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->select('i.ID_INVESTIGADOR', DB::raw('CONCAT(p.NOMBRES," ",p.APELLIDOS) as nombre'))
            ->orderBy('nombre')
            ->get();

        $tipos = DB::table('tipos_proyecto')
            ->select('ID_TIPO', 'NOMBRE_TIPO')
            ->orderBy('NOMBRE_TIPO')
            ->get();

        return view('admin.publicaciones.create', compact(
            'areas',
            'estados',
            'revistas',
            'grupos',
            'investigadores',
            'tipos'
        ));
    }

    /**
     * Guardar nueva publicación
     */
    public function store(Request $request)
    {
        // 👉 CANDADO: Solo Super Admin y Publicador pueden guardar en la BD
        Gate::authorize('gestionar-publicaciones');

        $request->validate([
            // Proyecto
            'titulo'               => 'required|string|max:200',
            'anio'                 => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'fecha_inicio'         => 'nullable|date',
            'fecha_fin'            => 'nullable|date|after_or_equal:fecha_inicio',
            'descripcion_proyecto' => 'nullable|string',
            'presupuesto'          => 'nullable|numeric|min:0',
            'id_area'              => 'required|integer|exists:area_investigacion,ID_AREA',
            'id_estado'            => 'required|integer|exists:estado_proyecto,ID_ESTADO',
            'id_tipo'              => 'nullable|integer|exists:tipos_proyecto,ID_TIPO',
            'id_autor'             => 'required|integer|exists:investigador,ID_INVESTIGADOR',
            'rol_autor'            => 'nullable|string|max:50',
            'id_grupo'             => 'nullable|integer',

            // Publicación
            'id_revista'           => 'required|integer|exists:revistas,ID_REVISTA',
            'anio_publicacion'     => 'required|integer|min:1900|max:' . (date('Y') + 1),

            // Coautores
            'coautores'            => 'nullable|array',
            'coautores.*'          => 'integer|exists:investigador,ID_INVESTIGADOR',

            // PDF
            'pdf'                  => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        Log::info('Iniciando creación de publicación', [
            'titulo'    => $request->titulo,
            'tiene_pdf' => $request->hasFile('pdf'),
        ]);

        DB::beginTransaction();
        try {
            // 1) Crear PROYECTO
            $idProyecto = DB::table('proyecto')->insertGetId([
                'TITULO'               => $request->titulo,
                'DESCRIPCION_PROYECTO' => $request->descripcion_proyecto,
                'ANIO'                 => $request->anio,
                'FECHA_INICIO'         => $request->fecha_inicio,
                'FECHA_FIN'            => $request->fecha_fin,
                'PRESUPUESTO'          => $request->presupuesto,
                'ID_AUTOR'             => $request->id_autor,
                'ID_AREA'              => $request->id_area,
                'ID_ESTADO'            => $request->id_estado,
                'ID_TIPO'              => $request->id_tipo ?? null,
            ]);

            if (!empty($request->id_grupo)) {
                try {
                    DB::table('proyecto')
                        ->where('ID_PROYECTO', $idProyecto)
                        ->update(['ID_GRUPO' => $request->id_grupo]);
                } catch (\Throwable $e) {
                    Log::warning('No se pudo actualizar ID_GRUPO', [
                        'id_proyecto' => $idProyecto,
                        'error'       => $e->getMessage(),
                    ]);
                }
            }

            // 2) Crear PROYECTO_REVISTA
            $idPublicacion = DB::table('proyecto_revista')->insertGetId([
                'ID_PROYECTO'      => $idProyecto,
                'ID_REVISTA'       => $request->id_revista,
                'ANIO_PUBLICACION' => $request->anio_publicacion,
            ]);

            // 3) Autor principal
            DB::table('proyecto_investigador')->updateOrInsert(
                [
                    'ID_PROYECTO'     => $idProyecto,
                    'ID_INVESTIGADOR' => $request->id_autor,
                ],
                [
                    'ROL_EN_PROYECTO'            => $request->rol_autor,
                    'FECHA_INICIO_PARTICIPACION' => $request->fecha_inicio,
                    'FECHA_FIN_PARTICIPACION'    => $request->fecha_fin,
                ]
            );

            // 4) Coautores
            $coautores = collect($request->coautores ?? [])
                ->filter(fn($id) => (int)$id !== (int)$request->id_autor)
                ->unique()
                ->values();

            foreach ($coautores as $idInv) {
                DB::table('proyecto_investigador')->updateOrInsert(
                    [
                        'ID_PROYECTO'     => $idProyecto,
                        'ID_INVESTIGADOR' => $idInv,
                    ],
                    [
                        'ROL_EN_PROYECTO'            => null,
                        'FECHA_INICIO_PARTICIPACION' => $request->fecha_inicio,
                        'FECHA_FIN_PARTICIPACION'    => $request->fecha_fin,
                    ]
                );
            }

            // 5) PDF opcional – SIN Storage::url, guardado manual en storage/app/public
            if ($request->hasFile('pdf')) {
                $file = $request->file('pdf');

                if (!$file->isValid() || $file->getError() !== UPLOAD_ERR_OK) {
                    $msg = $file->getErrorMessage();
                    Log::error('Fallo de subida PDF', [
                        'php_error' => $file->getError(),
                        'msg'       => $msg,
                    ]);
                    return back()
                        ->withInput()
                        ->withErrors(['pdf' => 'No se pudo subir el PDF: ' . $msg]);
                }

                // 1) Capturamos TODO lo que necesitemos ANTES del move()
                $originalName = $file->getClientOriginalName();
                $sizeBytes    = $file->getSize();
                $mimeType     = $file->getMimeType();

                Log::info('PDF detectado', [
                    'original' => $originalName,
                    'size'     => $sizeBytes,
                    'mime'     => $mimeType,
                ]);

                // 2) Directorios
                $relativeDir = "publicaciones/{$idPublicacion}";              // relativo dentro de public
                $rootPublic  = storage_path('app/public');                    // raíz del disco public
                $fullDir     = $rootPublic . DIRECTORY_SEPARATOR . $relativeDir;

                if (!is_dir($fullDir)) {
                    mkdir($fullDir, 0775, true);
                }

                // 3) Nombre único de archivo
                $fileName = (string) Str::uuid() . '.pdf';

                // 4) Mover físicamente el archivo a storage/app/public/publicaciones/{id}
                $file->move($fullDir, $fileName);

                // 5) Paths
                $storedPath = $relativeDir . '/' . $fileName;   // publicaciones/{id}/uuid.pdf
                $publicUrl  = '/storage/' . $storedPath;        // /storage/publicaciones/{id}/uuid.pdf

                // 6) Hash sobre el archivo ya movido
                $absolutePath = $rootPublic . DIRECTORY_SEPARATOR . $storedPath;
                $hash = hash_file('sha256', $absolutePath);

                // 7) Insert en tabla de archivos usando los datos capturados ANTES del move()
                $idArchivo = DB::table('proyecto_revista_archivos')->insertGetId([
                    'ID_PROYECTO_REVISTA' => $idPublicacion,
                    'NOMBRE_ORIGINAL'     => $originalName,
                    'DISK'                => 'public',
                    'PATH'                => $storedPath,
                    'URL'                 => $publicUrl,
                    'MIME_TYPE'           => $mimeType,
                    'SIZE_BYTES'          => $sizeBytes,
                    'HASH_SHA256'         => $hash,
                    'TIPO'                => 'publicado',
                    'VERSION'             => 1,
                    'LICENCIA'            => null,
                ]);

                DB::table('proyecto_revista')
                    ->where('ID_PROYECTO_REVISTA', $idPublicacion)
                    ->update(['ID_ARCHIVO_PRINCIPAL' => $idArchivo]);
            }

            DB::commit();

            return redirect()
                ->route('publicaciones.index')
                ->with('ok', 'Publicación creada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al guardar la publicación', [
                'msg'  => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            return back()
                ->withInput()
                ->with('error', 'Ocurrió un error al guardar la publicación.');
        }
    }

    /**
     * Ver detalle de una publicación
     */
    public function show(string $id)
    {
        // El show no lleva candado restrictivo porque Lector también debe ver el detalle.

        $pub = DB::table('proyecto_revista as pr')
            ->join('proyecto as p', 'p.ID_PROYECTO', '=', 'pr.ID_PROYECTO')
            ->leftJoin('tipos_proyecto as tp', 'tp.ID_TIPO', '=', 'p.ID_TIPO')   // tipo de investigación
            ->leftJoin('area_investigacion as a', 'a.ID_AREA', '=', 'p.ID_AREA')
            ->leftJoin('estado_proyecto as est', 'est.ID_ESTADO', '=', 'p.ID_ESTADO')
            ->join('revistas as r', 'r.ID_REVISTA', '=', 'pr.ID_REVISTA')
            ->join('investigador as inv', 'inv.ID_INVESTIGADOR', '=', 'p.ID_AUTOR')
            ->join('persona as per', 'per.ID_PERSONA', '=', 'inv.ID_PERSONA')
            ->where('pr.ID_PROYECTO_REVISTA', $id)
            ->select([
                'pr.ID_PROYECTO_REVISTA as id_publicacion',
                'pr.ANIO_PUBLICACION as anio_publicacion',
                'pr.ID_ARCHIVO_PRINCIPAL as id_archivo_principal',

                'p.ID_PROYECTO as id_proyecto',
                'p.TITULO as titulo',
                'p.DESCRIPCION_PROYECTO as descripcion',
                'p.ANIO as anio_proyecto',
                'p.FECHA_INICIO as fecha_inicio',
                'p.FECHA_FIN as fecha_fin',
                'p.PRESUPUESTO as presupuesto',

                'a.NOMBRE_AREA as area',
                'est.NOMBRE_ESTADO as estado',

                // aquí definimos el tipo que la vista muestra como $pub->tipo
                DB::raw('COALESCE(tp.NOMBRE_TIPO, "Sin tipo") as tipo'),

                'r.NOMBRE_REVISTA as revista',
                'r.EDITORIAL as editorial',
                'r.PAIS as pais',
                DB::raw('CONCAT(per.NOMBRES," ",per.APELLIDOS) as autor_principal'),
            ])
            ->first();

        if (!$pub) {
            abort(404);
        }

        // Autores
        $autores = DB::table('proyecto_investigador as pi')
            ->join('investigador as i', 'i.ID_INVESTIGADOR', '=', 'pi.ID_INVESTIGADOR')
            ->join('persona as per', 'per.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->where('pi.ID_PROYECTO', $pub->id_proyecto)
            ->select([
                'i.ID_INVESTIGADOR',
                'pi.ROL_EN_PROYECTO',
                'DB::raw("CONCAT(per.NOMBRES,\" \",per.APELLIDOS) as nombre")',
            ])
            ->orderBy('per.APELLIDOS')
            ->get();

        // Archivo principal
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

        return view('admin.publicaciones.show', [
            'pub'              => $pub,
            'autores'          => $autores,
            'archivoPrincipal' => $archivoPrincipal,
        ]);
    }


    /**
     * Formulario de edición
     */
    public function edit($id)
    {
        // 👉 CANDADO: Solo Super Admin y Publicador
        Gate::authorize('gestionar-publicaciones');

        $pub = DB::table('proyecto_revista as pr')
            ->join('proyecto as p', 'p.ID_PROYECTO', '=', 'pr.ID_PROYECTO')
            ->leftJoin('estado_proyecto as est', 'est.ID_ESTADO', '=', 'p.ID_ESTADO')
            ->leftJoin('area_investigacion as a', 'a.ID_AREA', '=', 'p.ID_AREA')
            ->leftJoin('grupos_investigacion as g', 'g.ID_GRUPO', '=', 'p.ID_GRUPO')
            ->leftJoin('proyecto_investigador as pia', function ($join) {
                $join->on('pia.ID_PROYECTO', '=', 'p.ID_PROYECTO')
                    ->on('pia.ID_INVESTIGADOR', '=', 'p.ID_AUTOR');
            })
            ->join('revistas as r', 'r.ID_REVISTA', '=', 'pr.ID_REVISTA')
            ->join('investigador as inv', 'inv.ID_INVESTIGADOR', '=', 'p.ID_AUTOR')
            ->join('persona as per', 'per.ID_PERSONA', '=', 'inv.ID_PERSONA')
            ->where('pr.ID_PROYECTO_REVISTA', $id)
            ->select([
                'pr.ID_PROYECTO_REVISTA as id_publicacion',
                'pr.ID_REVISTA as id_revista',
                'pr.ANIO_PUBLICACION as anio_publicacion',

                'p.ID_PROYECTO as id_proyecto',
                'p.TITULO as titulo',
                'p.DESCRIPCION_PROYECTO as descripcion',
                'p.ANIO as anio_proyecto',
                'p.FECHA_INICIO as fecha_inicio',
                'p.FECHA_FIN as fecha_fin',
                'p.PRESUPUESTO as presupuesto',
                'p.ID_AREA as id_area',
                'p.ID_ESTADO as id_estado',
                'p.ID_AUTOR as id_autor',
                'p.ID_GRUPO as id_grupo',
                'p.ID_TIPO as id_tipo',

                DB::raw('COALESCE(g.NOMBRE_GRUPO,"—") as grupo'),
                'r.NOMBRE_REVISTA as revista',
                'pia.ROL_EN_PROYECTO as rol_autor',
            ])
            ->first();

        if (!$pub) abort(404);

        $areas = DB::table('area_investigacion')
            ->select('ID_AREA', 'NOMBRE_AREA')
            ->orderBy('NOMBRE_AREA')
            ->get();

        $estados = DB::table('estado_proyecto')
            ->select('ID_ESTADO', 'NOMBRE_ESTADO')
            ->orderBy('NOMBRE_ESTADO')
            ->get();

        $revistas = DB::table('revistas')
            ->select('ID_REVISTA', 'NOMBRE_REVISTA')
            ->orderBy('NOMBRE_REVISTA')
            ->get();

        $grupos = DB::table('grupos_investigacion')
            ->select('ID_GRUPO', 'NOMBRE_GRUPO')
            ->orderBy('NOMBRE_GRUPO')
            ->get();

        $investigadores = DB::table('investigador as i')
            ->join('persona as p', 'p.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->select('i.ID_INVESTIGADOR', DB::raw('CONCAT(p.NOMBRES," ",p.APELLIDOS) as nombre'))
            ->orderBy('nombre')
            ->get();

        $tipos = DB::table('tipos_proyecto')
            ->select('ID_TIPO', 'NOMBRE_TIPO')
            ->orderBy('NOMBRE_TIPO')
            ->get();

        $coautorIds = DB::table('proyecto_investigador')
            ->where('ID_PROYECTO', $pub->id_proyecto)
            ->where('ID_INVESTIGADOR', '<>', $pub->id_autor)
            ->pluck('ID_INVESTIGADOR')
            ->toArray();

        return view('admin.publicaciones.edit', [
            'pub'            => $pub,
            'areas'          => $areas,
            'estados'        => $estados,
            'revistas'       => $revistas,
            'grupos'         => $grupos,
            'investigadores' => $investigadores,
            'coautorIds'     => $coautorIds,
            'tipos'          => $tipos,
        ]);
    }

    /**
     * Actualizar publicación
     */
    public function update(Request $request, string $id)
    {
        // 👉 CANDADO: Solo Super Admin y Publicador
        Gate::authorize('gestionar-publicaciones');

        $data = $request->validate([
            'titulo'               => ['required', 'string', 'max:200'],
            'anio'                 => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'fecha_inicio'         => ['nullable', 'date'],
            'fecha_fin'            => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'descripcion_proyecto' => ['nullable', 'string'],
            'presupuesto'          => ['nullable', 'numeric'],
            'id_area'              => ['required', 'integer'],
            'id_estado'            => ['required', 'integer'],
            'id_tipo'              => ['nullable', 'integer', 'exists:tipos_proyecto,ID_TIPO'],
            'id_autor'             => ['required', 'integer'],
            'id_grupo'             => ['nullable', 'integer'],
            'rol_autor'            => ['nullable', 'string', 'max:50'],
            'coautores'            => ['nullable', 'array'],
            'coautores.*'          => ['integer'],
            'id_revista'           => ['required', 'integer'],
            'anio_publicacion'     => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'pdf'                  => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $coautores = collect($data['coautores'] ?? [])
            ->map(fn($v) => (int) $v)
            ->unique()
            ->reject(fn($v) => $v === (int) $data['id_autor'])
            ->values()
            ->all();

        DB::beginTransaction();
        try {
            $pub = DB::table('proyecto_revista')
                ->where('ID_PROYECTO_REVISTA', $id)
                ->first(['ID_PROYECTO']);

            if (!$pub) {
                DB::rollBack();
                return redirect()
                    ->route('publicaciones.index')
                    ->with('error', 'No se encontró la publicación.');
            }

            $idProyecto = (int) $pub->ID_PROYECTO;

            DB::table('proyecto')
                ->where('ID_PROYECTO', $idProyecto)
                ->update([
                    'TITULO'               => $data['titulo'],
                    'DESCRIPCION_PROYECTO' => $data['descripcion_proyecto'] ?? null,
                    'ANIO'                 => $data['anio'],
                    'FECHA_INICIO'         => $data['fecha_inicio'] ?? null,
                    'FECHA_FIN'            => $data['fecha_fin'] ?? null,
                    'PRESUPUESTO'          => $data['presupuesto'] ?? null,
                    'ID_AUTOR'             => $data['id_autor'],
                    'ID_GRUPO'             => $data['id_grupo'] ?? null,
                    'ID_AREA'              => $data['id_area'],
                    'ID_ESTADO'            => $data['id_estado'],
                    'ID_TIPO'              => $data['id_tipo'] ?? null,
                ]);

            DB::table('proyecto_revista')
                ->where('ID_PROYECTO_REVISTA', $id)
                ->update([
                    'ID_REVISTA'       => $data['id_revista'],
                    'ANIO_PUBLICACION' => $data['anio_publicacion'],
                ]);

            DB::table('proyecto_investigador')->updateOrInsert(
                ['ID_PROYECTO' => $idProyecto, 'ID_INVESTIGADOR' => $data['id_autor']],
                ['ROL_EN_PROYECTO' => $data['rol_autor'] ?? null]
            );

            foreach ($coautores as $idInv) {
                DB::table('proyecto_investigador')->updateOrInsert(
                    ['ID_PROYECTO' => $idProyecto, 'ID_INVESTIGADOR' => $idInv],
                    []
                );
            }

            $participantesActuales = DB::table('proyecto_investigador')
                ->where('ID_PROYECTO', $idProyecto)
                ->pluck('ID_INVESTIGADOR')
                ->map(fn($v) => (int) $v)
                ->all();

            $debenQuedar = array_merge([$data['id_autor']], $coautores);
            $paraEliminar = array_diff($participantesActuales, $debenQuedar);

            if (!empty($paraEliminar)) {
                DB::table('proyecto_investigador')
                    ->where('ID_PROYECTO', $idProyecto)
                    ->whereIn('ID_INVESTIGADOR', $paraEliminar)
                    ->delete();
            }
            // 👉 PASO 2: AGREGAMOS LA LÓGICA DE ACTUALIZACIÓN DEL PDF AQUÍ 👈
            if ($request->hasFile('pdf')) {
                $file = $request->file('pdf');

                if ($file->isValid() && $file->getError() === UPLOAD_ERR_OK) {
                    $originalName = $file->getClientOriginalName();
                    $sizeBytes    = $file->getSize();
                    $mimeType     = $file->getMimeType();

                    $relativeDir = "publicaciones/{$id}";
                    $rootPublic  = storage_path('app/public');
                    $fullDir     = $rootPublic . DIRECTORY_SEPARATOR . $relativeDir;

                    if (!is_dir($fullDir)) {
                        mkdir($fullDir, 0775, true);
                    }

                    // Mover el nuevo archivo
                    $fileName = (string) Str::uuid() . '.pdf';
                    $file->move($fullDir, $fileName);

                    // Rutas y Hash
                    $storedPath = $relativeDir . '/' . $fileName;
                    $publicUrl  = '/storage/' . $storedPath;
                    $absolutePath = $rootPublic . DIRECTORY_SEPARATOR . $storedPath;
                    $hash = hash_file('sha256', $absolutePath);

                    // Insertar el nuevo archivo en la base de datos
                    $idArchivo = DB::table('proyecto_revista_archivos')->insertGetId([
                        'ID_PROYECTO_REVISTA' => $id,
                        'NOMBRE_ORIGINAL'     => $originalName,
                        'DISK'                => 'public',
                        'PATH'                => $storedPath,
                        'URL'                 => $publicUrl,
                        'MIME_TYPE'           => $mimeType,
                        'SIZE_BYTES'          => $sizeBytes,
                        'HASH_SHA256'         => $hash,
                        'TIPO'                => 'publicado',
                        'VERSION'             => 2, // Opcional: podrías calcular la versión real leyendo la BD primero
                        'LICENCIA'            => null,
                    ]);

                    // Actualizar el puntero en proyecto_revista
                    DB::table('proyecto_revista')
                        ->where('ID_PROYECTO_REVISTA', $id)
                        ->update(['ID_ARCHIVO_PRINCIPAL' => $idArchivo]);
                } else {
                    // Opcional: Manejo de errores si el PDF falla
                    Log::error('Fallo de subida de nuevo PDF en update', ['error' => $file->getErrorMessage()]);
                }
            }
            //Fin pdf

            DB::commit();

            return redirect()
                ->route('publicaciones.index')
                ->with('ok', 'Publicación actualizada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }



    public function verArchivo($id, $file)
    {
        // Accesible por todos los que pueden ver la publicación
        // Ruta relativa dentro del disco "public"
        $path = "publicaciones/{$id}/{$file}";

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        // Devuelve el PDF embebido en el navegador
        return Storage::disk('public')->response($path);
    }

    public function descargarArchivo($id, $file)
    {
        // Accesible por todos los que pueden ver la publicación
        $path = "publicaciones/{$id}/{$file}";

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        // Fuerza la descarga del PDF
        return Storage::disk('public')->download($path);
    }

    public function destroy(string $id)
    {
        // 👉 CANDADO EXCLUSIVO: Solo Super Admin
        Gate::authorize('eliminar-publicaciones');

        DB::beginTransaction();

        try {
            // 1) Buscar la publicación
            $pub = DB::table('proyecto_revista')
                ->where('ID_PROYECTO_REVISTA', $id)
                ->first(['ID_PROYECTO']);

            if (!$pub) {
                DB::rollBack();
                return redirect()
                    ->route('publicaciones.index')
                    ->with('error', 'No se encontró la publicación.');
            }

            $idProyecto = (int) $pub->ID_PROYECTO;

            // 2) Borrar archivos físicos y registros de proyecto_revista_archivos
            $archivos = DB::table('proyecto_revista_archivos')
                ->where('ID_PROYECTO_REVISTA', $id)
                ->get(['PATH']);

            foreach ($archivos as $archivo) {
                if ($archivo->PATH && Storage::disk('public')->exists($archivo->PATH)) {
                    Storage::disk('public')->delete($archivo->PATH);
                }
            }

            // opcional: borrar todo el directorio de la publicación
            Storage::disk('public')->deleteDirectory("publicaciones/{$id}");

            DB::table('proyecto_revista_archivos')
                ->where('ID_PROYECTO_REVISTA', $id)
                ->delete();

            // 3) Borrar la fila de proyecto_revista
            DB::table('proyecto_revista')
                ->where('ID_PROYECTO_REVISTA', $id)
                ->delete();

            // 4) Si ya no hay más publicaciones que usen ese proyecto,
            //    limpiamos autores y el proyecto base
            $existenMasPublicaciones = DB::table('proyecto_revista')
                ->where('ID_PROYECTO', $idProyecto)
                ->exists();

            if (!$existenMasPublicaciones) {
                DB::table('proyecto_investigador')
                    ->where('ID_PROYECTO', $idProyecto)
                    ->delete();

                DB::table('proyecto')
                    ->where('ID_PROYECTO', $idProyecto)
                    ->delete();
            }

            DB::commit();

            return redirect()
                ->route('publicaciones.index')
                ->with('ok', 'Publicación eliminada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Error al eliminar publicación', [
                'id_publicacion' => $id,
                'msg'            => $e->getMessage(),
                'file'           => $e->getFile(),
                'line'           => $e->getLine(),
            ]);

            return redirect()
                ->route('publicaciones.index')
                ->with('error', 'No se pudo eliminar la publicación.');
        }
    }

}