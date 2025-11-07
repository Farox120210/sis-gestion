<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        // Asegúrate que en .env: DB_DATABASE=sistema_control

        // ===== KPIs =====
        // Total proyectos
        $totalPublicaciones = DB::table('proyecto')->count();

        // En curso / Finalizado (por nombre en estado_proyecto)
        $totalEnCurso = DB::table('proyecto as p')
            ->join('estado_proyecto as ep', 'ep.ID_ESTADO', '=', 'p.ID_ESTADO')
            ->where('ep.NOMBRE_ESTADO', 'En curso')
            ->count();

        $totalFinalizados = DB::table('proyecto as p')
            ->join('estado_proyecto as ep', 'ep.ID_ESTADO', '=', 'p.ID_ESTADO')
            ->where('ep.NOMBRE_ESTADO', 'Publicado')
            ->count();

        // Artículos = proyectos que tienen al menos un registro en proyecto_revista
        $totalArticulos = DB::table('proyecto_revista')->distinct('ID_PROYECTO')->count('ID_PROYECTO');

        // ===== Series por AÑO =====
        // Usa p.ANIO (existe en tu tabla). Orden ascendente.
        $porAnioRows = DB::table('proyecto as p')
            ->selectRaw('p.ANIO as anio, COUNT(*) as total')
            ->groupBy('p.ANIO')
            ->orderBy('p.ANIO')
            ->get();

        $porAnioLabels = $porAnioRows->pluck('anio');
        $porAnioData   = $porAnioRows->pluck('total');

        // ===== Por ESTADO =====
        $porEstadoRows = DB::table('proyecto as p')
            ->join('estado_proyecto as ep', 'ep.ID_ESTADO', '=', 'p.ID_ESTADO')
            ->selectRaw('ep.NOMBRE_ESTADO as estado, COUNT(*) as total')
            ->groupBy('ep.NOMBRE_ESTADO')
            ->orderByDesc('total')
            ->get();

        $porEstadoLabels = $porEstadoRows->pluck('estado');
        $porEstadoData   = $porEstadoRows->pluck('total');

        // ===== Top ÁREAS (7) =====
        $porAreaRows = DB::table('proyecto as p')
            ->join('area_investigacion as a', 'a.ID_AREA', '=', 'p.ID_AREA')
            ->selectRaw('a.NOMBRE_AREA as area, COUNT(*) as total')
            ->groupBy('a.NOMBRE_AREA')
            ->orderByDesc('total')
            ->limit(7)
            ->get();

        $porAreaLabels = $porAreaRows->pluck('area');
        $porAreaData   = $porAreaRows->pluck('total');

        // ===== Tipo (Artículo vs Proyecto) =====
        // Artículo: existe en proyecto_revista. Proyecto: no tiene revista asociada.
        $conRevista = DB::table('proyecto as p')
            ->join('proyecto_revista as pr', 'pr.ID_PROYECTO', '=', 'p.ID_PROYECTO')
            ->distinct('p.ID_PROYECTO')
            ->count('p.ID_PROYECTO');

        $sinRevista = $totalPublicaciones - $conRevista;

        $porTipoLabels = collect(['Proyecto','Artículo']);
        $porTipoData   = collect([$sinRevista, $conRevista]);

        // ===== Últimos 12 meses (por FECHA_INICIO/FECHA_FIN) =====
        // Tomamos COALESCE(FECHA_INICIO, FECHA_FIN) para agrupar por mes.
        $hasta = Carbon::now()->endOfMonth();
        $desde = (clone $hasta)->subMonths(11)->startOfMonth();

        $porMesRows = DB::table('proyecto as p')
            ->selectRaw("DATE_FORMAT(COALESCE(p.FECHA_INICIO, p.FECHA_FIN), '%Y-%m') as ym, COUNT(*) as total")
            ->whereBetween(DB::raw('COALESCE(p.FECHA_INICIO, p.FECHA_FIN)'), [$desde->toDateString(), $hasta->toDateString()])
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        $mesLabels = [];
        $mesData   = [];
        $cursor = (clone $desde);
        while ($cursor <= $hasta) {
            $ym = $cursor->format('Y-m');
            $mesLabels[] = $cursor->isoFormat('MMM YYYY'); // requiere locale; si no, usa $cursor->format('M Y')
            $mesData[]   = isset($porMesRows[$ym]) ? (int)$porMesRows[$ym]->total : 0;
            $cursor->addMonth();
        }

        // ===== Top 5 AUTORES =====
        // Autor principal es p.ID_AUTOR -> investigador -> persona
        $topAutoresRows = DB::table('proyecto as p')
            ->join('investigador as i', 'i.ID_INVESTIGADOR', '=', 'p.ID_AUTOR')
            ->join('persona as pe', 'pe.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->selectRaw("CONCAT(pe.NOMBRES, ' ', pe.APELLIDOS) as autor, COUNT(*) as total")
            ->groupBy('autor')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topAutoresLabels = $topAutoresRows->pluck('autor');
        $topAutoresData   = $topAutoresRows->pluck('total');

        return view('admin.index', compact(
            'totalPublicaciones','totalEnCurso','totalFinalizados','totalArticulos',
            'porAnioLabels','porAnioData',
            'porEstadoLabels','porEstadoData',
            'porAreaLabels','porAreaData',
            'porTipoLabels','porTipoData',
            'mesLabels','mesData',
            'topAutoresLabels','topAutoresData'
        ));
    }
}
