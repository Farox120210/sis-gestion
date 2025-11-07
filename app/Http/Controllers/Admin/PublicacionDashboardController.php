<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class PublicacionDashboardController extends Controller
{
    public function index()
    {
        // ========= KPIs (tomados de PROYECTO) =========
        $totalPublicaciones = (int) Proyecto::count();

        // Estados por nombre en catálogo estado_proyecto
        $idEnCurso = DB::table('estado_proyecto')->where('NOMBRE_ESTADO','En curso')->value('ID_ESTADO');
        $idFinal   = DB::table('estado_proyecto')->where('NOMBRE_ESTADO','Finalizado')->value('ID_ESTADO');

        $totalEnCurso     = $idEnCurso ? (int) Proyecto::where('ID_ESTADO', $idEnCurso)->count() : 0;
        $totalFinalizados = $idFinal   ? (int) Proyecto::where('ID_ESTADO', $idFinal)->count()   : 0;

        // “Publicados”: proyectos que tienen al menos un registro en proyecto_revista
        $totalPublicados = (int) Proyecto::whereExists(function ($q) {
            $q->select(DB::raw(1))
              ->from('proyecto_revista')
              ->whereColumn('proyecto_revista.ID_PROYECTO', 'proyecto.ID_PROYECTO');
        })->count();

        // ========= Gráfico: Publicaciones por año (ANIO) =========
        $porAnio = Proyecto::select('ANIO', DB::raw('COUNT(*) as total'))
            ->groupBy('ANIO')->orderBy('ANIO')->get();

        $porAnioLabels = $porAnio->pluck('ANIO')->map(fn($v)=>(string)$v)->all();
        $porAnioData   = $porAnio->pluck('total')->map(fn($v)=>(int)$v)->all();

        // ========= Gráfico: Publicaciones por estado =========
        $porEstado = Proyecto::select('estado_proyecto.NOMBRE_ESTADO as nombre', DB::raw('COUNT(*) as total'))
            ->join('estado_proyecto','estado_proyecto.ID_ESTADO','=','proyecto.ID_ESTADO')
            ->groupBy('estado_proyecto.NOMBRE_ESTADO')
            ->orderByDesc('total')
            ->get();

        $porEstadoLabels = $porEstado->pluck('nombre')->map(fn($v)=>(string)$v)->all();
        $porEstadoData   = $porEstado->pluck('total')->map(fn($v)=>(int)$v)->all();

        // ========= Gráfico: Top 7 áreas de investigación =========
        $porArea = Proyecto::select('area_investigacion.NOMBRE_AREA as nombre', DB::raw('COUNT(*) as total'))
            ->join('area_investigacion','area_investigacion.ID_AREA','=','proyecto.ID_AREA')
            ->groupBy('area_investigacion.NOMBRE_AREA')
            ->orderByDesc('total')->limit(7)->get();

        $porAreaLabels = $porArea->pluck('nombre')->map(fn($v)=>(string)$v)->all();
        $porAreaData   = $porArea->pluck('total')->map(fn($v)=>(int)$v)->all();

        // ========= Gráfico: Distribución por TIPO DE PROYECTO =========
        // Usa catálogo tipos_proyecto.ID_TIPO → proyecto.ID_TIPO
        $porTipo = Proyecto::select(DB::raw('COALESCE(tipos_proyecto.NOMBRE_TIPO, "Sin tipo") as nombre'),
                                    DB::raw('COUNT(*) as total'))
            ->leftJoin('tipos_proyecto','tipos_proyecto.ID_TIPO','=','proyecto.ID_TIPO')
            ->groupBy('nombre')
            ->orderByDesc('total')
            ->get();

        $porTipoLabels = $porTipo->pluck('nombre')->map(fn($v)=>(string)$v)->all();
        $porTipoData   = $porTipo->pluck('total')->map(fn($v)=>(int)$v)->all();

        // ========= Gráfico: Publicaciones por mes (últimos 12) =========
        // Basado en FECHA_INICIO; puedes cambiar a FECHA_FIN si te conviene.
        $inicio = Carbon::now()->startOfMonth()->subMonths(11);
        $fin    = Carbon::now()->endOfMonth();
        $period = CarbonPeriod::create($inicio, '1 month', $fin);

        $crudos = Proyecto::selectRaw("DATE_FORMAT(FECHA_INICIO, '%Y-%m') as ym, COUNT(*) as total")
            ->whereNotNull('FECHA_INICIO')
            ->whereBetween('FECHA_INICIO', [$inicio, $fin])
            ->groupBy('ym')->orderBy('ym')
            ->pluck('total','ym');  // mapa ym => total

        $mesLabels = [];
        $mesData   = [];
        foreach ($period as $m) {
            $ym = $m->format('Y-m');
            $mesLabels[] = $ym;                  // p.ej. "2025-09"
            $mesData[]   = (int) ($crudos[$ym] ?? 0);
        }

        // ========= Gráfico: Top 5 autores =========
        $topAutores = Proyecto::select('investigador.ID_INVESTIGADOR', DB::raw('COUNT(*) as total'),
                                DB::raw("CONCAT(persona.NOMBRES,' ',persona.APELLIDOS) as autor"))
            ->join('investigador','investigador.ID_INVESTIGADOR','=','proyecto.ID_AUTOR')
            ->join('persona','persona.ID_PERSONA','=','investigador.ID_PERSONA')
            ->groupBy('investigador.ID_INVESTIGADOR','persona.NOMBRES','persona.APELLIDOS')
            ->orderByDesc('total')->limit(5)->get();

        $topAutoresLabels = $topAutores->pluck('autor')->map(fn($v)=>(string)$v)->all();
        $topAutoresData   = $topAutores->pluck('total')->map(fn($v)=>(int)$v)->all();

        // ========= Render =========
        return view('admin.publicaciones.index', compact(
            'totalPublicaciones','totalEnCurso','totalFinalizados','totalPublicados',
            'porAnioLabels','porAnioData',
            'porEstadoLabels','porEstadoData',
            'porAreaLabels','porAreaData',
            'porTipoLabels','porTipoData',
            'mesLabels','mesData',
            'topAutoresLabels','topAutoresData'
        )); 
    }
}
