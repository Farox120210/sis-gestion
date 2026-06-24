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

<!--@push('styles')
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
    </style>-->
@endpush
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Defaults compactos y limpios
            Chart.defaults.maintainAspectRatio = false;
            Chart.defaults.font.size = 11; // Un poquito más grande para mejor lectura
            Chart.defaults.font.family = "'Inter', 'Segoe UI', 'Helvetica Neue', sans-serif"; // Fuente más moderna
            Chart.defaults.color = '#64748B'; // Texto gris azulado en lugar de negro
            Chart.defaults.layout = { padding: { top: 10, right: 10, bottom: 0, left: 0 } };
            
            // Leyendas modernas con círculos en lugar de cuadrados
            Chart.defaults.plugins.legend.position = 'bottom';
            Chart.defaults.plugins.legend.labels = {
                boxWidth: 12,
                padding: 15,
                usePointStyle: true,
                pointStyle: 'circle'
            };

            // Datos (se mantienen igual, recibidos desde PHP)
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

            // 👇 1. NUEVA PALETA INSTITUCIONAL ESAT/UNASAM
            const palette = [
                '#2A7BCA', // Celeste ESAT (Principal)
                '#071444', // Azul Marino (Secundario)
                '#10B981', // Verde esmeralda suave
                '#F59E0B', // Ambar
                '#8B5CF6', // Púrpura suave
                '#14B8A6', // Turquesa
                '#F43F5E', // Rosa oscuro
                '#64748B', // Gris Pizarra
                '#3B82F6', // Azul claro
                '#0ea5e9'  // Sky blue
            ];
            
            // Generador de colores sólidos para gráficos de categorías
            const bg = n => Array.from({ length: n }, (_, i) => palette[i % palette.length]);

            // 👇 2. Gráfico Por año (Barras Verticales - Todo en Celeste)
            new Chart(document.getElementById('chartPorAnio'), {
                type: 'bar',
                data: {
                    labels: porAnioLabels,
                    datasets: [{
                        label: 'Publicaciones',
                        data: porAnioData,
                        backgroundColor: '#2A7BCA', // Color unificado para tiempo
                        borderRadius: 6,            // ✨ BARRAS REDONDEADAS
                        borderSkipped: false,       // ✨ Redondea también la base
                        maxBarThickness: 30         // Barras un poco más gruesas
                    }]
                },
                options: {
                    plugins: { legend: { display: false } }, // Ocultar leyenda obvia
                    scales: {
                        x: { grid: { display: false } },     // ✨ Oculta las líneas de fondo verticales
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#E2E8F0',            // ✨ Líneas horizontales súper suaves
                                borderDash: [4, 4]           // ✨ Líneas punteadas elegantes
                            }
                        }
                    }
                }
            });

            // 👇 3. Gráfico de Estado (Anillo / Doughnut)
            new Chart(document.getElementById('chartPorEstado'), {
                type: 'doughnut',
                data: {
                    labels: porEstadoLabels,
                    datasets: [{
                        data: porEstadoData,
                        backgroundColor: bg(porEstadoData.length),
                        borderColor: '#ffffff', // ✨ Borde blanco entre porciones
                        borderWidth: 3,         // ✨ Grosor del borde blanco
                        borderRadius: 4,        // ✨ Porciones con esquinas suaves
                        spacing: 2              // ✨ Pequeño espacio físico entre rebanadas
                    }]
                },
                options: {
                    cutout: '70%' // ✨ Hace el anillo más delgado y elegante
                }
            });

            // 👇 4. Gráfico de Áreas (Barras Horizontales)
            new Chart(document.getElementById('chartAreas'), {
                type: 'bar',
                data: {
                    labels: porAreaLabels,
                    datasets: [{
                        label: 'Publicaciones',
                        data: porAreaData,
                        backgroundColor: bg(porAreaData.length), // Varios colores porque son categorías
                        borderRadius: 6, // ✨ BARRAS REDONDEADAS
                        borderSkipped: false,
                        maxBarThickness: 20
                    }]
                },
                options: {
                    indexAxis: 'y', // Lo hace horizontal
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: '#E2E8F0', borderDash: [4, 4] }
                        },
                        y: { grid: { display: false } }
                    }
                }
            });

            // 👇 5. Gráfico de Tipo (Pastel / Pie)
            new Chart(document.getElementById('chartTipo'), {
                type: 'pie', // El pie también soporta bordes blancos y redondeados
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
                options: {}
            });

            // 👇 6. Gráfico de Mes (Línea suave temporal)
            new Chart(document.getElementById('chartMes'), {
                type: 'line',
                data: {
                    labels: mesLabels,
                    datasets: [{
                        label: 'Publicaciones',
                        data: mesData,
                        tension: 0.4, // ✨ Suaviza la línea (hace curvas en lugar de picos rectos)
                        fill: true,
                        backgroundColor: 'rgba(42, 123, 202, 0.15)', // Celeste semitransparente debajo
                        borderColor: '#2A7BCA', // Línea celeste
                        borderWidth: 3,
                        pointBackgroundColor: '#071444', // ✨ Puntos Azul Marino para resaltar
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#E2E8F0', borderDash: [4, 4] }
                        }
                    }
                }
            });

            // 👇 7. Gráfico Top Autores (Anillo / Doughnut)
            new Chart(document.getElementById('chartAutores'), {
                type: 'doughnut',
                data: {
                    labels: topAutoresLabels,
                    datasets: [{
                        data: topAutoresData,
                        backgroundColor: bg(topAutoresData.length),
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        borderRadius: 4,
                        spacing: 2
                    }]
                },
                options: {
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'right', // Se ve mejor a la derecha si los nombres son largos
                        }
                    }
                }
            });
        });
    </script>
@endpush