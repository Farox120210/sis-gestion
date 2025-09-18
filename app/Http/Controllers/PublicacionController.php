<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class PublicacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $publicaciones = DB::table('proyecto_revista as pr')
            ->join('proyecto as p', 'p.ID_PROYECTO', '=', 'pr.ID_PROYECTO')
            ->join('revistas as r', 'r.ID_REVISTA', '=', 'pr.ID_REVISTA')
            ->leftJoin('area_investigacion as a', 'a.ID_AREA', '=', 'p.ID_AREA')
            ->leftJoin('grupos_investigacion as g', 'g.ID_GRUPO', '=', 'p.ID_GRUPO') // NUEVO
            ->leftJoin('proyecto_investigador as pi', 'pi.ID_PROYECTO', '=', 'p.ID_PROYECTO')
            ->leftJoin('investigador as i', 'i.ID_INVESTIGADOR', '=', 'pi.ID_INVESTIGADOR')
            ->leftJoin('persona as per', 'per.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->selectRaw('
            pr.ID_PROYECTO_REVISTA as id,
            p.TITULO as titulo,
            COALESCE(g.NOMBRE_GRUPO, "—") as grupo,     -- NUEVO
            a.NOMBRE_AREA as area,
            r.NOMBRE_REVISTA as revista,
            pr.ANIO_PUBLICACION as anio_publicacion,
            COALESCE(GROUP_CONCAT(DISTINCT CONCAT(per.NOMBRES, " ", per.APELLIDOS) SEPARATOR "; "), "—") as autores
        ')
            ->groupBy(
                'pr.ID_PROYECTO_REVISTA',
                'p.TITULO',
                'g.NOMBRE_GRUPO',
                'a.NOMBRE_AREA',
                'r.NOMBRE_REVISTA',
                'pr.ANIO_PUBLICACION'
            )
            ->orderByDesc('pr.ANIO_PUBLICACION')
            ->get();

        return view('admin.publicaciones.index', compact('publicaciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $areas = DB::table('area_investigacion')->select('ID_AREA', 'NOMBRE_AREA')->orderBy('NOMBRE_AREA')->get();
        $estados = DB::table('estado_proyecto')->select('ID_ESTADO', 'NOMBRE_ESTADO')->orderBy('NOMBRE_ESTADO')->get();
        $revistas = DB::table('revistas')->select('ID_REVISTA', 'NOMBRE_REVISTA')->orderBy('NOMBRE_REVISTA')->get();
        $grupos  = DB::table('grupos_investigacion')->select('ID_GRUPO', 'NOMBRE_GRUPO')->orderBy('NOMBRE_GRUPO')->get();  // NUEVO
        $investigadores = DB::table('investigador as i')
            ->join('persona as p', 'p.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->select('i.ID_INVESTIGADOR', DB::raw('CONCAT(p.NOMBRES," ",p.APELLIDOS) as nombre'))
            ->orderBy('nombre')->get();

        return view('admin.publicaciones.create', compact('areas', 'estados', 'revistas', 'grupos', 'investigadores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)


    {
        $request->validate([
            // Proyecto
            'titulo'                => 'required|string|max:200',
            'anio'                  => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'fecha_inicio'          => 'nullable|date',
            'fecha_fin'             => 'nullable|date|after_or_equal:fecha_inicio',
            'descripcion_proyecto'  => 'nullable|string',
            'presupuesto'           => 'nullable|numeric|min:0',
            'id_area'               => 'required|integer|exists:area_investigacion,ID_AREA',
            'id_estado'             => 'required|integer|exists:estado_proyecto,ID_ESTADO',
            'id_autor'              => 'required|integer|exists:investigador,ID_INVESTIGADOR',
            'rol_autor'             => 'nullable|string|max:50',
            'id_grupo'              => 'nullable|integer',

            // Publicación
            'id_revista'            => 'required|integer|exists:revistas,ID_REVISTA',
            'anio_publicacion'      => 'required|integer|min:1900|max:' . (date('Y') + 1),

            // Coautores
            'coautores'             => 'nullable|array',
            'coautores.*'           => 'integer|exists:investigador,ID_INVESTIGADOR',

            // PDF opcional
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        Log::info('=== INICIO DEBUG PUBLICACION ===', [
            'hasFile' => $request->hasFile('pdf'),
            'fileInfo' => $request->hasFile('pdf') ? [
                'name' => $request->file('pdf')->getClientOriginalName(),
                'size' => $request->file('pdf')->getSize(),
                'error' => $request->file('pdf')->getError(),
            ] : null,
            'input_data' => $request->except(['pdf', '_token']),
            'upload_max' => ini_get('upload_max_filesize'),
            'post_max' => ini_get('post_max_size'),
        ]);

        Log::info('Iniciando creación de publicación', [
            'titulo' => $request->titulo,
            'tiene_pdf' => $request->hasFile('pdf'),
        ]);
        Log::debug('UPLOAD DEBUG', [
            'hasFile'     => $request->hasFile('pdf'),
            'error'       => $request->file('pdf')?->getError(),         // código UPLOAD_ERR_*
            'errStr'      => $request->file('pdf')?->getErrorMessage(),  // ← instancia, sin parámetros
            'filesize_ini' => ini_get('upload_max_filesize'),
            'postsize_ini' => ini_get('post_max_size'),
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
            ]);

            // (Opcional) ID_GRUPO si existe la columna
            if (!empty($request->id_grupo)) {
                try {
                    DB::table('proyecto')->where('ID_PROYECTO', $idProyecto)->update([
                        'ID_GRUPO' => $request->id_grupo
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Columna ID_GRUPO no existe o no se pudo actualizar', ['error' => $e->getMessage()]);
                }
            }

            // 2) Crear PROYECTO_REVISTA
            $idPublicacion = DB::table('proyecto_revista')->insertGetId([
                'ID_PROYECTO'      => $idProyecto,
                'ID_REVISTA'       => $request->id_revista,
                'ANIO_PUBLICACION' => $request->anio_publicacion,
            ]);

            // 3) Autor principal en proyecto_investigador
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

            // 4) Coautores (sin duplicar autor)
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

            // 5) PDF opcional (single) → tabla hija + marcar principal
            if ($request->hasFile('pdf')) {
                $file = $request->file('pdf');

                // 5.1 Verifica error de subida de PHP antes de procesar
                if ($file->getError() !== UPLOAD_ERR_OK) {
                    $msg = $file->getErrorMessage(); // método de instancia, sin args
                    Log::error('Fallo de subida (PHP)', ['php_error' => $file->getError(), 'msg' => $msg]);
                    return back()->withInput()->withErrors(['pdf' => 'No se pudo subir el PDF: ' . $msg]);
                }

                Log::info('PDF detectado', [
                    'original' => $file->getClientOriginalName(),
                    'size'     => $file->getSize(),
                    'mime'     => $file->getMimeType(),
                ]);

                $disk = 'public';
                $directory = "publicaciones/{$idPublicacion}";
                $fileName = (string) Str::uuid() . '.pdf';

                try {
                    // Asegura la carpeta destino
                    Storage::disk($disk)->makeDirectory($directory);

                    // Guarda el archivo en el disco configurado y obtiene la ruta relativa
                    $storedPath = Storage::disk($disk)->putFileAs($directory, $file, $fileName);

                    if (empty($storedPath)) {
                        throw new \RuntimeException('putFileAs devolvió ruta vacía.');
                    }

                    $publicUrl = Storage::disk($disk)->url($storedPath);
                    $absolutePath = Storage::disk($disk)->path($storedPath);

                    Log::debug('STORAGE DEBUG POST', [
                        'storedPath' => $storedPath,
                        'exists'     => Storage::disk($disk)->exists($storedPath),
                        'publicUrl'  => $publicUrl,
                    ]);

                    $hash = hash_file('sha256', $absolutePath);

                    $idArchivo = DB::table('proyecto_revista_archivos')->insertGetId([
                        'ID_PROYECTO_REVISTA' => $idPublicacion,
                        'NOMBRE_ORIGINAL'     => $file->getClientOriginalName(),
                        'DISK'                => $disk,
                        'PATH'                => $storedPath,
                        'URL'                 => $publicUrl,
                        'MIME_TYPE'           => $file->getMimeType(),
                        'SIZE_BYTES'          => $file->getSize(),
                        'HASH_SHA256'         => $hash,
                        'TIPO'                => 'publicado',
                        'VERSION'             => 1,
                        'LICENCIA'            => null,
                    ]);

                    DB::table('proyecto_revista')
                        ->where('ID_PROYECTO_REVISTA', $idPublicacion)
                        ->update(['ID_ARCHIVO_PRINCIPAL' => $idArchivo]);
                } catch (\Throwable $ex) {
                    Log::error('Error subiendo/registrando PDF', [
                        'msg'  => $ex->getMessage(),
                        'line' => $ex->getLine(),
                        'file' => $ex->getFile(),
                    ]);

                    throw $ex; // rollback por transacción
                }
            } else {
                Log::warning('hasFile("pdf") == false');
            }

            DB::commit();

            Log::info('Publicación creada OK', [
                'id_proyecto'    => $idProyecto,
                'id_publicacion' => $idPublicacion,
            ]);

            return redirect()->route('publicaciones.index')->with('ok', 'Publicación creada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al guardar la publicación', [
                'msg'  => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            return back()->withInput()->with('error', 'Ocurrió un error al guardar la publicación.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pub = DB::table('proyecto_revista as pr')
            ->join('proyecto as p', 'p.ID_PROYECTO', '=', 'pr.ID_PROYECTO')
            ->leftJoin('area_investigacion as a', 'a.ID_AREA', '=', 'p.ID_AREA')
            ->leftJoin('estado_proyecto as est', 'est.ID_ESTADO', '=', 'p.ID_ESTADO')
            ->join('revistas as r', 'r.ID_REVISTA', '=', 'pr.ID_REVISTA')
            ->join('investigador as inv', 'inv.ID_INVESTIGADOR', '=', 'p.ID_AUTOR')
            ->join('persona as per', 'per.ID_PERSONA', '=', 'inv.ID_PERSONA')
            ->where('pr.ID_PROYECTO_REVISTA', $id)
            ->select([
                'pr.ID_PROYECTO_REVISTA as id_publicacion',
                'pr.ANIO_PUBLICACION as anio_publicacion',
                // ⬇️ Asegúrate de traer este campo (debe existir en tu tabla)
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
                'r.NOMBRE_REVISTA as revista',
                'r.EDITORIAL as editorial',
                'r.PAIS as pais',
                DB::raw('CONCAT(per.NOMBRES," ",per.APELLIDOS) as autor_principal'),
            ])
            ->first();

        if (!$pub) {
            abort(404);
        }

        // Autores (como ya lo tenías)
        $autores = DB::table('proyecto_investigador as pi')
            ->join('investigador as i', 'i.ID_INVESTIGADOR', '=', 'pi.ID_INVESTIGADOR')
            ->join('persona as per', 'per.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->where('pi.ID_PROYECTO', $pub->id_proyecto)
            ->select([
                'i.ID_INVESTIGADOR',
                'pi.ROL_EN_PROYECTO',
                DB::raw('CONCAT(per.NOMBRES," ",per.APELLIDOS) as nombre'),
            ])
            ->orderBy('per.APELLIDOS')
            ->get();

        $archivoPrincipal = null;

        // Si la publicación tiene un id de archivo principal, cárgalo
        if (!empty($pub->id_archivo_principal)) {
            $archivoPrincipal = DB::table('proyecto_revista_archivos')
                ->where('ID_ARCHIVO', $pub->id_archivo_principal)
                ->select([
                    'ID_ARCHIVO',
                    'NOMBRE_ORIGINAL',
                    'DISK',
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
            'pub'               => $pub,
            'autores'           => $autores,
            'archivoPrincipal'  => $archivoPrincipal, // ya existe la variable
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // 1) Carga de la publicación
        $pub = DB::table('proyecto_revista as pr')
            ->join('proyecto as p', 'p.ID_PROYECTO', '=', 'pr.ID_PROYECTO')
            ->leftJoin('estado_proyecto as est', 'est.ID_ESTADO', '=', 'p.ID_ESTADO')
            ->leftJoin('area_investigacion as a', 'a.ID_AREA', '=', 'p.ID_AREA')
            ->leftJoin('grupos_investigacion as g', 'g.ID_GRUPO', '=', 'p.ID_GRUPO')
            ->leftJoin('proyecto_investigador as pia', function ($join) {
                $join->on('pia.ID_PROYECTO', '=', 'p.ID_PROYECTO')
                    ->on('pia.ID_INVESTIGADOR', '=', 'p.ID_AUTOR'); // rol del autor principal
            })
            ->join('revistas as r', 'r.ID_REVISTA', '=', 'pr.ID_REVISTA')
            ->join('investigador as inv', 'inv.ID_INVESTIGADOR', '=', 'p.ID_AUTOR')
            ->join('persona as per', 'per.ID_PERSONA', '=', 'inv.ID_PERSONA')
            ->where('pr.ID_PROYECTO_REVISTA', $id)
            ->select([
                'pr.ID_PROYECTO_REVISTA as id_publicacion',
                'pr.ID_ARCHIVO_PRINCIPAL as id_archivo_principal',
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

                DB::raw('COALESCE(g.NOMBRE_GRUPO,"—") as grupo'),
                'r.NOMBRE_REVISTA as revista',
                'pia.ROL_EN_PROYECTO as rol_autor',
            ])
            ->first();

        if (!$pub) abort(404);

        // 2) Catálogos (¡estos son los que faltaban!)
        $areas = DB::table('area_investigacion')
            ->select('ID_AREA', 'NOMBRE_AREA')
            ->orderBy('NOMBRE_AREA')->get();

        $estados = DB::table('estado_proyecto')
            ->select('ID_ESTADO', 'NOMBRE_ESTADO')
            ->orderBy('NOMBRE_ESTADO')->get();

        $revistas = DB::table('revistas')
            ->select('ID_REVISTA', 'NOMBRE_REVISTA')
            ->orderBy('NOMBRE_REVISTA')->get();

        $grupos = DB::table('grupos_investigacion')
            ->select('ID_GRUPO', 'NOMBRE_GRUPO')
            ->orderBy('NOMBRE_GRUPO')->get();

        $investigadores = DB::table('investigador as i')
            ->join('persona as p', 'p.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->select('i.ID_INVESTIGADOR', DB::raw('CONCAT(p.NOMBRES," ",p.APELLIDOS) as nombre'))
            ->orderBy('nombre')->get();

        // 3) Coautores preseleccionados
        $coautorIds = DB::table('proyecto_investigador')
            ->where('ID_PROYECTO', $pub->id_proyecto)
            ->where('ID_INVESTIGADOR', '<>', $pub->id_autor)
            ->pluck('ID_INVESTIGADOR')
            ->toArray();

        $archivos = DB::table('proyecto_revista_archivos')
            ->where('ID_PROYECTO_REVISTA', $id)
            ->orderByDesc('FECHA_SUBIDA')
            ->get();

        // 4) Return (mejor pasar arreglo explícito para evitar typos)
        return view('admin.publicaciones.edit', [
            'pub'            => $pub,
            'areas'          => $areas,
            'estados'        => $estados,
            'revistas'       => $revistas,
            'grupos'         => $grupos,
            'investigadores' => $investigadores,
            'coautorIds'     => $coautorIds,
            'archivos'       => $archivos,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validación
        $data = $request->validate([
            'titulo'              => ['required', 'string', 'max:200'],
            'anio'                => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'fecha_inicio'        => ['nullable', 'date'],
            'fecha_fin'           => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'descripcion_proyecto' => ['nullable', 'string'],
            'presupuesto'         => ['nullable', 'numeric'],
            'id_area'             => ['required', 'integer'],
            'id_estado'           => ['required', 'integer'],
            'id_autor'            => ['required', 'integer'],
            'id_grupo'            => ['nullable', 'integer'],
            'rol_autor'           => ['nullable', 'string', 'max:50'],
            'coautores'           => ['nullable', 'array'],
            'coautores.*'         => ['integer'],
            'id_revista'          => ['required', 'integer'],
            'anio_publicacion'    => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
        ]);

        // Normaliza coautores
        $coautores = collect($data['coautores'] ?? [])
            ->map(fn($v) => (int)$v)
            ->unique()
            ->reject(fn($v) => $v === (int)$data['id_autor'])   // evita duplicar autor
            ->values()
            ->all();

        DB::beginTransaction();
        try {
            // 1) Localiza la publicación y su proyecto
            $pub = DB::table('proyecto_revista')
                ->where('ID_PROYECTO_REVISTA', $id)
                ->first(['ID_PROYECTO', 'ID_REVISTA', 'ANIO_PUBLICACION']);

            if (!$pub) {
                DB::rollBack();
                return redirect()->route('publicaciones.index')
                    ->with('error', 'No se encontró la publicación.');
            }

            $idProyecto = (int) $pub->ID_PROYECTO;

            // 2) Actualiza PROYECTO
            DB::table('proyecto')
                ->where('ID_PROYECTO', $idProyecto)
                ->update([
                    'TITULO'              => $data['titulo'],
                    'DESCRIPCION_PROYECTO' => $data['descripcion_proyecto'] ?? null,
                    'ANIO'                => $data['anio'],
                    'FECHA_INICIO'        => $data['fecha_inicio'] ?? null,
                    'FECHA_FIN'           => $data['fecha_fin'] ?? null,
                    'PRESUPUESTO'         => $data['presupuesto'] ?? null,
                    'ID_AUTOR'            => $data['id_autor'],
                    'ID_GRUPO' => $data['id_grupo'] ?? null,
                    'ID_AREA'             => $data['id_area'],
                    'ID_ESTADO'           => $data['id_estado'],
                ]);

            // 3) Actualiza PROYECTO_REVISTA
            DB::table('proyecto_revista')
                ->where('ID_PROYECTO_REVISTA', $id)
                ->update([
                    'ID_REVISTA'       => $data['id_revista'],
                    'ANIO_PUBLICACION' => $data['anio_publicacion'],
                ]);

            // 4) Sincroniza autores en PROYECTO_INVESTIGADOR
            //    - Asegura fila del autor principal (con rol opcional)
            DB::table('proyecto_investigador')->updateOrInsert(
                ['ID_PROYECTO' => $idProyecto, 'ID_INVESTIGADOR' => $data['id_autor']],
                ['ROL_EN_PROYECTO' => $data['rol_autor'] ?? null]
            );

            //    - Coautores: inserta los seleccionados
            foreach ($coautores as $idInv) {
                DB::table('proyecto_investigador')->updateOrInsert(
                    ['ID_PROYECTO' => $idProyecto, 'ID_INVESTIGADOR' => $idInv],
                    [] // sin cambios extra; puedes guardar rol si lo capturas
                );
            }

            //    - Elimina participantes que ya no están (excepto autor)
            $participantesActuales = DB::table('proyecto_investigador')
                ->where('ID_PROYECTO', $idProyecto)
                ->pluck('ID_INVESTIGADOR')
                ->map(fn($v) => (int)$v)
                ->all();

            $debenQuedar = array_merge([$data['id_autor']], $coautores);
            $paraEliminar = array_diff($participantesActuales, $debenQuedar);

            if (!empty($paraEliminar)) {
                DB::table('proyecto_investigador')
                    ->where('ID_PROYECTO', $idProyecto)
                    ->whereIn('ID_INVESTIGADOR', $paraEliminar)
                    ->delete();
            }

            DB::commit();

            return redirect()
                ->route('publicaciones.index')
                ->with('ok', 'Publicación actualizada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
