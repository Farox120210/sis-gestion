@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Panel de publicaciones</h1>
        </div>
    </div>
    <hr>

    {{-- KPIs --}}
    <div class="row">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $totalPublicaciones ?? 0 }}</h3>
                    <p>Total de publicaciones</p>
                </div>
                <div class="icon"><i class="fas fa-book"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totalEnCurso ?? 0 }}</h3>
                    <p>En desarrollo</p>
                </div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalFinalizados ?? 0 }}</h3>
                    <p>Publicados</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalSustentados ?? 0 }}</h3>
                    <p>Sustentados</p>
                </div>
                <div class="icon"><i class="fas fa-bullhorn"></i></div>
            </div>
        </div>




    </div>

    {{-- FILA 1: PASTELES / DONAS --}}
    <div class="row dashboard g-2">
        <div class="col-xl-4 col-lg-4 mb-2">
            <div class="card card-outline card-primary card-compact h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0">Publicaciones por estado</h3>
                </div>
                <div class="card-body">
                    <div class="chart-box"><canvas id="chartPorEstado"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-4 mb-2">
            <div class="card card-outline card-primary card-compact h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0">Investigación por tipo</h3>
                </div>
                <div class="card-body">
                    <div class="chart-box"><canvas id="chartTipo"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-4 mb-2">
            <div class="card card-outline card-primary card-compact h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0">Top 5 autores</h3>
                </div>
                <div class="card-body">
                    <div class="chart-box"><canvas id="chartAutores"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILA 2: BARRAS + LÍNEA --}}
    <div class="row dashboard g-2">
        <div class="col-xl-4 col-lg-4 mb-2">
            <div class="card card-outline card-primary card-compact h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0">Proyectos por año</h3>
                </div>
                <div class="card-body">
                    <div class="chart-box"><canvas id="chartPorAnio"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-4 mb-2">
            <div class="card card-outline card-primary card-compact h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0">Top 7 áreas</h3>
                </div>
                <div class="card-body">
                    <div class="chart-box"><canvas id="chartAreas"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-4 mb-2">
            <div class="card card-outline card-primary card-compact h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0">Publicados en el año (últimos 12)</h3>
                </div>
                <div class="card-body">
                    <div class="chart-box"><canvas id="chartMes"></canvas></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* KPIs compactos */
        .small-box .inner {
            padding: .5rem .75rem
        }

        .small-box h3 {
            font-size: 1.35rem;
            margin: 0
        }

        .small-box p {
            margin: 0;
            font-size: .9rem
        }

        .small-box .icon {
            opacity: .35;
            right: 10px;
            top: 8px
        }

        /* Cards compactas */
        .card-compact .card-header {
            padding: .35rem .6rem
        }

        .card-compact .card-title {
            font-size: .95rem;
            line-height: 1.1
        }

        .card-compact .card-body {
            padding: .5rem .5rem .35rem
        }

        /* Altura de charts (uniforme para 2x3 sin scroll en 1366x768) */
        .chart-box {
            position: relative;
            height: 170px
        }

        /* Ajusta a 160 si necesitas más compacto */
        @media (min-width:1400px) {
            .chart-box {
                height: 160px
            }
        }

        @media (max-width:575.98px) {
            .chart-box {
                height: 220px
            }
        }

        /* Menos separación vertical */
        .row.dashboard {
            row-gap: .25rem
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Defaults compactos
            Chart.defaults.maintainAspectRatio = false;
            Chart.defaults.font.size = 10;
            Chart.defaults.layout = {
                padding: {
                    top: 2,
                    right: 2,
                    bottom: 0,
                    left: 2
                }
            };
            Chart.defaults.plugins.legend.position = 'bottom';
            Chart.defaults.plugins.legend.labels = {
                boxWidth: 10,
                padding: 6,
                usePointStyle: true
            };
            Chart.defaults.elements.point.radius = 2;
            Chart.defaults.elements.bar.borderWidth = 1;

            // Datos
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
            const topAutoresLabels = @json($topAutoresLabels ?? []);
            const topAutoresData = (@json($topAutoresData ?? [])).map(Number);

            // Paleta institucional (basada en tonos corporativos / AdminLTE)
            const palette = [
                '#004b8d', // azul institucional oscuro
                '#007bff', // azul primary
                '#17a2b8', // info (turquesa)
                '#28a745', // success (verde)
                '#6c757d', // gris
                '#ffc107', // amarillo/acento
                '#6610f2', // morado institucional/acento
                '#20c997', // verde agua
                '#fd7e14', // naranja/acento
                '#dc3545' // rojo (para resaltar)
            ];
            const bg = n => Array.from({
                length: n
            }, (_, i) => palette[i % palette.length] + '88');
            const bd = n => Array.from({
                length: n
            }, (_, i) => palette[i % palette.length]);

            // Por año
            new Chart(document.getElementById('chartPorAnio'), {
                type: 'bar',
                data: {
                    labels: porAnioLabels,
                    datasets: [{
                        label: 'Publicaciones',
                        data: porAnioData,
                        backgroundColor: bg(porAnioData.length),
                        borderColor: bd(porAnioData.length),
                        maxBarThickness: 18,
                        barPercentage: .7,
                        categoryPercentage: .7
                    }]
                },
                options: {
                    scales: {
                        x: {
                            ticks: {
                                maxRotation: 0,
                                autoSkip: true
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                maxTicksLimit: 5
                            },
                            grid: {
                                color: 'rgba(0,0,0,.06)'
                            }
                        }
                    }
                }
            });

            // Estado
            new Chart(document.getElementById('chartPorEstado'), {
                type: 'doughnut',
                data: {
                    labels: porEstadoLabels,
                    datasets: [{
                        data: porEstadoData,
                        backgroundColor: bg(porEstadoData.length),
                        borderColor: bd(porEstadoData.length)
                    }]
                },
                options: {
                    cutout: '55%'
                }
            });

            // Áreas (horizontal)
            new Chart(document.getElementById('chartAreas'), {
                type: 'bar',
                data: {
                    labels: porAreaLabels,
                    datasets: [{
                        label: 'Publicaciones',
                        data: porAreaData,
                        backgroundColor: bg(porAreaData.length),
                        borderColor: bd(porAreaData.length),
                        maxBarThickness: 18,
                        barPercentage: .7,
                        categoryPercentage: .7
                    }]
                },
                options: {
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                maxTicksLimit: 5
                            },
                            grid: {
                                color: 'rgba(0,0,0,.06)'
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Tipo
            new Chart(document.getElementById('chartTipo'), {
                type: 'pie',
                data: {
                    labels: porTipoLabels,
                    datasets: [{
                        data: porTipoData,
                        backgroundColor: bg(porTipoData.length),
                        borderColor: bd(porTipoData.length)
                    }]
                },
                options: {}
            });

            // Mes (línea)
            new Chart(document.getElementById('chartMes'), {
                type: 'line',
                data: {
                    labels: mesLabels,
                    datasets: [{
                        label: 'Publicaciones',
                        data: mesData,
                        tension: .25,
                        fill: true,
                        backgroundColor: 'rgba(78,121,167,.25)',
                        borderColor: '#4e79a7',
                        borderWidth: 2,
                        pointRadius: 2,
                        pointHitRadius: 6
                    }]
                },
                options: {
                    scales: {
                        x: {
                            ticks: {
                                autoSkip: true,
                                maxTicksLimit: 7
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                maxTicksLimit: 5
                            }
                        }
                    }
                }
            });

            // Autores
            new Chart(document.getElementById('chartAutores'), {
                type: 'doughnut',
                data: {
                    labels: topAutoresLabels,
                    datasets: [{
                        data: topAutoresData,
                        backgroundColor: bg(topAutoresData.length),
                        borderColor: bd(topAutoresData.length)
                    }]
                },
                options: {
                    cutout: '55%'
                }
            });
        });
    </script>
@endpush
