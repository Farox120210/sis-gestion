<?php

namespace App\Exports;

use App\Models\Publicacion;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PublicacionesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected ?string $from = null,
        protected ?string $to = null
    ) {}

    public function query()
    {
        $q = Publicacion::query()
            ->with(['area','tipo','estado','autor','grupo'])
            ->orderByDesc('created_at');

        // Filtro por rango de creación (opcional)
        if ($this->from) {
            $q->whereDate('created_at', '>=', $this->from);
        }
        if ($this->to) {
            $q->whereDate('created_at', '<=', $this->to);
        }

        return $q;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Título',
            'Año proyecto',
            'Año publicación',
            'Fecha inicio',
            'Fecha fin',
            'Presupuesto',
            'Área',
            'Tipo',
            'Estado',
            'Autor principal',
            'Grupo',
            'Coautores (IDs)',
            'Creado el (fecha)',
            'Creado a las (hora)',
        ];
    }

    public function map($p): array
    {
        // Ajusta los nombres de campos según tu esquema real
        return [
            $p->id ?? $p->ID ?? null,
            $p->titulo ?? '',
            $p->anio ?? $p->anio_proyecto ?? null,
            $p->anio_publicacion ?? null,
            optional($p->fecha_inicio)->format('Y-m-d') ?? $p->fecha_inicio,
            optional($p->fecha_fin)->format('Y-m-d') ?? $p->fecha_fin,
            $p->presupuesto ?? null,
            // Si tus relaciones devuelven NOMBRE_* úsalas; si no, usa ->nombre
            optional($p->area)->NOMBRE_AREA ?? optional($p->area)->nombre ?? '',
            optional($p->tipo)->NOMBRE_TIPO ?? optional($p->tipo)->nombre ?? '',
            optional($p->estado)->NOMBRE_ESTADO ?? optional($p->estado)->nombre ?? '',
            optional($p->autor)->nombre ?? '',
            optional($p->grupo)->NOMBRE_GRUPO ?? optional($p->grupo)->nombre ?? '',
            // Ejemplo simple: lista de IDs de coautores si existe relación many-to-many
            method_exists($p, 'coautores') ? $p->coautores->pluck('id')->join(', ') : '',
            optional($p->created_at)->format('Y-m-d'),
            optional($p->created_at)->format('H:i:s'),
        ];
    }
}
