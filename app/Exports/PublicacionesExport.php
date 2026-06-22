<?php

namespace App\Exports;

use App\Models\Publicacion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PublicacionesExport implements FromCollection, WithHeadings
{
    protected $tipo;
    protected $q;

    public function __construct($tipo = null, $q = null)
    {
        $this->tipo = $tipo;
        $this->q    = $q;
    }

    public function collection()
    {
        $query = Publicacion::query();

        // Filtro por tipo de investigación
        if ($this->tipo) {
            $query->where('tipo', $this->tipo);
        }

        // Buscador general (igual que en el index)
        if ($this->q) {
            $search = $this->q;
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                    ->orWhere('autores', 'like', "%{$search}%")
                    ->orWhere('grupo', 'like', "%{$search}%")
                    ->orWhere('area', 'like', "%{$search}%");
            });
        }

        return $query
            ->select('titulo', 'autores', 'grupo', 'area', 'tipo')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Título',
            'Autores',
            'Grupo de investigación',
            'Área investigativa',
            'Tipo de investigación',
        ];
    }
}
