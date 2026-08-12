<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Exports\InvestigadoresExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\AuditLoggerService;

class InvestigadorController extends Controller

{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
    $grupos = DB::table('grupos_investigacion')
        ->select('ID_GRUPO', 'NOMBRE_GRUPO') // <-- si tu columna es NOMBRE_GRUPO, usa ese nombre
        ->orderBy('NOMBRE_GRUPO')            // idem: ajusta al nombre real de tu BD
        ->get();

    
     $tipos = DB::table('investigador')
         ->whereNotNull('TIPO_INVESTIGADOR')
        ->distinct()
         ->orderBy('TIPO_INVESTIGADOR')
    ->pluck('TIPO_INVESTIGADOR');

        $investigadores = DB::table('investigador as i')
            ->join('persona as p', 'p.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->leftJoin('investigador_grupo as ig', 'ig.ID_INVESTIGADOR', '=', 'i.ID_INVESTIGADOR')
            ->leftJoin('grupos_investigacion as g', 'g.ID_GRUPO', '=', 'ig.ID_GRUPO')
            ->selectRaw('
            i.ID_INVESTIGADOR as id,
            i.TIPO_INVESTIGADOR as tipo,
            i.CORREO_INSTITUCIONAL as correo,
            CONCAT(p.NOMBRES, " ", p.APELLIDOS) as nombre_completo,
            COALESCE(GROUP_CONCAT(DISTINCT g.NOMBRE_GRUPO ORDER BY g.NOMBRE_GRUPO SEPARATOR "; "), "") as grupos
        ')
            ->when($request->q, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('p.NOMBRES', 'like', "%{$q}%")
                        ->orWhere('p.APELLIDOS', 'like', "%{$q}%")
                        ->orWhere(DB::raw("CONCAT(p.NOMBRES,' ',p.APELLIDOS)"), 'like', "%{$q}%")
                        ->orWhere('i.CORREO_INSTITUCIONAL', 'like', "%{$q}%")
                        ->orWhere('i.TIPO_INVESTIGADOR', 'like', "%{$q}%")
                        ->orWhere('g.NOMBRE_GRUPO', 'like', "%{$q}%");
                });
            })
            ->groupBy('i.ID_INVESTIGADOR', 'i.TIPO_INVESTIGADOR', 'i.CORREO_INSTITUCIONAL', 'p.NOMBRES', 'p.APELLIDOS')
            ->orderBy('p.APELLIDOS')
            ->paginate(10);

        // Para que al paginar no se pierda el parámetro q
        $investigadores->appends($request->only('q'));

    return view('admin.investigadores.index', compact('investigadores', 'grupos', 'tipos'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $grupos = DB::table('grupos_investigacion')
            ->select('ID_GRUPO', 'NOMBRE_GRUPO')
            ->orderBy('NOMBRE_GRUPO')
            ->get();
        $grupos = DB::table('grupos_investigacion')
            ->select('ID_GRUPO', 'NOMBRE_GRUPO')
            ->orderBy('NOMBRE_GRUPO')
            ->get();

        $areas = DB::table('area_investigacion')
            ->select('ID_AREA', 'NOMBRE_AREA')
            ->orderBy('NOMBRE_AREA')
            ->get();

        return view('admin.investigadores.create', compact('grupos', 'areas'));
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    $data = $request->validate([
        // Persona
        'nombres'          => ['required', 'string', 'max:100'],
        'apellidos'        => ['required', 'string', 'max:100'],
        'direccion'        => ['nullable', 'string', 'max:200'],
        'telefono'         => ['nullable', 'string', 'max:15'],
        'correo_personal'  => ['nullable', 'email', 'max:100'],

        // Investigador
        'tipo_investigador'     => ['required', 'string', 'max:50'],
        'correo_institucional'  => ['nullable', 'email', 'max:100'],

        // Nuevos campos
        'cod_orcid'        => ['nullable', 'string', 'max:20'],
        'grado_academico'  => ['nullable', 'string', 'max:100'],
        'institucion'      => ['nullable', 'string', 'max:150'],

        // Grupo inicial (coincide con el create.blade)
        'id_grupo'         => ['nullable', 'integer', Rule::exists('grupos_investigacion', 'ID_GRUPO')],
    ]);

    DB::beginTransaction();
    try {
        // 1) Crear persona
        $idPersona = DB::table('persona')->insertGetId([
            'NOMBRES'         => $data['nombres'],
            'APELLIDOS'       => $data['apellidos'],
            'DIRECCION'       => $data['direccion'] ?? null,
            'TELEFONO'        => $data['telefono'] ?? null,
            'CORREO_PERSONAL' => $data['correo_personal'] ?? null,
        ]);

        // 2) Crear investigador
        $idInv = DB::table('investigador')->insertGetId([
            'ID_PERSONA'           => $idPersona,
            'TIPO_INVESTIGADOR'    => $data['tipo_investigador'],
            'CORREO_INSTITUCIONAL' => $data['correo_institucional'] ?? null,
            'cod_orcid'            => $data['cod_orcid'] ?? null,
            'grado_academico'      => $data['grado_academico'] ?? null,
            'institucion'          => $data['institucion'] ?? null,
        ]);

        // 3) Guardar grupo (si eligió uno)
        if (!empty($data['id_grupo'])) {
            DB::table('investigador_grupo')->insert([
                'ID_INVESTIGADOR' => $idInv,
                'ID_GRUPO'        => (int) $data['id_grupo'],
                'FECHA_INGRESO'   => now()->toDateString(),
            ]);
        }

        DB::commit();

        AuditLoggerService::log('Investigadores', 'CREAR', "Se registró un nuevo investigador: '{$data['nombres']} {$data['apellidos']}' ({$data['correo']}).");

        return redirect()->route('investigadores.index')
            ->with('ok', 'Investigador registrado correctamente.');
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Error al registrar investigador', ['msg' => $e->getMessage()]);
        return back()->withInput()->with('error', 'No se pudo registrar: ' . $e->getMessage());
    }
}



    /**
     * Display the specified resource.
     */
public function show(string $id)
{
    $inv = DB::table('investigador as i')
        ->join('persona as p', 'p.ID_PERSONA', '=', 'i.ID_PERSONA')
        ->where('i.ID_INVESTIGADOR', $id)
        ->select([
            'i.ID_INVESTIGADOR',
            'p.ID_PERSONA',
            'p.NOMBRES',
            'p.APELLIDOS',
            'p.DIRECCION',
            'p.TELEFONO',
            'p.CORREO_PERSONAL',
            'i.TIPO_INVESTIGADOR',
            'i.CORREO_INSTITUCIONAL',

            // NUEVOS CAMPOS
            'i.cod_orcid',
            'i.grado_academico',
            'i.institucion',
        ])
        ->first();

    if (!$inv) abort(404);

    $grupos = DB::table('investigador_grupo as ig')
        ->join('grupos_investigacion as g', 'g.ID_GRUPO', '=', 'ig.ID_GRUPO')
        ->where('ig.ID_INVESTIGADOR', $id)
        ->select([
            'g.NOMBRE_GRUPO',
            'ig.FECHA_INGRESO',
            'ig.FECHA_SALIDA',
        ])
        ->get();

    return view('admin.investigadores.show', compact('inv', 'grupos'));
}



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
{
    $inv = DB::table('investigador as i')
        ->join('persona as p', 'p.ID_PERSONA', '=', 'i.ID_PERSONA')
        ->where('i.ID_INVESTIGADOR', $id)
        ->select([
            'i.ID_INVESTIGADOR',
            'p.ID_PERSONA',
            'p.NOMBRES',
            'p.APELLIDOS',
            'p.DIRECCION',
            'p.TELEFONO',
            'p.CORREO_PERSONAL',
            'i.TIPO_INVESTIGADOR',
            'i.CORREO_INSTITUCIONAL',
            'i.cod_orcid',
            'i.grado_academico',
            'i.institucion',
        ])
        ->first();

    if (!$inv) abort(404);

    $grupos = DB::table('grupos_investigacion')
        ->select('ID_GRUPO', 'NOMBRE_GRUPO')
        ->orderBy('NOMBRE_GRUPO')
        ->get();

    $gruposActuales = DB::table('investigador_grupo')
        ->where('ID_INVESTIGADOR', $id)
        ->pluck('ID_GRUPO')
        ->toArray();

    return view('admin.investigadores.edit', compact('inv', 'grupos', 'gruposActuales'));
}


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
{
    $data = $request->validate([
        // Persona
        'nombres'          => ['required', 'string', 'max:100'],
        'apellidos'        => ['required', 'string', 'max:100'],
        'direccion'        => ['nullable', 'string', 'max:200'],
        'telefono'         => ['nullable', 'string', 'max:15'],
        'correo_personal'  => ['nullable', 'email', 'max:100'],

        // Investigador
        'tipo_investigador'     => ['required', 'string', 'max:50'],
        'correo_institucional'  => ['nullable', 'email', 'max:100'],

        // Nuevos campos
        'cod_orcid'        => ['nullable', 'string', 'max:20'],
        'grado_academico'  => ['nullable', 'string', 'max:100'],
        'institucion'      => ['nullable', 'string', 'max:150'],

        // Grupos
        'grupos'           => ['nullable', 'array'],
        'grupos.*'         => ['integer', Rule::exists('grupos_investigacion', 'ID_GRUPO')],
    ]);

    DB::beginTransaction();
    try {

        $inv = DB::table('investigador')->where('ID_INVESTIGADOR', $id)->first();
        if (!$inv) throw new \RuntimeException('No se encontró el investigador.');

        // 1) Actualizar persona
        DB::table('persona')
            ->where('ID_PERSONA', $inv->ID_PERSONA)
            ->update([
                'NOMBRES'         => $data['nombres'],
                'APELLIDOS'       => $data['apellidos'],
                'DIRECCION'       => $data['direccion'] ?? null,
                'TELEFONO'        => $data['telefono'] ?? null,
                'CORREO_PERSONAL' => $data['correo_personal'] ?? null,
            ]);

        // 2) Actualizar investigador
        DB::table('investigador')
            ->where('ID_INVESTIGADOR', $id)
            ->update([
                'TIPO_INVESTIGADOR'    => $data['tipo_investigador'],
                'CORREO_INSTITUCIONAL' => $data['correo_institucional'] ?? null,
                'cod_orcid'            => $data['cod_orcid'] ?? null,
                'grado_academico'      => $data['grado_academico'] ?? null,
                'institucion'          => $data['institucion'] ?? null,
            ]);

        // 3) Sincronizar grupos
        if ($request->has('grupos')) {

            $nuevos = collect($data['grupos'] ?? [])
                ->map(fn($v) => (int)$v)
                ->unique()
                ->values()
                ->all();

            $actuales = DB::table('investigador_grupo')
                ->where('ID_INVESTIGADOR', $id)
                ->pluck('ID_GRUPO')
                ->map(fn($v) => (int)$v)
                ->all();

            $agregar = array_diff($nuevos, $actuales);
            $quitar  = array_diff($actuales, $nuevos);

            foreach ($agregar as $gid) {
                DB::table('investigador_grupo')->updateOrInsert(
                    ['ID_INVESTIGADOR' => $id, 'ID_GRUPO' => $gid],
                    ['FECHA_INGRESO' => now()->toDateString()]
                );
            }

            if (!empty($quitar)) {
                DB::table('investigador_grupo')
                    ->where('ID_INVESTIGADOR', $id)
                    ->whereIn('ID_GRUPO', $quitar)
                    ->delete();
            }
        }

        DB::commit();

        AuditLoggerService::log('Investigadores', 'ACTUALIZAR', "Se actualizó la información del investigador ID {$id}.");

        return redirect()->route('investigadores.index')
            ->with('ok', 'Investigador actualizado correctamente.');

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Error al actualizar investigador', ['msg' => $e->getMessage()]);
        return back()->withInput()->with('error', 'No se pudo actualizar: ' . $e->getMessage());
    }
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            // Buscar investigador
            $inv = DB::table('investigador')->where('ID_INVESTIGADOR', $id)->first();
            if (!$inv) {
                return redirect()->route('investigadores.index')
                    ->with('error', 'El investigador no existe.');
            }

            // 1) Eliminar relaciones en investigador_grupo
            DB::table('investigador_grupo')
                ->where('ID_INVESTIGADOR', $id)
                ->delete();

            // 2) Eliminar el investigador
            DB::table('investigador')
                ->where('ID_INVESTIGADOR', $id)
                ->delete();

            // 3) Eliminar persona asociada (opcional, si no la necesitas en otras tablas)
            DB::table('persona')
                ->where('ID_PERSONA', $inv->ID_PERSONA)
                ->delete();

            DB::commit();

            AuditLoggerService::log('Investigadores', 'ELIMINAR', "Se eliminó permanentemente al investigador ID {$id}.");

            return redirect()->route('investigadores.index')
                ->with('ok', 'Investigador eliminado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al eliminar investigador', ['msg' => $e->getMessage()]);
            return redirect()->route('investigadores.index')
                ->with('error', 'No se pudo eliminar el investigador: ' . $e->getMessage());
        }
    }
    public function exportGeneral()
    {
        return Excel::download(new InvestigadoresExport(), 'investigadores.xlsx');
    }

    public function exportFiltrado(Request $request)
    {
        $filtros = [
            'grupo' => $request->grupo,
            'tipo'  => $request->tipo,
        ];

        return Excel::download(new InvestigadoresExport($filtros), 'reporte_investigadores.xlsx');
    }
}
