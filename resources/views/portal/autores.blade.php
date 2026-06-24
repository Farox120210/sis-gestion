<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Investigadores | ESAT - UNASAM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/inicio.css') }}">
</head>
<body>

    {{-- CABECERA (Mismo menú global) --}}
    <nav class="navbar navbar-expand-xl navbar-custom shadow-sm py-3 sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('inicio') }}">
                <img src="{{ asset('dist/img/LOGO_UNASAM-removebg-preview.png') }}" alt="Logo ESAT" style="height: 50px; margin-right: 15px; object-fit: contain;">
                <h4 class="mb-0 font-weight-bold" style="color: var(--unasam-navy);">ESAT<span style="color: var(--esat-blue);">-</span>UNASAM</h4>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-toggle="collapse" data-target="#topBarNavbar" aria-controls="topBarNavbar" aria-expanded="false" aria-label="Navegación">
                <i class="fas fa-bars" style="color: var(--unasam-navy); font-size: 1.5rem;"></i>
            </button>

            <div class="collapse navbar-collapse" id="topBarNavbar">
                <ul class="navbar-nav mx-auto font-weight-bold" style="font-size: 0.9rem;">
                    <li class="nav-item dropdown px-2">
                        <a class="nav-link dropdown-toggle" href="#" id="dropTipo" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Tipo</a>
                        <div class="dropdown-menu border-0 shadow-sm" aria-labelledby="dropTipo">
                            @foreach($menuTipos as $t) <a class="dropdown-item" href="{{ route('inicio', ['tipo' => $t->ID_TIPO]) }}">{{ $t->NOMBRE_TIPO }}</a> @endforeach
                        </div>
                    </li>
                    <li class="nav-item dropdown px-2">
                        <a class="nav-link dropdown-toggle" href="#" id="dropArea" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Materia</a>
                        <div class="dropdown-menu border-0 shadow-sm" aria-labelledby="dropArea">
                            @foreach($menuAreas as $a) <a class="dropdown-item" href="{{ route('inicio', ['area' => $a->ID_AREA]) }}">{{ $a->NOMBRE_AREA }}</a> @endforeach
                        </div>
                    </li>
                    <li class="nav-item dropdown px-2">
                        <a class="nav-link dropdown-toggle" href="#" id="dropGrupo" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Grupos</a>
                        <div class="dropdown-menu border-0 shadow-sm" aria-labelledby="dropGrupo">
                            @foreach($menuGrupos as $g) <a class="dropdown-item" href="{{ route('inicio', ['grupo' => $g->ID_GRUPO]) }}">{{ $g->NOMBRE_GRUPO }}</a> @endforeach
                        </div>
                    </li>
                    <li class="nav-item px-2"><a class="nav-link active text-primary" href="{{ route('public.autores') }}">Autores</a></li>
                    <li class="nav-item dropdown px-2">
                        <a class="nav-link dropdown-toggle" href="#" id="dropAnio" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Fechas</a>
                        <div class="dropdown-menu border-0 shadow-sm" aria-labelledby="dropAnio">
                            @foreach($menuAnios as $an) <a class="dropdown-item text-center" href="{{ route('inicio', ['anio' => $an->ANIO]) }}">{{ $an->ANIO }}</a> @endforeach
                        </div>
                    </li>
                </ul>

                <ul class="navbar-nav align-items-center ml-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('inicio') }}"><i class="fas fa-home mr-1"></i> Inicio</a></li>
                    <li class="nav-item ml-3"><a class="btn btn-primary font-weight-bold text-white px-4" href="{{ route('login') }}"><i class="fas fa-sign-in-alt mr-2"></i> Ingresar</a></li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- INTERFAZ PRINCIPAL DE AUTORES --}}
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="font-weight-bold" style="color: var(--unasam-navy);">Directorio de Investigadores</h2>
            <p class="text-muted">Explora la producción científica de la comunidad académica ESAT-UNASAM</p>
        </div>

        {{-- BARRA DE BÚSQUEDA DE AUTORES --}}
        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <form action="{{ route('public.autores') }}" method="GET">
                    <div class="input-group input-group-lg shadow-sm" style="border-radius: 30px; overflow: hidden; border: 1px solid #e2e8f0;">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-0 pl-4"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="search_autor" id="txtBuscarAutor" class="form-control border-0" placeholder="Escribe el nombre o apellido del investigador..." value="{{ request('search_autor') }}" style="font-size: 1rem; height: 55px;">
                        @if(request('search_autor'))
                            <div class="input-group-append">
                                <a href="{{ route('public.autores') }}" class="btn bg-white border-0 text-muted d-flex align-items-center justify-content-center pr-3"><i class="fas fa-times"></i></a>
                            </div>
                        @endif
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary px-4 font-weight-bold" style="font-size: 0.95rem;">Buscar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- LISTA DE TARJETAS DE AUTORES --}}
        <div class="row" id="contenedorAutores">
            @forelse($todosLosAutores as $autor)
                <div class="col-xl-4 col-md-6 mb-4 tarjeta-autor-item" data-nombre="{{ strtolower($autor->apellidos . ' ' . $autor->nombres) }}">
                    <div class="card h-100 shadow-sm border-0 py-3" style="border-radius: 14px; transition: transform 0.2s;">
                        <div class="card-body d-flex align-items-center">
                            {{-- Avatar circular dinámico con inicial --}}
                            <div class="d-flex align-items-center justify-content-center rounded-circle text-white font-weight-bold mr-3" style="width: 55px; height: 55px; background-color: var(--esat-blue); font-size: 1.3rem; min-width: 55px;">
                                {{ substr($autor->apellidos, 0, 1) }}
                            </div>
                            <div>
                                <h5 class="font-weight-bold mb-1 text-capitalize" style="color: var(--unasam-navy); font-size: 1.05rem;">
                                    {{ strtolower($autor->apellidos . ', ' . $autor->nombres) }}
                                </h5>
                                <p class="text-muted mb-2" style="font-size: 0.85rem;"><i class="fas fa-file-alt mr-1"></i> {{ $autor->total_proyectos }} Publicaciones registradas</p>
                                <a href="{{ route('inicio', ['autor' => $autor->id]) }}" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius: 6px; font-size: 0.78rem;">
                                    Ver Investigaciones <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-user-slash fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No se encontraron investigadores con ese criterio de búsqueda.</h4>
                </div>
            @endforelse
            {{-- BOTONES DEL PAGINADOR ENLAZADOS A BOOTSTRAP 4 --}}
        <div class="d-flex justify-content-center mt-5">
            {{ $todosLosAutores->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Script Opcional: Filtro instantáneo al escribir sin recargar --}}
    <script>
        document.getElementById('txtBuscarAutor').addEventListener('input', function(e) {
            let filtro = e.target.value.toLowerCase().trim();
            let tarjetas = document.querySelectorAll('.tarjeta-autor-item');
            
            tarjetas.forEach(function(tarjeta) {
                let nombreAutor = tarjeta.getAttribute('data-nombre');
                if (nombreAutor.includes(filtro)) {
                    tarjeta.style.display = 'block';
                } else {
                    tarjeta.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>