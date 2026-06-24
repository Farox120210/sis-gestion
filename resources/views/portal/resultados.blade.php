<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resultados de Exploración | ESAT - UNASAM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/inicio.css') }}">
</head>

<body>

    {{-- CABECERA (La misma del inicio) --}}
    {{-- BARRA SUPERIOR DE NAVEGACIÓN Y FILTROS INTEGRADOS --}}
    <nav class="navbar navbar-expand-xl navbar-custom shadow-sm py-2 sticky-top">
        <div class="container-fluid px-4">
            {{-- Logo y Nombre de la Institución --}}
            <a class="navbar-brand d-flex align-items-center" href="{{ route('inicio') }}">
                <img src="{{ asset('dist/img/LOGO_UNASAM-removebg-preview.png') }}" alt="Logo ESAT"
                    style="height: 48px; margin-right: 12px; object-fit: contain;">
                <h5 class="mb-0 font-weight-bold" style="color: var(--unasam-navy); letter-spacing: -0.5px;">ESAT<span
                        style="color: var(--esat-blue);">-</span>UNASAM</h5>
            </a>

            {{-- Botón de colapso para pantallas móviles --}}
            <button class="navbar-toggler border-0" type="button" data-toggle="collapse" data-target="#topBarNavbar"
                aria-controls="topBarNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars" style="color: var(--unasam-navy); font-size: 1.3rem;"></i>
            </button>

            {{-- Menús de Categorías Desplegables --}}
            <div class="collapse navbar-collapse" id="topBarNavbar">
                <ul class="navbar-nav mx-auto text-uppercase" style="font-size: 0.82rem; letter-spacing: 0.5px;">

                    {{-- 1. DROPDOWN: TIPO DE INVESTIGACIÓN --}}
                    <li class="nav-item dropdown px-2">
                        <a class="nav-link dropdown-toggle" href="#" id="dropTipo" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-graduation-cap text-muted mr-1"></i> Tipo
                        </a>
                        <div class="dropdown-menu border-0 shadow-sm animate-fade-in" aria-labelledby="dropTipo">
                            @foreach ($menuTipos as $t)
                                <a class="dropdown-item"
                                    href="{{ route('inicio', ['tipo' => $t->ID_TIPO]) }}">{{ $t->NOMBRE_TIPO }}</a>
                            @endforeach
                        </div>
                    </li>

                    {{-- 2. DROPDOWN: MATERIA / ÁREA DE INVESTIGACIÓN --}}
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

                    {{-- 3. DROPDOWN: GRUPOS DE INVESTIGACIÓN --}}
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

                    {{-- 4. DROPDOWN: AUTORES --}}
                    {{-- Enlace Directo a Autores en tu Navbar --}}
                    <li class="nav-item px-2">
                        <a class="nav-link" href="{{ route('public.autores') }}">
                            <i class="fas fa-user-tie text-muted mr-1"></i> Autores
                        </a>
                    </li>

                    {{-- 5. DROPDOWN: FECHAS / AÑOS --}}
                    <li class="nav-item dropdown px-2">
                        <a class="nav-link dropdown-toggle" href="#" id="dropAnio" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-calendar-alt text-muted mr-1"></i> Cronología
                        </a>
                        <div class="dropdown-menu border-0 shadow-sm" aria-labelledby="dropAnio">
                            @foreach ($menuAnios as $an)
                                <a class="dropdown-item text-center font-weight-bold"
                                    href="{{ route('inicio', ['anio' => $an->ANIO]) }}">{{ $an->ANIO }}</a>
                            @endforeach
                        </div>
                    </li>

                </ul>

                {{-- Botón de Ingreso al Sistema Privado --}}
                <div class="form-inline mt-2 mt-xl-0">
                    <a class="btn btn-primary font-weight-bold text-white px-4 py-2" href="{{ route('login') }}"
                        style="border-radius: 6px; font-size: 0.85rem;">
                        <i class="fas fa-sign-in-alt mr-2"></i> Acceso Interno
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="font-weight-bold" style="color: var(--unasam-navy);">
                Datos registrados ({{ $publicaciones->count() }})
            </h3>
            <a href="{{ route('inicio') }}" class="btn btn-outline-secondary btn-sm"><i
                    class="fas fa-times mr-1"></i> Limpiar filtros</a>
        </div>

        {{-- LISTA DE PUBLICACIONES (Diseño de la Captura) --}}
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body p-0">
                @forelse($publicaciones as $pub)
                    <a href="{{ route('public.publicacion.show', $pub->id) }}" class="text-decoration-none d-block result-row" style="border-bottom: 1px solid #f1f5f9; transition: background-color 0.2s ease;">
                    <div class="row no-gutters align-items-center p-4">

                        {{-- Ícono de Documento --}}
                        <div class="col-md-2 col-12 text-center mb-3 mb-md-0">
                            <div class="d-inline-flex align-items-center justify-content-center bg-light"
                                style="width: 100px; height: 120px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <i class="fas fa-file-pdf fa-3x text-secondary"></i>
                            </div>
                        </div>

                        {{-- Detalles de la Publicación --}}
                        <div class="col-md-10 col-12 pl-md-4">
                            <div class="mb-2">
                                <span class="badge"
                                    style="background-color: var(--unasam-navy); color: white; padding: 5px 10px; font-weight: 600;">
                                    {{ $pub->tipo ?? 'Sin tipo' }}
                                </span>
                                <span class="badge border ml-1"
                                    style="color: #64748b; background-color: white; padding: 5px 10px; font-weight: 600;">
                                    {{ $pub->area ?? 'General' }}
                                </span>
                            </div>

                            <h4 class="font-weight-bold mb-2 text-uppercase" style="color: var(--esat-blue);">
                                {{ $pub->titulo }}
                            </h4>

                            <p class="text-muted mb-1" style="font-size: 0.95rem;">
                                <i class="fas fa-user-edit mr-2"></i>
                                {{ $pub->autor_nombres }} {{ $pub->autor_apellidos }}
                            </p>

                            <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                <i class="fas fa-university mr-2"></i>
                                {{ $pub->grupo ?? 'ESAT-UNASAM' }}
                            </p>

                            @if (!empty($pub->keywords))
                                <div class="mt-2 d-flex flex-wrap" style="gap: 4px;">
                                    @foreach(explode(',', $pub->keywords) as $keyword)
                                        <span class="badge badge-light border text-dark px-2 py-1" style="font-size: 0.72rem; font-weight: 500; border-radius: 12px; background-color: rgba(7, 20, 68, 0.03);">
                                            <i class="fas fa-tag mr-1 text-muted" style="font-size: 0.65rem;"></i>{{ trim($keyword) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    </a>
                @empty
                    <div class="p-5 text-center">
                        <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No se encontraron registros para esta categoría.</h4>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
