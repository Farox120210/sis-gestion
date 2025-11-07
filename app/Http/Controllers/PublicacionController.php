<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TipoProyecto;
use App\Models\EstadoProyecto;
use App\Models\AreaInvestigacion;
use App\Models\Investigador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Exports\PublicacionesExport;
use Maatwebsite\Excel\Facades\Excel;

class PublicacionController extends Controller
{
    // ------------------------------
    // Búsquedas (Select2)
    // ------------------------------
    public function searchInvestigadores(Request $request)
    {
        $q = trim($request->get('q', ''));

        $query = DB::table('investigador as i')
            ->join('persona as p', 'p.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->select([
                'i.ID_INVESTIGADOR as id',
                DB::raw('CONCAT(p.NOMBRES," ",p.APELLIDOS) as text'),
            ])
            ->orderBy('p.APELLIDOS');

        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('p.NOMBRES', 'like', $like)
                  ->orWhere('p.APELLIDOS', 'like', $like)
                  ->orWhere(DB::raw('CONCAT(p.NOMBRES," ",p.APELLIDOS)'), 'like', $like);
            });
        }

        $items = $query->limit(20)->get();

        return response()->json([
            'results'    => $items,
            'pagination' => ['more' => false],
        ]);
    }

    public function verArchivo($id, $file)
    {
        $path = "publicaciones/{$id}/{$file}";
        abort_unless(Storage::disk('public')->exists($path), 404);
        return Storage::disk('public')->response($path);
    }

    public function descargarArchivo($id, $file)
    {
        $path = "publicaciones/{$id}/{$file}";
        abort_unless(Storage::disk('public')->exists($path), 404);
        return Storage::disk('public')->download($path);
    }

    // ------------------------------
    // Index
    // ------------------------------
    public function index()
    {
        $publicaciones = DB::table('proyecto_revista as pr')
            ->join('proyecto as p', 'p.ID_PROYECTO', '=', 'pr.ID_PROYECTO')
            ->join('revistas as r', 'r.ID_REVISTA', '=', 'pr.ID_REVISTA')
            ->leftJoin('tipos_proyecto as tp', 'tp.ID_TIPO', '=', 'p.ID_TIPO')
            ->leftJoin('area_investigacion as a', 'a.ID_AREA', '=', 'p.ID_AREA')
            ->leftJoin('grupos_investigacion as g', 'g.ID_GRUPO', '=', 'p.ID_GRUPO')
            ->leftJoin('proyecto_investigador as pi', 'pi.ID_PROYECTO', '=', 'p.ID_PROYECTO')
            ->leftJoin('investigador as i', 'i.ID_INVESTIGADOR', '=', 'pi.ID_INVESTIGADOR')
            ->leftJoin('persona as per', 'per.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->selectRaw("
                pr.ID_PROYECTO_REVISTA as id,
                p.TITULO as titulo,
                COALESCE(g.NOMBRE_GRUPO, '—') as grupo,
                a.NOMBRE_AREA as area,
                r.NOMBRE_REVISTA as revista,
                pr.ANIO_PUBLICACION as anio_publicacion,
                COALESCE(tp.NOMBRE_TIPO, 'Sin tipo') as tipo,
                COALESCE(GROUP_CONCAT(DISTINCT CONCAT(per.NOMBRES, ' ', per.APELLIDOS) SEPARATOR '; '), '—') as autores
            ")
            ->groupBy(
                'pr.ID_PROYECTO_REVISTA',
                'p.TITULO',
                'g.NOMBRE_GRUPO',
                'a.NOMBRE_AREA',
                'r.NOMBRE_REVISTA',
                'pr.ANIO_PUBLICACION',
                'tp.NOMBRE_TIPO'
            )
            ->orderByDesc('pr.ANIO_PUBLICACION')
            ->get();

        return view('admin.publicaciones.index', compact('publicaciones'));
    }

    // ------------------------------
    // Create
    // ------------------------------
    public function create()
    {
        $areas   = DB::table('area_investigacion')->select('ID_AREA', 'NOMBRE_AREA')->orderBy('NOMBRE_AREA')->get();
        $estados = DB::table('estado_proyecto')->select('ID_ESTADO', 'NOMBRE_ESTADO')->orderBy('NOMBRE_ESTADO')->get();
        $revistas= DB::table('revistas')->select('ID_REVISTA', 'NOMBRE_REVISTA')->orderBy('NOMBRE_REVISTA')->get();
        $grupos  = DB::table('grupos_investigacion')->select('ID_GRUPO', 'NOMBRE_GRUPO')->orderBy('NOMBRE_GRUPO')->get();
        $tipos   = DB::table('tipos_proyecto')->select('ID_TIPO', 'NOMBRE_TIPO')->orderBy('NOMBRE_TIPO')->get();

        $investigadores = DB::table('investigador as i')
            ->join('persona as p', 'p.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->select('i.ID_INVESTIGADOR', DB::raw('CONCAT(p.NOMBRES," ",p.APELLIDOS) as nombre'))
            ->orderBy('nombre')->get();

        // IMPORTANTE: enviar $tipos
        return view('admin.publicaciones.create', compact('areas', 'estados', 'revistas', 'grupos', 'investigadores', 'tipos'));
    }

    // ------------------------------
    // Store
    // ------------------------------
    public function store(Request $request)
    {
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
            'pdf'                  => ['nullable','file','mimes:pdf','max:20480'],
        ]);

        Log::info('=== INICIO DEBUG PUBLICACION ===', [
            'hasFile'   => $request->hasFile('pdf'),
            'fileInfo'  => $request->hasFile('pdf') ? [
                'name'  => $request->file('pdf')->getClientOriginalName(),
                'size'  => $request->file('pdf')->getSize(),
                'error' => $request->file('pdf')->getError(),
            ] : null,
            'input_data' => $request->except(['pdf', '_token']),
            'upload_max' => ini_get('upload_max_filesize'),
            'post_max'   => ini_get('post_max_size'),
        ]);

        DB::beginTransaction();
        try {
            // 1) PROYECTO
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

            // 1.1) Grupo opcional
            if (!empty($request->id_grupo)) {
                try {
                    DB::table('proyecto')->where('ID_PROYECTO', $idProyecto)->update([
                        'ID_GRUPO' => $request->id_grupo
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Columna ID_GRUPO no existe o no se pudo actualizar', ['error' => $e->getMessage()]);
                }
            }

            // 2) PROYECTO_REVISTA
            $idPublicacion = DB::table('proyecto_revista')->insertGetId([
                'ID_PROYECTO'      => $idProyecto,
                'ID_REVISTA'       => $request->id_revista,
                'ANIO_PUBLICACION' => $request->anio_publicacion,
            ]);

            if (empty($idPublicacion)) {
                throw new \RuntimeException('ID de publicación no disponible.');
            }

            // 3) Autor principal
            DB::table('proyecto_investigador')->updateOrInsert(
                ['ID_PROYECTO' => $idProyecto, 'ID_INVESTIGADOR' => $request->id_autor],
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
                    ['ID_PROYECTO' => $idProyecto, 'ID_INVESTIGADOR' => $idInv],
                    [
                        'ROL_EN_PROYECTO'            => null,
                        'FECHA_INICIO_PARTICIPACION' => $request->fecha_inicio,
                        'FECHA_FIN_PARTICIPACION'    => $request->fecha_fin,
                    ]
                );
            }

            // 5) PDF opcional
            if ($request->hasFile('pdf')) {
                $file = $request->file('pdf');

                if (!$file->isValid()) {
                    $msg = method_exists($file, 'getErrorMessage') ? $file->getErrorMessage() : ('Código: ' . $file->getError());
                    Log::error('Fallo de subida (PHP)', ['php_error' => $file->getError(), 'msg' => $msg]);
                    return back()->withInput()->withErrors(['pdf' => 'No se pudo subir el PDF: ' . $msg]);
                }

                try {
                    $dir = "publicaciones/{$idPublicacion}";
                    $fileName = (string) Str::uuid() . '.pdf';

                    Storage::disk('public')->put('__ping.txt', 'OK');
                    Storage::disk('public')->makeDirectory($dir);

                    $finalPath = rtrim($dir, '/') . '/' . $fileName;

                    $tmpPath = method_exists($file, 'getPathname') ? $file->getPathname() : (method_exists($file, 'path') ? $file->path() : null);
                    if (empty($tmpPath) || !is_file($tmpPath)) {
                        throw new \RuntimeException('No se pudo resolver el archivo temporal (pathname).');
                    }

                    $stream = @fopen($tmpPath, 'rb');
                    if ($stream === false) throw new \RuntimeException('No se pudo abrir el archivo temporal.');

                    $contents = stream_get_contents($stream);
                    fclose($stream);
                    if ($contents === false) throw new \RuntimeException('No se pudo leer el archivo temporal.');

                    $ok = Storage::disk('public')->put($finalPath, $contents);
                    if (!$ok) throw new \RuntimeException('Storage::put devolvió false.');

                    $storedPath = $finalPath;
                    $publicUrl  = Storage::disk('public')->url($storedPath);
                    $hash       = hash_file('sha256', $tmpPath);

                    $idArchivo = DB::table('proyecto_revista_archivos')->insertGetId([
                        'ID_PROYECTO_REVISTA' => $idPublicacion,
                        'NOMBRE_ORIGINAL'     => $file->getClientOriginalName(),
                        'DISK'                => 'public',
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
                    throw $ex;
                }
            } else {
                Log::warning('hasFile("pdf") == false');
            }

            DB::commit();

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

    // ------------------------------
    // Show
    // ------------------------------
    public function show(string $id)
    {
        $pub = DB::table('proyecto_revista as pr')
            ->join('proyecto as p', 'p.ID_PROYECTO', '=', 'pr.ID_PROYECTO')
            ->leftJoin('tipos_proyecto as tp', 'tp.ID_TIPO', '=', 'p.ID_TIPO') // ← AÑADIDO
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
                DB::raw('COALESCE(tp.NOMBRE_TIPO,"Sin tipo") as tipo'),
                'r.NOMBRE_REVISTA as revista',
                'r.EDITORIAL as editorial',
                'r.PAIS as pais',
                DB::raw('CONCAT(per.NOMBRES," ",per.APELLIDOS) as autor_principal'),
            ])
            ->first();

        if (!$pub) abort(404);

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

    // ------------------------------
    // Edit
    // ------------------------------
    public function edit($id)
    {
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
                'p.ID_TIPO as id_tipo', // ← AÑADIDO

                DB::raw('COALESCE(g.NOMBRE_GRUPO,"—") as grupo'),
                'r.NOMBRE_REVISTA as revista',
                'pia.ROL_EN_PROYECTO as rol_autor',
            ])
            ->first();

        if (!$pub) abort(404);

        $areas   = DB::table('area_investigacion')->select('ID_AREA','NOMBRE_AREA')->orderBy('NOMBRE_AREA')->get();
        $estados = DB::table('estado_proyecto')->select('ID_ESTADO','NOMBRE_ESTADO')->orderBy('NOMBRE_ESTADO')->get();
        $revistas= DB::table('revistas')->select('ID_REVISTA','NOMBRE_REVISTA')->orderBy('NOMBRE_REVISTA')->get();
        $grupos  = DB::table('grupos_investigacion')->select('ID_GRUPO','NOMBRE_GRUPO')->orderBy('NOMBRE_GRUPO')->get();
        $investigadores = DB::table('investigador as i')
            ->join('persona as p', 'p.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->select('i.ID_INVESTIGADOR', DB::raw('CONCAT(p.NOMBRES," ",p.APELLIDOS) as nombre'))
            ->orderBy('nombre')->get();

        $tipos = DB::table('tipos_proyecto')->select('ID_TIPO','NOMBRE_TIPO')->orderBy('NOMBRE_TIPO')->get();

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

    // ------------------------------
    // Update
    // ------------------------------
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'titulo'               => ['required','string','max:200'],
            'anio'                 => ['required','integer','min:1900','max:' . (date('Y') + 1)],
            'fecha_inicio'         => ['nullable','date'],
            'fecha_fin'            => ['nullable','date','after_or_equal:fecha_inicio'],
            'descripcion_proyecto' => ['nullable','string'],
            'presupuesto'          => ['nullable','numeric'],
            'id_area'              => ['required','integer'],
            'id_estado'            => ['required','integer'],
            'id_tipo'              => ['nullable','integer','exists:tipos_proyecto,ID_TIPO'],
            'id_autor'             => ['required','integer'],
            'id_grupo'             => ['nullable','integer'],
            'rol_autor'            => ['nullable','string','max:50'],
            'coautores'            => ['nullable','array'],
            'coautores.*'          => ['integer'],
            'id_revista'           => ['required','integer'],
            'anio_publicacion'     => ['required','integer','min:1900','max:' . (date('Y') + 1)],
        ]);

        $coautores = collect($data['coautores'] ?? [])
            ->map(fn($v) => (int)$v)
            ->unique()
            ->reject(fn($v) => $v === (int)$data['id_autor'])
            ->values()
            ->all();

        DB::beginTransaction();
        try {
            $pub = DB::table('proyecto_revista')
                ->where('ID_PROYECTO_REVISTA', $id)
                ->first(['ID_PROYECTO','ID_REVISTA','ANIO_PUBLICACION']);

            if (!$pub) {
                DB::rollBack();
                return redirect()->route('publicaciones.index')->with('error','No se encontró la publicación.');
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

            return redirect()->route('publicaciones.index')->with('ok','Publicación actualizada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error','Error al actualizar: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        // TODO: eliminar publicación si corresponde
    }
    public function export(Request $request)
{
    $from = $request->query('from'); // YYYY-MM-DD
    $to   = $request->query('to');   // YYYY-MM-DD

    $filename = 'publicaciones_'.now()->format('Ymd_His').'.xlsx';

    return Excel::download(new PublicacionesExport($from, $to), $filename);
}
}
