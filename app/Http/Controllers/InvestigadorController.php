<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvestigadorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
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
            ->groupBy('i.ID_INVESTIGADOR', 'i.TIPO_INVESTIGADOR', 'i.CORREO_INSTITUCIONAL', 'p.NOMBRES', 'p.APELLIDOS')
            ->orderBy('p.APELLIDOS')
            ->paginate(10);

        return view('admin.investigadores.index', compact('investigadores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
