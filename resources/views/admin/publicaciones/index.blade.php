@extends('layouts.admin')

@section('content')
    <div class="container-fluid py-4">

        {{-- ==========================================
         ENCABEZADO
         ========================================== --}}
        <div class="row mb-4">
            <div class="col-12 d-flex flex-wrap justify-content-between align-items-center">
                <h2 class="mb-0 mb-2 mb-md-0" style="color: var(--unasam-navy); font-weight: 700;">
                    <i class="fas fa-book-reader mr-2 text-muted"></i>Panel de publicaciones científicas
                </h2>
                <div class="d-flex flex-wrap align-items-center">
                    @if(auth()->user()->rol === 'super_admin')
                        <a href="{{ route('publicaciones.export.excel', request()->all()) }}" 
                           class="btn btn-success font-weight-bold shadow-sm mr-2 mb-2 mb-sm-0"
                           style="border-radius: 6px; background-color: #10b981; border: none;">
                            <i class="fas fa-file-excel mr-1"></i> Exportar Excel
                        </a>
                        <a href="{{ route('publicaciones.export.pdf', request()->all()) }}" 
                           class="btn btn-danger font-weight-bold shadow-sm mr-2 mb-2 mb-sm-0"
                           style="border-radius: 6px; background-color: #dc2626; border: none;">
                            <i class="fas fa-file-pdf mr-1"></i> Exportar PDF
                        </a>
                    @endif
                    @can('gestionar-publicaciones')
                        <a href="{{ route('publicaciones.create') }}" class="btn btn-primary font-weight-bold shadow-sm mb-2 mb-sm-0"
                            style="border-radius: 6px; background-color: var(--esat-blue); border: none;">
                            <i class="fas fa-plus-circle mr-1"></i> Nueva publicación
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        {{-- ==========================================
         SECCIÓN DE FILTROS
         ========================================== --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-body p-4 bg-light">
                <form method="GET" action="{{ route('publicaciones.index') }}">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label for="tipo" class="form-label font-weight-bold text-dark small text-uppercase">Tipo de
                                investigación</label>
                            <select name="tipo" id="tipo" class="form-control" style="border-radius: 6px;">
                                <option value="">-- Todos --</option>
                                @foreach ($tiposInvestigacion as $tipo)
                                    <option value="{{ $tipo }}" {{ request('tipo') == $tipo ? 'selected' : '' }}>
                                        {{ $tipo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-5 mb-3 mb-md-0">
                            <label for="q"
                                class="form-label font-weight-bold text-dark small text-uppercase">Buscar</label>
                            <input type="text" name="q" id="q" class="form-control"
                                value="{{ request('q') }}" placeholder="Autor, grupo, área, título..."
                                style="border-radius: 6px;">
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 mr-2 font-weight-bold"
                                style="border-radius: 6px; background-color: var(--unasam-navy);">
                                <i class="bi bi-search"></i> Filtrar
                            </button>
                            <a href="{{ route('publicaciones.index') }}" class="btn btn-outline-secondary w-100"
                                style="border-radius: 6px;">
                                Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ==========================================
         LISTA DE PUBLICACIONES (ESTILO REPOSITORIO)
         ========================================== --}}
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header border-0" style="background-color: var(--unasam-navy); color: white;">
                <h3 class="card-title mb-0 font-weight-bold" style="font-size: 1.05rem;">
                    <i class="fas fa-list-ul mr-2 text-white-50"></i>Datos registrados
                </h3>
            </div>

            <div class="card-body p-0 bg-white">

                @forelse ($publicaciones as $pub)
                    @php
                        // --- LÓGICA DE DETECCIÓN DE ARCHIVO ---
                        // Buscamos el nombre del archivo en todas las variables posibles que Laravel suele usar
                        $nombreArchivo =
                            $pub->archivo_principal_nombre ??
                            ($pub->path_archivo ??
                                ($pub->archivo ??
                                    (isset($pub->archivoPrincipal) ? basename($pub->archivoPrincipal->PATH) : null)));

                        // Obtenemos el ID correcto (id de publicación o de proyecto)
                        $idParaRuta = $pub->id ?? ($pub->id_proyecto ?? $pub->id_publicacion);

                        // Construimos la URL solo si encontramos un nombre de archivo
                        $urlPdfFinal =
                            $nombreArchivo && $idParaRuta
                                ? route('publicaciones.ver-archivo', ['id' => $idParaRuta, 'file' => $nombreArchivo])
                                : null;
                    @endphp

                    <div class="row m-0 border-bottom p-4 publication-item hover-bg-light transition-all">

                        {{-- COLUMNA IZQUIERDA: PORTADA (DISEÑO IDÉNTICO AL TUYO) --}}
                        {{-- COLUMNA IZQUIERDA: PORTADA --}}
<div class="col-md-3 col-lg-2 text-center mb-3 mb-md-0 d-flex flex-column align-items-center justify-content-center">
    <div class="border bg-light d-flex justify-content-center align-items-center shadow-sm" 
         style="width: 120px; height: 160px; border-radius: 4px; overflow: hidden; background-color: #f8f9fa; position: relative;">
        
        @php
            // 1. Intentamos obtener el archivo de varias formas posibles dependiendo de tu BD
            $nombreArchivo = $pub->archivo_principal_nombre 
                          ?? $pub->path_archivo 
                          ?? (isset($pub->archivoPrincipal) ? basename($pub->archivoPrincipal->PATH) : null);
            
            // 2. ID necesario para la ruta
            $idParaRuta = $pub->id ?? $pub->id_proyecto ?? $pub->id_publicacion ?? null;
            
            // 3. Generamos la URL segura
            $urlPdfFinal = ($nombreArchivo && $idParaRuta) 
                           ? route('publicaciones.ver-archivo', ['id' => $idParaRuta, 'file' => $nombreArchivo]) 
                           : null;
        @endphp

        @if ($urlPdfFinal)
            {{-- Canvas para el PDF --}}
            <canvas class="pdf-render-canvas" 
                    data-pdf-url="{{ $urlPdfFinal }}" 
                    style="width: 100%; height: 100%; object-fit: cover; display: none;"></canvas>
            
            {{-- Spinner de carga que desaparecerá automáticamente --}}
            <div class="pdf-loader" style="position: absolute;">
                <i class="fas fa-spinner fa-spin text-primary"></i>
            </div>
        @else
            {{-- Solo sale esto si realmente no hay nombre de archivo --}}
            <div class="text-center p-2 text-muted">
                <i class="fas fa-file-pdf fa-2x mb-1 opacity-25"></i><br>
                <span style="font-size: 0.7rem;"></span>
            </div>
        @endif
    </div>
</div>

                        {{-- COLUMNA DERECHA: TUS DATOS (IDÉNTICO AL TUYO) --}}
                        <div class="col-md-9 col-lg-10 position-relative">
                            <div class="mb-2">
                                <span class="badge"
                                    style="background-color: var(--unasam-navy); color: white;">{{ $pub->tipo ?? 'INVESTIGACIÓN' }}</span>
                                @if (!empty($pub->area))
                                    <span class="badge badge-light border text-muted ml-1">{{ $pub->area }}</span>
                                @endif
                            </div>

                            <h4 class="mb-1">
                                <a href="{{ route('publicaciones.show', $pub->id ?? $pub->id_proyecto) }}"
                                    style="color: var(--esat-blue); font-weight: 700; text-decoration: none;">
                                    {{ $pub->titulo }}
                                </a>
                            </h4>

                            <p class="text-muted mb-2">
                                <i class="fas fa-user-edit mr-1"></i> {{ $pub->autores ?? 'Sin autores' }}
                                @if (!empty($pub->grupo))
                                    <br><i class="fas fa-university mr-1"></i> {{ $pub->grupo }}
                                @endif
                            </p>

                            @if (!empty($pub->keywords))
                                <div class="mb-2">
                                    <i class="fas fa-tags mr-1 text-muted small"></i>
                                    @foreach(explode(',', $pub->keywords) as $keyword)
                                        <span class="badge badge-light border text-dark mr-1 mb-1 px-2 py-1" style="font-size: 0.72rem; font-weight: 500; border-radius: 12px; background-color: rgba(7, 20, 68, 0.03);">
                                            {{ trim($keyword) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="d-flex gap-2 justify-content-end align-items-center mt-3">
                                <a href="{{ route('publicaciones.show', $pub->id ?? $pub->id_proyecto) }}"
                                    class="btn btn-sm btn-outline-info mr-1"><i class="bi bi-eye"></i> Ver</a>
                                @can('gestionar-publicaciones')
                                    <a href="{{ route('publicaciones.edit', $pub->id ?? $pub->id_proyecto) }}"
                                        class="btn btn-sm btn-warning text-white mr-1"><i class="bi bi-pencil-square"></i>
                                        Actualizar</a>
                                @endcan
                                @can('eliminar-publicaciones')
                                    <form action="{{ route('publicaciones.destroy', $pub->id ?? $pub->id_proyecto) }}"
                                        method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('¿Eliminar?')"><i class="bi bi-trash"></i> Eliminar</button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-5 text-center text-muted">No hay publicaciones.</div>
                @endforelse
            </div>

            {{-- Paginación --}}
            @if (method_exists($publicaciones, 'links'))
                <div class="card-footer bg-white d-flex justify-content-center border-top-0 pt-4 pb-4">
                    {{ $publicaciones->links('pagination::bootstrap-5') }}
                </div>
            @endif

        </div>
    </div>

    {{-- Estilos CSS Extra para los efectos Hover --}}
    <style>
        .hover-bg-light:hover {
            background-color: #f8f9fa !important;
        }

        .transition-all {
            transition: all 0.2s ease-in-out;
        }

        .hover-text-navy:hover {
            color: var(--unasam-navy) !important;
            text-decoration: underline !important;
        }
    </style>
@endsection
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
        
        document.querySelectorAll('.pdf-render-canvas').forEach(canvas => {
            const url = canvas.dataset.pdfUrl;
            console.log("Intentando cargar PDF desde:", url); // <--- ESTO ES CLAVE

            if (!url) {
                console.error("URL vacía para este canvas");
                return;
            }

            pdfjsLib.getDocument(url).promise.then(pdf => {
                return pdf.getPage(1);
            }).then(page => {
                const viewport = page.getViewport({ scale: 0.35 });
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                return page.render({ canvasContext: context, viewport: viewport }).promise;
            }).then(() => {
                canvas.style.display = 'block';
            }).catch(err => {
                console.error("ERROR CRÍTICO DEL PDF:", err);
                // Si ves este error en la consola, haz clic en el enlace de arriba
            });
        });
    });
</script>
@endpush