<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pub->titulo }} | ESAT - UNASAM</title>
    <meta name="description" content="{{ Str::limit($pub->descripcion, 160, '...') }}">

    {{-- Fuentes y Librerías Externas --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    {{-- CSS Institucional --}}
    <link rel="stylesheet" href="{{ asset('css/inicio.css') }}">
</head>

<body>

    {{-- CABECERA (Mismo menú global del repositorio) --}}
    <nav class="navbar navbar-expand-xl navbar-custom shadow-sm py-3 sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('inicio') }}">
                <img src="{{ asset('dist/img/LOGO_UNASAM-removebg-preview.png') }}" alt="Logo ESAT"
                    style="height: 50px; margin-right: 15px; object-fit: contain;">
                <h4 class="mb-0 font-weight-bold" style="color: var(--unasam-navy);">ESAT<span
                        style="color: var(--esat-blue);">-</span>UNASAM</h4>
            </a>

            <button class="navbar-toggler border-0" type="button" data-toggle="collapse" data-target="#topBarNavbar"
                aria-controls="topBarNavbar" aria-expanded="false" aria-label="Navegación">
                <i class="fas fa-bars" style="color: var(--unasam-navy); font-size: 1.5rem;"></i>
            </button>

            <div class="collapse navbar-collapse" id="topBarNavbar">
                <ul class="navbar-nav mx-auto font-weight-bold" style="font-size: 0.9rem;">
                    <li class="nav-item dropdown px-2">
                        <a class="nav-link dropdown-toggle" href="#" id="dropTipo" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-graduation-cap text-muted mr-1"></i> Tipo
                        </a>
                        <div class="dropdown-menu border-0 shadow-sm" aria-labelledby="dropTipo">
                            @foreach ($menuTipos as $t)
                                <a class="dropdown-item"
                                    href="{{ route('inicio', ['tipo' => $t->ID_TIPO]) }}">{{ $t->NOMBRE_TIPO }}</a>
                            @endforeach
                        </div>
                    </li>
                    <li class="nav-item dropdown px-2">
                        <a class="nav-link dropdown-toggle" href="#" id="dropArea" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-microscope text-muted mr-1"></i> Materia
                        </a>
                        <div class="dropdown-menu border-0 shadow-sm" aria-labelledby="dropArea">
                            @foreach ($menuAreas as $a)
                                <a class="dropdown-item"
                                    href="{{ route('inicio', ['area' => $a->ID_AREA]) }}">{{ $a->NOMBRE_AREA }}</a>
                            @endforeach
                        </div>
                    </li>
                    <li class="nav-item dropdown px-2">
                        <a class="nav-link dropdown-toggle" href="#" id="dropGrupo" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-users text-muted mr-1"></i> Grupos
                        </a>
                        <div class="dropdown-menu border-0 shadow-sm" aria-labelledby="dropGrupo">
                            @foreach ($menuGrupos as $g)
                                <a class="dropdown-item"
                                    href="{{ route('inicio', ['grupo' => $g->ID_GRUPO]) }}">{{ $g->NOMBRE_GRUPO }}</a>
                            @endforeach
                        </div>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link" href="{{ route('public.autores') }}">
                            <i class="fas fa-user-tie text-muted mr-1"></i> Autores
                        </a>
                    </li>
                    <li class="nav-item dropdown px-2">
                        <a class="nav-link dropdown-toggle" href="#" id="dropAnio" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-calendar-alt text-muted mr-1"></i> Año de publicación
                        </a>
                        <div class="dropdown-menu border-0 shadow-sm" aria-labelledby="dropAnio">
                            @foreach ($menuAnios as $an)
                                <a class="dropdown-item text-center"
                                    href="{{ route('inicio', ['anio' => $an->ANIO]) }}">{{ $an->ANIO }}</a>
                            @endforeach
                        </div>
                    </li>
                </ul>

                <ul class="navbar-nav align-items-center ml-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('inicio') }}"><i
                                class="fas fa-home mr-1"></i> Inicio</a></li>
                    <li class="nav-item ml-3">
                        <a class="btn btn-primary font-weight-bold text-white px-4" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt mr-2"></i> Ingresar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- CUERPO PRINCIPAL --}}
    <div class="container py-5">

        {{-- Migas de pan --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 0.88rem;">
                <li class="breadcrumb-item"><a href="{{ route('inicio') }}" style="color: var(--esat-blue);">Inicio</a></li>
                <li class="breadcrumb-item active text-muted" aria-current="page">Detalle de publicación</li>
            </ol>
        </nav>

        {{-- Botón volver --}}
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm mb-4" style="border-radius: 8px;">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>

        {{-- TÍTULO PRINCIPAL --}}
        <div class="mb-4">
            <h2 class="font-weight-bold mb-3" style="color: var(--unasam-navy); line-height: 1.35;">
                {{ $pub->titulo }}
            </h2>
            <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                @if ($pub->estado)
                    <span class="pub-badge pub-badge--navy">
                        <i class="fas fa-bookmark mr-1"></i> {{ $pub->estado }}
                    </span>
                @endif
                @if ($pub->tipo)
                    <span class="pub-badge pub-badge--blue">
                        <i class="fas fa-graduation-cap mr-1"></i> {{ $pub->tipo }}
                    </span>
                @endif
                @if ($pub->area)
                    <span class="pub-badge pub-badge--gold">
                        <i class="fas fa-microscope mr-1"></i> {{ $pub->area }}
                    </span>
                @endif
            </div>
        </div>

        <div class="row">

            {{-- COLUMNA IZQUIERDA: Información Resumida --}}
            <div class="col-lg-4 mb-4">

                {{-- Tarjeta de Portada y Documento PDF --}}
                <div class="pub-sidebar-card mb-4">
                    <div class="pub-sidebar-card__header">
                        <i class="fas fa-file-pdf mr-2"></i> Documento PDF
                    </div>
                    <div class="pub-sidebar-card__body text-center">
                        @php
                            $fileName = $archivoPrincipal?->PATH ? basename($archivoPrincipal->PATH) : null;
                            $tam = $archivoPrincipal && $archivoPrincipal->SIZE_BYTES ? number_format($archivoPrincipal->SIZE_BYTES / 1024, 1) . ' KB' : '—';
                            $ver = $archivoPrincipal->VERSION ?? 1;
                        @endphp

                        <div class="border mb-3 bg-light d-flex justify-content-center align-items-center" style="border-radius: 6px; min-height: 220px; overflow: hidden; background-color: #f8f9fa; position: relative;">
                            @if ($fileName)
                                <canvas id="pdf-preview" style="max-width: 100%; height: auto; display: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></canvas>
                                
                                <div id="pdf-loading" class="text-muted small font-weight-bold">
                                    <i class="fas fa-spinner fa-spin mr-2 text-primary"></i>Generando portada...
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-file-pdf fa-3x mb-2" style="opacity: 0.3;"></i><br>
                                    <small>Sin documento adjunto</small>
                                </div>
                            @endif
                        </div>
                        
                        @if ($fileName)
                            <div class="text-left mb-3">
                                <span class="badge badge-secondary mb-2">{{ $tam }}</span>
                                <p class="small text-muted mb-0 text-break" style="line-height: 1.2;">
                                    {{ $archivoPrincipal->NOMBRE_ORIGINAL ?? '—' }}
                                </p>
                            </div>
                            
                            <a href="{{ route('public.publicaciones.ver-archivo', ['id' => $pub->id_publicacion, 'file' => $fileName]) }}" 
                               target="_blank" rel="noopener" class="btn btn-outline-danger btn-block mb-2 font-weight-bold" style="border-radius: 6px;">
                                <i class="fas fa-eye mr-1"></i> Ver PDF (v{{ $ver }})
                            </a>
                            
                            <a href="{{ route('public.publicaciones.descargar-archivo', ['id' => $pub->id_publicacion, 'file' => $fileName]) }}" 
                               class="btn btn-primary btn-block font-weight-bold" style="border-radius: 6px; background-color: var(--esat-blue); border-color: var(--esat-blue);">
                                <i class="fas fa-download mr-1"></i> Descargar
                            </a>
                        @else
                            <div class="alert alert-warning p-2 small m-0 shadow-none border-0 text-center">No hay PDF principal asignado aún.</div>
                        @endif
                    </div>
                </div>

                {{-- Tarjeta de Autores --}}
                <div class="pub-sidebar-card mb-4">
                    <div class="pub-sidebar-card__header">
                        <i class="fas fa-users mr-2"></i> Autores
                    </div>
                    <div class="pub-sidebar-card__body">
                        {{-- Autor principal --}}
                        @if ($pub->autor_principal)
                            <div class="d-flex align-items-start mb-3">
                                <div class="pub-avatar mr-3">
                                    {{ substr($pub->autor_principal, 0, 1) }}
                                </div>
                                <div>
                                    <p class="mb-0 font-weight-bold" style="color: var(--unasam-navy); font-size: 0.95rem;">
                                        {{ $pub->autor_principal }}
                                    </p>
                                    <small class="text-muted">Autor principal</small>
                                </div>
                            </div>
                        @endif

                        {{-- Co-autores --}}
                        @foreach ($autores as $a)
                            @continue($a->nombre === $pub->autor_principal)
                            <div class="d-flex align-items-start mb-3">
                                <div class="pub-avatar pub-avatar--small mr-3">
                                    {{ substr($a->nombre, 0, 1) }}
                                </div>
                                <div>
                                    <p class="mb-0" style="color: var(--text-main); font-size: 0.9rem;">
                                        {{ $a->nombre }}
                                    </p>
                                    @if ($a->ROL_EN_PROYECTO)
                                        <small class="text-muted">{{ $a->ROL_EN_PROYECTO }}</small>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        @if ($autores->isEmpty() && !$pub->autor_principal)
                            <p class="text-muted small mb-0"><i class="fas fa-info-circle mr-1"></i> Sin autores registrados.</p>
                        @endif
                    </div>
                </div>

                {{-- Tarjeta de Datos del Proyecto --}}
                <div class="pub-sidebar-card">
                    <div class="pub-sidebar-card__header">
                        <i class="fas fa-info-circle mr-2"></i> Datos del Proyecto
                    </div>
                    <div class="pub-sidebar-card__body">
                        <div class="pub-detail-row">
                            <span class="pub-detail-row__label"><i class="fas fa-calendar-alt mr-2"></i>Año</span>
                            <span class="pub-detail-row__value">{{ $pub->anio ?: '—' }}</span>
                        </div>
                        <div class="pub-detail-row">
                            <span class="pub-detail-row__label"><i class="fas fa-play mr-2"></i>Inicio</span>
                            <span class="pub-detail-row__value">{{ $pub->fecha_inicio ?: '—' }}</span>
                        </div>
                        <div class="pub-detail-row">
                            <span class="pub-detail-row__label"><i class="fas fa-stop mr-2"></i>Fin</span>
                            <span class="pub-detail-row__value">{{ $pub->fecha_fin ?: '—' }}</span>
                        </div>
                        <div class="pub-detail-row">
                            <span class="pub-detail-row__label"><i class="fas fa-layer-group mr-2"></i>Grupo</span>
                            <span class="pub-detail-row__value">{{ $pub->grupo ?: '—' }}</span>
                        </div>
                        @if (!is_null($pub->presupuesto))
                        <div class="pub-detail-row">
                            <span class="pub-detail-row__label"><i class="fas fa-coins mr-2"></i>Presupuesto</span>
                            <span class="pub-detail-row__value">S/ {{ number_format($pub->presupuesto, 2) }}</span>
                        </div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- COLUMNA DERECHA: Resumen / Descripción --}}
            <div class="col-lg-8">
                <div class="pub-content-card">
                    <div class="pub-content-card__header">
                        <i class="fas fa-file-alt mr-2"></i> Resumen / Descripción
                    </div>
                    <div class="pub-content-card__body">
                        @if ($pub->descripcion)
                            <p class="text-justify" style="line-height: 1.85; font-size: 0.95rem; color: var(--text-main);">
                                {{ $pub->descripcion }}
                            </p>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-file-alt fa-3x mb-3" style="color: var(--border-color);"></i>
                                <p class="text-muted">Este proyecto aún no cuenta con una descripción detallada.</p>
                            </div>
                        @endif

                        {{-- Keywords --}}
                        @if(!empty($pub->keywords))
                            <div class="mt-4 pt-3 border-top">
                                <h6 class="font-weight-bold mb-2 text-uppercase text-muted" style="font-size: 0.8rem; letter-spacing: 0.5px;">Palabras Clave / Keywords</h6>
                                <div class="d-flex flex-wrap" style="gap: 6px;">
                                    @foreach(explode(',', $pub->keywords) as $keyword)
                                        <span class="badge badge-light text-dark px-3 py-2 border" style="font-size: 0.85rem; border-radius: 20px; background-color: rgba(7, 20, 68, 0.05);">
                                            <i class="fas fa-tag mr-1 text-muted"></i>{{ trim($keyword) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- FOOTER ACADÉMICO --}}
    <footer>
        <div class="footer-top text-center">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-2 mb-md-0 text-md-left">
                        <strong>CORREO INSTITUCIONAL:</strong> repositorio.esat@unasam.edu.pe
                    </div>
                    <div class="col-md-6 text-md-right">
                        ESAT-UNASAM &copy; {{ date('Y') }}. Todos los derechos reservados.
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-main">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <img src="{{ asset('dist/img/ESAT_LogoIdentidad_Sf_Blanco.png') }}" alt="UNASAM"
                            style="height: 60px; filter: brightness(0) invert(1); margin-bottom: 20px;">
                        <h5 class="mt-2">CONTÁCTANOS</h5>
                        <p><i class="fas fa-map-marker-alt"></i> Av. Centenario 200, Independencia<br>Huaraz - Ancash,
                            Perú</p>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <h5>CONOCE LA UNASAM</h5>
                        <ul>
                            <li><a href="#"><i class="fas fa-angle-double-right"></i> Portal Principal</a></li>
                            <li><a href="#"><i class="fas fa-angle-double-right"></i> Vicerrectorado de
                                    Investigación</a></li>
                            <li><a href="#"><i class="fas fa-angle-double-right"></i> Directorio
                                    Institucional</a></li>
                            <li><a href="#"><i class="fas fa-angle-double-right"></i> Portal de
                                    Transparencia</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <h5>SERVICIOS AL ESTUDIANTE</h5>
                        <ul>
                            <li><a href="#"><i class="fas fa-angle-double-right"></i> Biblioteca Central</a>
                            </li>
                            <li><a href="#"><i class="fas fa-angle-double-right"></i> Bienestar
                                    Universitario</a></li>
                            <li><a href="#"><i class="fas fa-angle-double-right"></i> Publicaciones
                                    Académicas</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    @if (!empty($fileName))
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
        
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Configuramos el worker de PDF.js
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

                // Usamos la ruta pública segura
                const url = "{{ route('public.publicaciones.ver-archivo', ['id' => $pub->id_publicacion, 'file' => $fileName]) }}";

                const loadingTask = pdfjsLib.getDocument(url);
                
                loadingTask.promise.then(function(pdf) {
                    return pdf.getPage(1);
                }).then(function(page) {
                    const scale = 1.2; 
                    const viewport = page.getViewport({scale: scale});

                    const canvas = document.getElementById('pdf-preview');
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    
                    page.render(renderContext).promise.then(function() {
                        document.getElementById('pdf-loading').style.display = 'none';
                        canvas.style.display = 'block';
                    });
                }).catch(function(error) {
                    console.error('Error al cargar la portada del PDF: ', error);
                    document.getElementById('pdf-loading').innerHTML = '<i class="fas fa-exclamation-triangle text-danger mr-1"></i> No se pudo cargar la vista previa';
                });
            });
        </script>
    @endif
</body>

</html>
