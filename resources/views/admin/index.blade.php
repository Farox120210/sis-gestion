@extends('layouts.admin')

@section('content')
<div class="container-fluid px-3">

    <!-- BANNER INSTITUCIONAL ESAT - UNASAM -->
    <div class="card border-0 shadow-sm rounded-lg mb-4 overflow-hidden" style="background: linear-gradient(135deg, #071444 0%, #1e3a8a 50%, #2A7BCA 100%);">
        <div class="card-body p-4 text-white">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-7 mb-3 mb-md-0">
                    <div class="d-flex align-items-center mb-2">
                        <img src="{{ asset('dist/img/LOGO_UNASAM-removebg-preview.png') }}" alt="Logo UNASAM" class="mr-3" style="max-height: 52px; width: auto; filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.3));">
                        <div>
                            <span class="badge badge-warning text-dark font-weight-bold px-2 py-1 text-uppercase" style="font-size: 0.82rem; letter-spacing: 0.5px;">
                                Sistema de Gestión De la Producción Científica ESAT - UNASAM
                            </span>
                            <h2 class="h2 font-weight-bold mb-0 text-white mt-1" style="font-size: 1.65rem;">
                                Control y Monitoreo de la Producción Científica
                            </h2>
                        </div>
                    </div>
                    <p class="text-light mb-0 font-weight-light" style="font-size: 1.05rem; opacity: 0.95;">
                        Bienvenido, <strong>{{ Auth::user()->name }}</strong> ({{ Auth::user()->rol === 'super_admin' ? 'Super Administrador' : 'Publicador' }}). 
                        Plataforma institucional para el seguimiento de proyectos, artículos de revistas e investigadores de la UNASAM.
                    </p>
                </div>
                <div class="col-lg-4 col-md-5 text-md-right">
                    <div class="d-inline-flex flex-column align-items-md-end">
                        <span class="badge badge-light px-3 py-2 mb-2 text-dark font-weight-bold shadow-sm" style="font-size: 0.92rem;">
                            <i class="far fa-calendar-alt text-primary mr-1"></i> {{ date('d \d\e F, Y') }}
                        </span>
                        @if(Auth::user()->rol === 'super_admin' || Auth::user()->rol === 'publicador')
                            <div class="btn-group">
                                <a href="{{ route('publicaciones.create') }}" class="btn btn-warning btn-md font-weight-bold shadow-sm text-dark mr-2" style="font-size: 0.9rem;">
                                    <i class="fas fa-plus-circle mr-1"></i> Nueva Publicación
                                </a>
                                <a href="{{ route('investigadores.create') }}" class="btn btn-outline-light btn-md font-weight-bold shadow-sm" style="font-size: 0.9rem;">
                                    <i class="fas fa-user-plus mr-1"></i> Nuevo Investigador
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TARJETAS KPIS / INDICADORES INFORMATIVOS -->
    <div class="row mb-4">
        <!-- Total Publicaciones -->
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100 rounded-lg overflow-hidden position-relative hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="font-weight-bold text-uppercase text-muted" style="font-size: 0.82rem;">Total Proyectos</span>
                        <div class="icon-circle bg-primary-subtle text-primary">
                            <i class="fas fa-book-open"></i>
                        </div>
                    </div>
                    <h3 class="font-weight-bold text-dark mb-1" style="font-size: 2rem;">{{ $totalPublicaciones ?? 0 }}</h3>
                    <div class="progress progress-xs mt-2" style="height: 5px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
                    </div>
                    <small class="text-muted mt-1 d-block" style="font-size: 0.82rem;">Registrados en la plataforma</small>
                </div>
            </div>
        </div>

        <!-- En Desarrollo -->
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100 rounded-lg overflow-hidden position-relative hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="font-weight-bold text-uppercase text-warning" style="font-size: 0.82rem;">En Desarrollo</span>
                        <div class="icon-circle bg-warning-subtle text-warning">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                    <h3 class="font-weight-bold text-dark mb-1" style="font-size: 2rem;">{{ $totalEnCurso ?? 0 }}</h3>
                    <div class="progress progress-xs mt-2" style="height: 5px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $totalPublicaciones > 0 ? round(($totalEnCurso/$totalPublicaciones)*100) : 0 }}%"></div>
                    </div>
                    <small class="text-muted mt-1 d-block" style="font-size: 0.82rem;">Investigaciones activas</small>
                </div>
            </div>
        </div>

        <!-- Publicados / Concluidos -->
        <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100 rounded-lg overflow-hidden position-relative hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="font-weight-bold text-uppercase text-success" style="font-size: 0.82rem;">Publicados / Concluidos</span>
                        <div class="icon-circle bg-success-subtle text-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <h3 class="font-weight-bold text-dark mb-1 mr-2" style="font-size: 2rem;">{{ $totalFinalizados ?? 0 }}</h3>
                        <span class="badge badge-success-light text-success font-weight-bold" style="font-size: 0.82rem;">
                            {{ $porcentajeCompletado }}% completado
                        </span>
                    </div>
                    <div class="progress progress-xs mt-2" style="height: 5px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $porcentajeCompletado }}%"></div>
                    </div>
                    <small class="text-muted mt-1 d-block" style="font-size: 0.82rem;">Publicaciones finalizadas</small>
                </div>
            </div>
        </div>

        <!-- Artículos Indexados -->
        <div class="col-xl-2 col-md-6 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100 rounded-lg overflow-hidden position-relative hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="font-weight-bold text-uppercase text-info" style="font-size: 0.82rem;">Artículos Revistas</span>
                        <div class="icon-circle bg-info-subtle text-info">
                            <i class="fas fa-newspaper"></i>
                        </div>
                    </div>
                    <h3 class="font-weight-bold text-dark mb-1" style="font-size: 2rem;">{{ $totalArticulos ?? 0 }}</h3>
                    <div class="progress progress-xs mt-2" style="height: 5px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 100%"></div>
                    </div>
                    <small class="text-muted mt-1 d-block" style="font-size: 0.82rem;">Publicados en revistas</small>
                </div>
            </div>
        </div>

        <!-- Investigadores Activos -->
        <div class="col-xl-3 col-md-6 col-sm-12 mb-3">
            <div class="card border-0 shadow-sm h-100 rounded-lg overflow-hidden position-relative hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="font-weight-bold text-uppercase text-purple" style="font-size: 0.82rem;">Comunidad Científica</span>
                        <div class="icon-circle bg-purple-subtle text-purple">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <h3 class="font-weight-bold text-dark mb-1" style="font-size: 2rem;">{{ $totalInvestigadores ?? 0 }} <span class="font-weight-normal text-muted" style="font-size: 1rem;">Investigadores</span></h3>
                    <div class="progress progress-xs mt-2" style="height: 5px;">
                        <div class="progress-bar bg-purple" role="progressbar" style="width: 100%"></div>
                    </div>
                    <small class="text-muted mt-1 d-block" style="font-size: 0.82rem;">Autores y coautores registrados</small>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN CENTRAL: MONITOREO DE ACTIVIDAD CIENTÍFICA EN VIVO + TOP INVESTIGADORES -->
    <div class="row mb-4">
        <!-- Tabla de Últimas Investigaciones Registradas -->
        <div class="col-lg-8 mb-3">
            <div class="card border-0 shadow-sm rounded-lg h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1.15rem;">
                            <i class="fas fa-stream text-primary mr-2"></i>Últimas Investigaciones Registradas
                        </h3>
                        <small class="text-muted d-block mt-1" style="font-size: 0.88rem;">Evidencia del avance y publicaciones de los investigadores en tiempo real</small>
                    </div>
                    <a href="{{ url('/admin/publicaciones') }}" class="btn btn-outline-primary btn-sm font-weight-bold rounded-pill px-3" style="font-size: 0.85rem;">
                        Ver Todas <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.95rem;">
                        <thead class="bg-light text-muted text-uppercase" style="font-size: 0.82rem;">
                            <tr>
                                <th>Título de Investigación</th>
                                <th>Autor Principal</th>
                                <th>Área</th>
                                <th>Estado</th>
                                <th>Año</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimasPublicaciones as $pub)
                                <tr>
                                    <td>
                                        <a href="{{ route('publicaciones.show', $pub->id) }}" class="font-weight-bold text-dark text-decoration-none hover-blue" style="max-width: 300px; display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 0.95rem;">
                                            {{ $pub->titulo }}
                                        </a>
                                        @if($pub->tipo)
                                            <br><small class="text-muted" style="font-size: 0.82rem;"><i class="fas fa-tag mr-1"></i>{{ $pub->tipo }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle mr-2 bg-primary-subtle text-primary font-weight-bold" style="font-size: 0.8rem;">
                                                {{ strtoupper(substr($pub->autor ?? 'A', 0, 2)) }}
                                            </div>
                                            <span class="text-dark font-weight-semibold" style="font-size: 0.9rem;">{{ $pub->autor ?? 'Sin asignar' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-light border text-secondary font-weight-normal px-2 py-1" style="font-size: 0.82rem;">
                                            {{ $pub->area ?? 'General' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if(str_contains(strtolower($pub->estado), 'desarrollo'))
                                            <span class="badge badge-warning-subtle text-warning-emphasis font-weight-bold px-2 py-1" style="font-size: 0.82rem;">
                                                <i class="fas fa-circle mr-1 text-warning" style="font-size: 6px;"></i>En desarrollo
                                            </span>
                                        @elseif(str_contains(strtolower($pub->estado), 'publicado') || str_contains(strtolower($pub->estado), 'finalizado'))
                                            <span class="badge badge-success-subtle text-success-emphasis font-weight-bold px-2 py-1" style="font-size: 0.82rem;">
                                                <i class="fas fa-circle mr-1 text-success" style="font-size: 6px;"></i>Publicado
                                            </span>
                                        @else
                                            <span class="badge badge-secondary-subtle text-secondary font-weight-bold px-2 py-1" style="font-size: 0.82rem;">
                                                {{ $pub->estado ?? 'Registrado' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td><span class="font-weight-bold text-muted" style="font-size: 0.9rem;">{{ $pub->anio ?? date('Y') }}</span></td>
                                    <td class="text-center">
                                        <a href="{{ route('publicaciones.show', $pub->id) }}" class="btn btn-light btn-sm text-primary border" title="Ver Detalle" style="font-size: 0.85rem;">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted" style="font-size: 0.95rem;">
                                        <i class="fas fa-folder-open fa-2x mb-2 d-block text-muted"></i>
                                        No hay publicaciones registradas recientemente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Panel de Investigadores Destacados (Ranking) -->
        <div class="col-lg-4 mb-3">
            <div class="card border-0 shadow-sm rounded-lg h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <h3 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1.15rem;">
                        <i class="fas fa-award text-warning mr-2"></i>Top Investigadores Activos
                    </h3>
                    <a href="{{ url('/admin/investigadores') }}" class="btn btn-link btn-xs text-muted p-0 font-weight-bold" style="font-size: 0.85rem;">
                        Ver lista completa
                    </a>
                </div>
                <div class="card-body p-3">
                    <div class="list-group list-group-flush">
                        @forelse($topAutoresRows as $index => $autor)
                            <div class="list-group-item border-0 px-0 py-2 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="rank-badge mr-3 font-weight-bold {{ $index == 0 ? 'bg-warning text-dark' : ($index == 1 ? 'bg-secondary text-white' : ($index == 2 ? 'bg-amber text-white' : 'bg-light text-muted')) }}" style="font-size: 0.85rem;">
                                        #{{ $index + 1 }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-dark font-weight-bold" style="font-size: 0.92rem;">{{ $autor->autor }}</h6>
                                        <small class="text-muted d-block" style="font-size: 0.82rem;">{{ $autor->correo ?? $autor->tipo_inv ?? 'Investigador UNASAM' }}</small>
                                    </div>
                                </div>
                                <span class="badge badge-primary-subtle text-primary font-weight-bold px-2 py-1" style="font-size: 0.85rem;">
                                    {{ $autor->total }} {{ $autor->total == 1 ? 'proyecto' : 'proyectos' }}
                                </span>
                            </div>
                        @empty
                            <p class="text-muted text-center py-3 mb-0" style="font-size: 0.9rem;">No se registran autores aún.</p>
                        @endforelse
                    </div>

                    <hr class="my-3">
                    <div class="callout callout-warning py-2 mb-0 border-left-warning rounded bg-light">
                        <small class="text-muted d-block" style="font-size: 0.85rem;">
                            <i class="fas fa-lightbulb text-warning mr-1"></i>
                            <strong>Control Científico:</strong> Los datos se actualizan automáticamente con cada nueva publicación aprobada.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN DE GRÁFICOS Y ANALÍTICA DE PRODUCCIÓN -->
    <!-- FILA 1: Evolución Mensual + Distribución por Estado -->
    <div class="row mb-4">
        <!-- Gráfico Evolución Temporal Mensual -->
        <div class="col-lg-8 mb-3">
            <div class="card border-0 shadow-sm rounded-lg h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h3 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1.15rem;">
                        <i class="fas fa-chart-line text-primary mr-2"></i>Evolución de Producción Científica (Últimos 12 Meses)
                    </h3>
                    <small class="text-muted" style="font-size: 0.88rem;">Ritmo mensual de registro y finalización de proyectos de investigación</small>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 280px;">
                        <canvas id="chartMes"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico por Estado de Investigación -->
        <div class="col-lg-4 mb-3">
            <div class="card border-0 shadow-sm rounded-lg h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h3 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1.15rem;">
                        <i class="fas fa-pie-chart text-success mr-2"></i>Distribución por Estado
                    </h3>
                    <small class="text-muted" style="font-size: 0.88rem;">Proporción de proyectos en curso vs concluidos</small>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div class="chart-container" style="position: relative; height: 250px; width: 100%;">
                        <canvas id="chartPorEstado"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FILA 2: Producción por Año + Top Áreas de Investigación + Tipo de Investigación -->
    <div class="row mb-4">
        <!-- Proyectos por Año -->
        <div class="col-lg-4 mb-3">
            <div class="card border-0 shadow-sm rounded-lg h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h3 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1.15rem;">
                        <i class="fas fa-calendar-check text-info mr-2"></i>Producción por Año
                    </h3>
                    <small class="text-muted" style="font-size: 0.88rem;">Histórico de publicaciones registradas por año</small>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 240px;">
                        <canvas id="chartPorAnio"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Áreas de Investigación -->
        <div class="col-lg-4 mb-3">
            <div class="card border-0 shadow-sm rounded-lg h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h3 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1.15rem;">
                        <i class="fas fa-layer-group text-warning mr-2"></i>Áreas Más Activas (Top 7)
                    </h3>
                    <small class="text-muted" style="font-size: 0.88rem;">Campos del conocimiento con mayor producción</small>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 240px;">
                        <canvas id="chartAreas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tipo de Investigación -->
        <div class="col-lg-4 mb-3">
            <div class="card border-0 shadow-sm rounded-lg h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h3 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1.15rem;">
                        <i class="fas fa-microscope text-purple mr-2"></i>Investigación por Tipo
                    </h3>
                    <small class="text-muted" style="font-size: 0.88rem;">Modalidad (Básica, Aplicada, Innovación...)</small>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div class="chart-container" style="position: relative; height: 240px; width: 100%;">
                        <canvas id="chartTipo"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('styles')
<style>
    /* Estilos personalizados del Dashboard Institucional */
    .icon-circle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .avatar-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .rank-badge {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .bg-primary-subtle { background-color: #e0f2fe; }
    .bg-warning-subtle { background-color: #fef3c7; }
    .bg-success-subtle { background-color: #d1fae5; }
    .bg-info-subtle { background-color: #e0e7ff; }
    .bg-purple-subtle { background-color: #f3e8ff; }
    .text-purple { color: #8b5cf6; }
    .bg-purple { background-color: #8b5cf6; }
    .badge-success-light { background-color: #ecfdf5; }
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
    }
    .hover-blue:hover {
        color: #2A7BCA !important;
    }
    .bg-amber { background-color: #d97706; }
    
    /* Adaptación de Badges para estados */
    .badge-warning-subtle { background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .badge-success-subtle { background-color: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-secondary-subtle { background-color: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Chart.defaults.maintainAspectRatio = false;
        Chart.defaults.font.size = 13; // 👈 Aumentado el tamaño de letra en los gráficos a 13px
        Chart.defaults.font.family = "'Montserrat', 'Segoe UI', 'Helvetica Neue', sans-serif";
        Chart.defaults.color = '#475569';
        
        Chart.defaults.plugins.legend.position = 'bottom';
        Chart.defaults.plugins.legend.labels = {
            boxWidth: 12,
            padding: 16,
            usePointStyle: true,
            pointStyle: 'circle'
        };

        // Datos desde PHP
        const porAnioLabels = @json($porAnioLabels ?? []);
        const porAnioData = (@json($porAnioData ?? [])).map(Number);
        const porEstadoLabels = @json($porEstadoLabels ?? []);
        const porEstadoData = (@json($porEstadoData ?? [])).map(Number);
        const porAreaLabels = @json($porAreaLabels ?? []);
        const porAreaData = (@json($porAreaData ?? [])).map(Number);
        const porTipoLabels = @json($porTipoLabels ?? []);
        const porTipoData = (@json($porTipoData ?? [])).map(Number);
        const mesLabels = @json($mesLabels ?? []);
        const mesData = (@json($mesData ?? [])).map(Number);

        // Paleta Institucional Armónica ESAT/UNASAM
        const palette = [
            '#2A7BCA', // Celeste ESAT (Principal)
            '#071444', // Azul Marino UNASAM
            '#10B981', // Verde Esmeralda
            '#F59E0B', // Ámbar Institucional
            '#8B5CF6', // Violeta
            '#06B6D4', // Cían
            '#EC4899', // Rosa
            '#64748B'  // Gris Pizarra
        ];

        const bg = n => Array.from({ length: n }, (_, i) => palette[i % palette.length]);

        // 1. Gráfico Evolución Temporal (Línea Suavizada con Gradiente)
        const ctxMes = document.getElementById('chartMes').getContext('2d');
        const gradientMes = ctxMes.createLinearGradient(0, 0, 0, 260);
        gradientMes.addColorStop(0, 'rgba(42, 123, 202, 0.35)');
        gradientMes.addColorStop(1, 'rgba(42, 123, 202, 0.0)');

        new Chart(ctxMes, {
            type: 'line',
            data: {
                labels: mesLabels,
                datasets: [{
                    label: 'Proyectos Registrados',
                    data: mesData,
                    tension: 0.4,
                    fill: true,
                    backgroundColor: gradientMes,
                    borderColor: '#2A7BCA',
                    borderWidth: 3,
                    pointBackgroundColor: '#071444',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: '#F1F5F9', borderDash: [4, 4] }
                    }
                }
            }
        });

        // 2. Gráfico por Estado (Anillo Elegante)
        new Chart(document.getElementById('chartPorEstado'), {
            type: 'doughnut',
            data: {
                labels: porEstadoLabels,
                datasets: [{
                    data: porEstadoData,
                    backgroundColor: bg(porEstadoData.length),
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    borderRadius: 4,
                    spacing: 2
                }]
            },
            options: {
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // 3. Gráfico Proyectos por Año (Barras Verticales)
        new Chart(document.getElementById('chartPorAnio'), {
            type: 'bar',
            data: {
                labels: porAnioLabels,
                datasets: [{
                    label: 'Proyectos',
                    data: porAnioData,
                    backgroundColor: '#2A7BCA',
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 34
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9', borderDash: [4, 4] }
                    }
                }
            }
        });

        // 4. Top Áreas (Barras Horizontales)
        new Chart(document.getElementById('chartAreas'), {
            type: 'bar',
            data: {
                labels: porAreaLabels,
                datasets: [{
                    label: 'Proyectos',
                    data: porAreaData,
                    backgroundColor: bg(porAreaData.length),
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 20
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: '#F1F5F9', borderDash: [4, 4] } },
                    y: { grid: { display: false } }
                }
            }
        });

        // 5. Investigación por Tipo (Pastel / Pie)
        new Chart(document.getElementById('chartTipo'), {
            type: 'pie',
            data: {
                labels: porTipoLabels,
                datasets: [{
                    data: porTipoData,
                    backgroundColor: bg(porTipoData.length),
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    borderRadius: 4,
                    spacing: 2
                }]
            },
            options: {
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    });
</script>
@endpush