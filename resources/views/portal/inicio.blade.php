<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Repositorio Institucional | ESAT - UNASAM</title>

    {{-- Fuentes y Librerías Externas --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    {{-- NUESTRO ARCHIVO CSS EXTERNO --}}
    <link rel="stylesheet" href="{{ asset('css/inicio.css') }}">
</head>

<body>

    {{-- 1. CABECERA CON FILTROS DESPLEGABLES --}}
    <nav class="navbar navbar-expand-xl navbar-custom shadow-sm py-3 sticky-top">
        <div class="container-fluid px-4">
            {{-- Logo --}}
            <a class="navbar-brand d-flex align-items-center" href="{{ route('inicio') }}">
                <img src="{{ asset('dist/img/LOGO_UNASAM-removebg-preview.png') }}" alt="Logo ESAT"
                    style="height: 50px; margin-right: 15px; object-fit: contain;">
                <h4 class="mb-0 font-weight-bold" style="color: var(--unasam-navy);">ESAT<span
                        style="color: var(--esat-blue);">-</span>UNASAM</h4>
            </a>

            {{-- Botón menú móvil --}}
            <button class="navbar-toggler border-0" type="button" data-toggle="collapse" data-target="#topBarNavbar"
                aria-controls="topBarNavbar" aria-expanded="false" aria-label="Navegación">
                <i class="fas fa-bars" style="color: var(--unasam-navy); font-size: 1.5rem;"></i>
            </button>

            <div class="collapse navbar-collapse" id="topBarNavbar">

                {{-- ETIQUETAS DESPLEGABLES (CENTRADAS) --}}
                <ul class="navbar-nav mx-auto font-weight-bold" style="font-size: 0.9rem;">

                    {{-- Dropdown: Tipo --}}
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

                    {{-- Dropdown: Materia --}}
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

                    {{-- Dropdown: Grupos --}}
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

                    {{-- Dropdown: Autores (Con scroll si son muchos) --}}
                    {{-- Enlace Directo a Autores en tu Navbar --}}
                    <li class="nav-item px-2">
                        <a class="nav-link" href="{{ route('public.autores') }}">
                            <i class="fas fa-user-tie text-muted mr-1"></i> Autores
                        </a>
                    </li>

                    {{-- Dropdown: Cronología --}}
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

                {{-- BOTONES ORIGINALES (ALINEADOS A LA DERECHA) --}}
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

    {{-- 2. HERO BANNER DINÁMICO CON EFECTO DE DIFUMINADO (CROSSFADE) --}}
    <div id="heroBannerCarousel" class="carousel slide carousel-fade hero-carousel" data-ride="carousel" data-interval="5000" data-pause="false">
        <div class="carousel-inner">
            
            {{-- IMAGEN 1 (Activa por defecto) --}}
            <div class="carousel-item active" style="background-image: linear-gradient(rgba(7, 20, 68, 0.8), rgba(7, 20, 68, 0.8)), url('{{ asset('dist/img/fondo-unasam2.jpg') }}');">
                <div class="container text-center">
                    <img src="{{ asset('dist/img/ESAT_LogoIdentidad_Sf_Blanco.png') }}" alt="Escudo UNASAM" class="img-fluid mb-4 hero-logo">
                    <h1 class="font-weight-bold">Repositorio Institucional</h1>
                    <p class="lead">Centro de investigación en ciencias de la tierra ,ambiente y tecnología - UNASAM</p>
                </div>
            </div>

            {{-- IMAGEN 2  --}}
            <div class="carousel-item" style="background-image: linear-gradient(rgba(7, 20, 68, 0.8), rgba(7, 20, 68, 0.8)), url('{{ asset('dist/img/fondo-unasam5.jpg') }}');">
                <div class="container text-center">
                    <img src="{{ asset('dist/img/ESAT_LogoIdentidad_Sf_Blanco.png') }}" alt="Escudo UNASAM" class="img-fluid mb-4 hero-logo">
                    <h1 class="font-weight-bold">Investigación de Vanguardia</h1>
                    <p class="lead">Impulsando el desarrollo sostenible y científico de nuestra región</p>
                </div>
            </div>

            {{-- IMAGEN 3  --}}
            <div class="carousel-item" style="background-image: linear-gradient(rgba(7, 20, 68, 0.8), rgba(7, 20, 68, 0.8)), url('{{ asset('dist/img/fondo-unasam3.jpg') }}');">
                <div class="container text-center">
                    <img src="{{ asset('dist/img/ESAT_LogoIdentidad_Sf_Blanco.png') }}" alt="Escudo UNASAM" class="img-fluid mb-4 hero-logo">
                    <h1 class="font-weight-bold">Acceso Abierto al Conocimiento</h1>
                    <p class="lead">Tesis, artículos científicos y proyectos al alcance de la comunidad</p>
                </div>
            </div>
            
            {{-- IMAGEN 4 (Cambia el nombre de la foto a tu cuarta imagen de prueba) --}}
            <div class="carousel-item" style="background-image: linear-gradient(rgba(7, 20, 68, 0.8), rgba(7, 20, 68, 0.8)), url('{{ asset('dist/img/fondo-unasam4.jpg') }}');">
                <div class="container text-center">
                    <img src="{{ asset('dist/img/ESAT_LogoIdentidad_Sf_Blanco.png') }}" alt="Escudo UNASAM" class="img-fluid mb-4 hero-logo">
                    <h1 class="font-weight-bold">Innovación Tecnológica</h1>
                    <p class="lead">Desarrollando soluciones sostenibles para los desafíos ambientales</p>
                </div>
            </div>

            {{-- IMAGEN 5 (Cambia el nombre de la foto a tu quinta imagen de prueba) --}}
            <div class="carousel-item" style="background-image: linear-gradient(rgba(7, 20, 68, 0.8), rgba(7, 20, 68, 0.8)), url('{{ asset('dist/img/fondo-unasam1.jpg') }}');">
                <div class="container text-center">
                    <img src="{{ asset('dist/img/ESAT_LogoIdentidad_Sf_Blanco.png') }}" alt="Escudo UNASAM" class="img-fluid mb-4 hero-logo">
                    <h1 class="font-weight-bold">Sostenibilidad Ambiental</h1>
                    <p class="lead">Promoviendo prácticas responsables para la conservación del medio ambiente</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. CUERPO PRINCIPAL --}}
    <div class="container mb-5">

        {{-- Buscador Flotante --}}
        <div class="search-box mb-5">
            <form action="{{ route('inicio') }}" method="GET" class="row align-items-center">
                <div class="col-md-9 mb-3 mb-md-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i
                                    class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="q" class="form-control form-control-lg border-left-0"
                            placeholder="Buscar investigaciones por título, autor o palabras clave..."
                            value="{{ request('q') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-block btn-lg font-weight-bold">Buscar</button>
                </div>
            </form>
        </div>



        <div id="repoCarousel" class="carousel slide card-custom p-4 mb-5 shadow-sm" data-ride="carousel">
            <div class="carousel-inner">
                @forelse($recientes as $index => $publicacion)
                    <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                        <a href="{{ route('public.publicacion.show', $publicacion->id) }}" class="text-decoration-none d-block">
                            <div class="row align-items-center p-3">
                                <div class="col-md-2 text-center mb-3 mb-md-0">
                                    <div class="recent-pub-icon-wrapper">
                                        <i class="fas fa-file-alt fa-2x"></i>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <span
                                        class="badge badge-primary mb-2">{{ $publicacion->estado ?? 'Investigación' }}</span>
                                    <h4 class="font-weight-bold mb-2" style="color: var(--unasam-navy);">
                                        {{ $publicacion->titulo }}</h4>
                                    <p class="text-muted mb-2">{{ Str::limit($publicacion->resumen, 200, '...') }}</p>
                                    
                                    @if (!empty($publicacion->keywords))
                                        <div class="mb-3 d-flex flex-wrap" style="gap: 4px;">
                                            @foreach(explode(',', $publicacion->keywords) as $keyword)
                                                <span class="badge badge-light border text-dark px-2 py-1" style="font-size: 0.72rem; font-weight: 500; border-radius: 12px; background-color: rgba(7, 20, 68, 0.03);">
                                                    <i class="fas fa-tag mr-1 text-muted" style="font-size: 0.65rem;"></i>{{ trim($keyword) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <span class="pub-ver-detalle">
                                        Ver detalle <i class="fas fa-arrow-right ml-1"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="carousel-item active text-center p-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No se encontraron publicaciones recientes o coincidencias.</h5>
                    </div>
                @endforelse
            </div>

            @if ($recientes->count() > 1)
                <a class="carousel-control-prev" href="#repoCarousel" role="button" data-slide="prev"
                    style="width: 5%; left: -20px;">
                    <span class="carousel-control-prev-icon shadow"></span>
                </a>
                <a class="carousel-control-next" href="#repoCarousel" role="button" data-slide="next"
                    style="width: 5%; right: -20px;">
                    <span class="carousel-control-next-icon shadow"></span>
                </a>
            @endif
        </div>

        {{-- Tarjetas KPI Inferiores --}}
        {{-- Tarjetas KPI Inferiores (Filtradas para contenido apto para el público) --}}
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="kpi-card bg-blue-1">
                    <h3>{{ number_format($stats['total'] ?? 0) }}</h3>
                    <p>Total de publicaciones</p>
                    <i class="fas fa-book icon"></i>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="kpi-card bg-green">
                    <h3>{{ number_format($stats['publicados'] ?? 0) }}</h3>
                    <p>Artículos Publicados</p>
                    <i class="fas fa-check-circle icon"></i>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="kpi-card bg-blue-2">
                    <h3>{{ number_format($stats['sustentados'] ?? 0) }}</h3>
                    <p>Tesis Sustentadas</p>
                    <i class="fas fa-bullhorn icon"></i>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="kpi-card bg-yellow">
                    <h3>{{ number_format($stats['grupos_qty'] ?? 0) }}</h3>
                    <p>Grupos de investigacion</p>
                    <i class="fas fa-users icon"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. FOOTER ACADÉMICO --}}
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
</body>

</html>
