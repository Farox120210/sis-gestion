<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InvestigadoresExport implements FromCollection, WithHeadings
{
    protected $filtros;

    public function __construct($filtros = [])
    {
        $this->filtros = $filtros;
    }

    public function collection()
    {
        $query = DB::table('investigador as i')
            ->join('persona as p', 'p.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->leftJoin('investigador_grupo as ig', 'ig.ID_INVESTIGADOR', '=', 'i.ID_INVESTIGADOR')
            ->leftJoin('grupos_investigacion as g', 'g.ID_GRUPO', '=', 'ig.ID_GRUPO')
            ->selectRaw('
                i.ID_INVESTIGADOR,
                CONCAT(p.NOMBRES, " ", p.APELLIDOS) as nombre,
                i.TIPO_INVESTIGADOR,
                i.CORREO_INSTITUCIONAL,
                COALESCE(GROUP_CONCAT(DISTINCT g.NOMBRE_GRUPO SEPARATOR "; "), "") as grupos
            ')
            ->groupBy('i.ID_INVESTIGADOR', 'p.NOMBRES', 'p.APELLIDOS', 'i.TIPO_INVESTIGADOR', 'i.CORREO_INSTITUCIONAL');

        // Aplicar filtros dinámicos
        if (!empty($this->filtros['grupo'])) {
            $query->where('g.ID_GRUPO', $this->filtros['grupo']);
        }
        if (!empty($this->filtros['tipo'])) {
            $query->where('i.TIPO_INVESTIGADOR', $this->filtros['tipo']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Tipo de Investigador',
            'Correo Institucional',
            'Grupos de investigación',
        ];
    }
}
