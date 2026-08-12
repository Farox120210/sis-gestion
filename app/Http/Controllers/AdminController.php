<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        // ===== KPIs PRINCIPALES =====
        // Total publicaciones / proyectos
        $totalPublicaciones = DB::table('proyecto')->count();

        // En desarrollo
        $totalEnCurso = DB::table('proyecto as p')
            ->join('estado_proyecto as ep', 'ep.ID_ESTADO', '=', 'p.ID_ESTADO')
            ->where('ep.NOMBRE_ESTADO', 'LIKE', '%desarrollo%')
            ->count();

        // Publicados / Finalizados
        $totalFinalizados = DB::table('proyecto as p')
            ->join('estado_proyecto as ep', 'ep.ID_ESTADO', '=', 'p.ID_ESTADO')
            ->where('ep.NOMBRE_ESTADO', 'LIKE', '%publicado%')
            ->orWhere('ep.NOMBRE_ESTADO', 'LIKE', '%finalizado%')
            ->count();

        // Artículos en revista
        $totalArticulos = DB::table('proyecto_revista')->distinct('ID_PROYECTO')->count('ID_PROYECTO');

        // Total Investigadores registrados
        $totalInvestigadores = DB::table('investigador')->count();

        // Porcentaje de publicaciones finalizadas
        $porcentajeCompletado = $totalPublicaciones > 0 ? round(($totalFinalizados / $totalPublicaciones) * 100, 1) : 0;

        // ===== SERIES POR AÑO =====
        $porAnioRows = DB::table('proyecto as p')
            ->selectRaw('p.ANIO as anio, COUNT(*) as total')
            ->whereNotNull('p.ANIO')
            ->groupBy('p.ANIO')
            ->orderBy('p.ANIO')
            ->get();

        $porAnioLabels = $porAnioRows->pluck('anio');
        $porAnioData   = $porAnioRows->pluck('total');

        // ===== POR ESTADO =====
        $porEstadoRows = DB::table('proyecto as p')
            ->join('estado_proyecto as ep', 'ep.ID_ESTADO', '=', 'p.ID_ESTADO')
            ->selectRaw('ep.NOMBRE_ESTADO as estado, COUNT(*) as total')
            ->groupBy('ep.NOMBRE_ESTADO')
            ->orderByDesc('total')
            ->get();

        $porEstadoLabels = $porEstadoRows->pluck('estado');
        $porEstadoData   = $porEstadoRows->pluck('total');

        // ===== TOP 7 ÁREAS =====
        $porAreaRows = DB::table('proyecto as p')
            ->join('area_investigacion as a', 'a.ID_AREA', '=', 'p.ID_AREA')
            ->selectRaw('a.NOMBRE_AREA as area, COUNT(*) as total')
            ->groupBy('a.NOMBRE_AREA')
            ->orderByDesc('total')
            ->limit(7)
            ->get();

        $porAreaLabels = $porAreaRows->pluck('area');
        $porAreaData   = $porAreaRows->pluck('total');

        // ===== INVESTIGACIÓN POR TIPO =====
        $porTipoRows = DB::table('proyecto as p')
            ->join('tipos_proyecto as tp', 'tp.ID_TIPO', '=', 'p.ID_TIPO')
            ->selectRaw('tp.NOMBRE_TIPO as tipo, COUNT(*) as total')
            ->groupBy('tp.NOMBRE_TIPO')
            ->orderByDesc('total')
            ->get();

        $porTipoLabels = $porTipoRows->pluck('tipo');
        $porTipoData   = $porTipoRows->pluck('total');

        // ===== ÚLTIMOS 12 MESES =====
        $hasta = Carbon::now()->endOfMonth();
        $desde = (clone $hasta)->subMonths(11)->startOfMonth();

        $porMesRows = DB::table('proyecto as p')
            ->selectRaw("DATE_FORMAT(COALESCE(p.FECHA_INICIO, p.created_at, p.FECHA_FIN), '%Y-%m') as ym, COUNT(*) as total")
            ->whereBetween(DB::raw('COALESCE(p.FECHA_INICIO, p.created_at, p.FECHA_FIN)'), [$desde->toDateString(), $hasta->toDateString()])
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        $mesLabels = [];
        $mesData   = [];
        $cursor = (clone $desde);
        while ($cursor <= $hasta) {
            $ym = $cursor->format('Y-m');
            $mesLabels[] = $cursor->translatedFormat('M Y');
            $mesData[]   = isset($porMesRows[$ym]) ? (int)$porMesRows[$ym]->total : 0;
            $cursor->addMonth();
        }

        // ===== TOP INVESTIGADORES (CON DETALLES DE CORREO Y PROYECTOS) =====
        $topAutoresRows = DB::table('proyecto as p')
            ->join('investigador as i', 'i.ID_INVESTIGADOR', '=', 'p.ID_AUTOR')
            ->join('persona as pe', 'pe.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->selectRaw("i.ID_INVESTIGADOR as id, CONCAT(pe.NOMBRES, ' ', pe.APELLIDOS) as autor, i.CORREO_INSTITUCIONAL as correo, i.TIPO_INVESTIGADOR as tipo_inv, COUNT(*) as total")
            ->groupBy('i.ID_INVESTIGADOR', 'pe.NOMBRES', 'pe.APELLIDOS', 'i.CORREO_INSTITUCIONAL', 'i.TIPO_INVESTIGADOR')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topAutoresLabels = $topAutoresRows->pluck('autor');
        $topAutoresData   = $topAutoresRows->pluck('total');

        // ===== ÚLTIMAS PUBLICACIONES Y ACTIVIDAD RECIENTE (LIVE FEED) =====
        $ultimasPublicaciones = DB::table('proyecto as p')
            ->leftJoin('investigador as i', 'i.ID_INVESTIGADOR', '=', 'p.ID_AUTOR')
            ->leftJoin('persona as pe', 'pe.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->leftJoin('estado_proyecto as ep', 'ep.ID_ESTADO', '=', 'p.ID_ESTADO')
            ->leftJoin('area_investigacion as ai', 'ai.ID_AREA', '=', 'p.ID_AREA')
            ->leftJoin('tipos_proyecto as tp', 'tp.ID_TIPO', '=', 'p.ID_TIPO')
            ->select(
                'p.ID_PROYECTO as id',
                'p.TITULO as titulo',
                'p.ANIO as anio',
                'p.created_at',
                'ep.NOMBRE_ESTADO as estado',
                'ai.NOMBRE_AREA as area',
                'tp.NOMBRE_TIPO as tipo',
                DB::raw("CONCAT(pe.NOMBRES, ' ', pe.APELLIDOS) as autor")
            )
            ->orderByDesc('p.ID_PROYECTO')
            ->limit(6)
            ->get();

        return view('admin.index', compact(
            'totalPublicaciones',
            'totalEnCurso',
            'totalFinalizados',
            'totalArticulos',
            'totalInvestigadores',
            'porcentajeCompletado',
            'porAnioLabels',
            'porAnioData',
            'porEstadoLabels',
            'porEstadoData',
            'porAreaLabels',
            'porAreaData',
            'porTipoLabels',
            'porTipoData',
            'mesLabels',
            'mesData',
            'topAutoresLabels',
            'topAutoresData',
            'topAutoresRows',
            'ultimasPublicaciones'
        ));
    }
}
