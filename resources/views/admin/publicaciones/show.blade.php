@extends('layouts.admin')

@section('content')
    <div class="container-fluid py-4">
        
        {{-- ==========================================
             BARRA SUPERIOR DE ACCIONES (Volver, Editar, Eliminar)
             ========================================== --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('publicaciones.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
            
            <div>
                <a href="{{ route('publicaciones.edit', $pub->id_publicacion) }}" class="btn btn-warning btn-sm text-white">
                    <i class="bi bi-pencil-square"></i> Editar
                </a>
                
                <form action="{{ route('publicaciones.destroy', $pub->id_publicacion) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" onclick="return confirm('¿Eliminar esta publicación?')" class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>

        {{-- ==========================================
             TÍTULO PRINCIPAL (Estilo Repositorio)
             ========================================== --}}
        <h2 class="mb-4" style="color: var(--unasam-navy); font-weight: 700; line-height: 1.3;">
            {{ $pub->titulo }}
            @if ($pub->estado)
                <span class="badge badge-info align-middle ml-2" style="font-size: 0.8rem; font-weight: 500;">{{ $pub->estado }}</span>
            @endif
        </h2>

        <div class="row">
            {{-- ==========================================
                 COLUMNA IZQUIERDA (30%) - PORTADA Y ACCIONES PDF
                 ========================================== --}}
            <div class="col-md-4 col-lg-3 mb-4">
                
                <div class="card shadow-sm mb-3 border-0" style="border-radius: 8px; overflow: hidden;">
                    <div class="card-header text-center border-0" style="background-color: var(--unasam-navy); color: white;">
                        <h5 class="card-title w-100 mb-0" style="font-size: 1rem; font-weight: 600;">Portada</h5>
                    </div>
                    <div class="card-body text-center bg-white">
                        
                        @php
                            $fileName = $archivoPrincipal?->PATH ? basename($archivoPrincipal->PATH) : null;
                            $tam = $archivoPrincipal && $archivoPrincipal->SIZE_BYTES ? number_format($archivoPrincipal->SIZE_BYTES / 1024, 1) . ' KB' : '—';
                            $ver = $archivoPrincipal->VERSION ?? 1;
                        @endphp

                        <div class="border mb-3 bg-light d-flex justify-content-center align-items-center" style="border-radius: 6px; min-height: 220px; overflow: hidden; background-color: #f8f9fa;">
                            @if ($fileName)
                                <canvas id="pdf-preview" style="max-width: 100%; height: auto; display: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></canvas>
                                
                                <div id="pdf-loading" class="text-muted small font-weight-bold">
                                    <i class="fas fa-spinner fa-spin mr-2 text-primary"></i>Generando portada...
                                </div>
                            @else
                                <img src="https://upload.wikimedia.org/wikipedia/commons/8/87/PDF_file_icon.svg" 
                                     alt="Sin documento" 
                                     style="height: 110px; opacity: 0.5;">
                            @endif
                        </div>
                        
                        @if ($fileName)
                            <div class="text-left mb-3">
                                <span class="badge bg-secondary mb-2">{{ $tam }}</span>
                                <p class="small text-muted mb-0 text-break" style="line-height: 1.2;">
                                    {{ $archivoPrincipal->NOMBRE_ORIGINAL ?? '—' }}
                                </p>
                            </div>
                            
                            <a href="{{ route('publicaciones.ver-archivo', ['id' => $pub->id_publicacion, 'file' => $fileName]) }}" 
                               target="_blank" rel="noopener" class="btn btn-outline-danger btn-block mb-2 font-weight-bold" style="border-radius: 6px;">
                                <i class="bi bi-file-earmark-pdf"></i> Ver PDF (v{{ $ver }})
                            </a>
                            
                            <a href="{{ route('publicaciones.descargar-archivo', ['id' => $pub->id_publicacion, 'file' => $fileName]) }}" 
                               class="btn btn-primary btn-block font-weight-bold" style="border-radius: 6px;">
                                <i class="bi bi-download"></i> Descargar
                            </a>
                        @else
                            <div class="alert alert-warning p-2 small m-0 shadow-none border-0">No hay PDF principal asignado aún.</div>
                        @endif
                    </div>
                </div>

                <div class="card shadow-sm mb-3 border-0" style="border-radius: 8px; overflow: hidden;">
                    <div class="card-header text-center border-0" style="background-color: var(--unasam-navy); color: white;">
                        <h5 class="card-title w-100 mb-0" style="font-size: 1rem; font-weight: 600;">Citas bibliográficas</h5>
                    </div>
                    <div class="card-body text-center p-3 bg-white">
                        <button class="btn btn-success btn-sm btn-block font-weight-bold" style="border-radius: 6px;" onclick="alert('Funcionalidad Cómo Citar en construcción')">
                            Cómo citar
                        </button>
                    </div>
                </div>

            </div>
            
        </div>
        
    </div>
<div class="row">
        <div class="col-12">
            <div class="card card-outline card-danger">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-file-earmark-pdf"></i> Archivo principal (PDF)
                    </h3>
                    @if ($archivoPrincipal)
                        @php
                            $pdfLink = $archivoPrincipal->URL ?: $archivoPrincipal->PATH;
                            $tam = $archivoPrincipal->SIZE_BYTES
                                ? number_format($archivoPrincipal->SIZE_BYTES / 1024, 1) . ' KB'
                                : '—';
                            $ver = $archivoPrincipal->VERSION ?? 1;
                        @endphp
                        <a href="{{ $pdfLink }}" target="_blank" class="btn btn-outline-danger">
                            <i class="bi bi-file-earmark-pdf"></i> Ver PDF (v{{ $ver }})
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if ($archivoPrincipal)
                        <div class="row">
                            <div class="col-md-8">
                                <strong>Nombre:</strong> {{ $archivoPrincipal->NOMBRE_ORIGINAL ?? '—' }}<br>
                                <strong>Tipo:</strong> {{ $archivoPrincipal->MIME_TYPE ?? '—' }}<br>
                                <strong>Versión:</strong> v{{ $archivoPrincipal->VERSION ?? 1 }}
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-uppercase fw-bold mb-1" style="font-size: 0.85rem; color: #6c757d; font-weight: 700;">Presupuesto</h6>
                                <p class="m-0 text-dark">{{ is_null($pub->presupuesto) ? '—' : 'S/ ' . number_format($pub->presupuesto, 2) }}</p>
                            </div>
                        </div>

                        <hr class="my-4" style="border-top: 1px dashed #e9ecef;">

                        <h6 class="text-uppercase fw-bold mb-2" style="font-size: 0.85rem; color: #6c757d; font-weight: 700;">Resumen / Descripción</h6>
                        <p class="text-justify text-dark" style="line-height: 1.7; font-size: 0.95rem;">
                            {{ $pub->descripcion ?: 'Este proyecto no cuenta con una descripción detallada en este momento. Puede revisar el documento adjunto para más información.' }}
                        </p>

                        <hr class="my-4" style="border-top: 1px dashed #e9ecef;">

                        <h6 class="text-uppercase fw-bold mb-2" style="font-size: 0.85rem; color: #6c757d; font-weight: 700;">Palabras clave</h6>
                        <div class="mb-4">
                            @if(!empty($pub->keywords))
                                @foreach(explode(',', $pub->keywords) as $keyword)
                                    <span class="badge badge-light border text-dark mr-1 mb-1 px-3 py-2" style="font-size: 0.85rem; border-radius: 20px; background-color: rgba(7, 20, 68, 0.05);">
                                        <i class="fas fa-tag mr-1 text-muted"></i>{{ trim($keyword) }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-muted small">No se registraron palabras clave.</span>
                            @endif
                        </div>

                        <hr class="my-4" style="border-top: 1px dashed #e9ecef;">

                        <h6 class="text-uppercase fw-bold mb-1" style="font-size: 0.85rem; color: #6c757d; font-weight: 700;">Colección / Medio</h6>
                        <p class="mb-0" style="color: var(--unasam-navy); font-weight: 500;">{{ $pub->revista ?: '—' }}</p>

                    </div>
                </div>
                
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Solo cargamos PDF.js si realmente hay un archivo para mostrar --}}
    @if (!empty($fileName))
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
        
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Configuramos el worker de PDF.js
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

                // Usamos la misma ruta segura que usas en tu botón "Ver PDF"
                const url = "{{ route('publicaciones.ver-archivo', ['id' => $pub->id_publicacion, 'file' => $fileName]) }}";

                const loadingTask = pdfjsLib.getDocument(url);
                
                loadingTask.promise.then(function(pdf) {
                    // Extraer solo la primera página (1)
                    return pdf.getPage(1);
                }).then(function(page) {
                    // Escala para darle buena resolución
                    const scale = 1.2; 
                    const viewport = page.getViewport({scale: scale});

                    // Seleccionamos el canvas
                    const canvas = document.getElementById('pdf-preview');
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    
                    page.render(renderContext).promise.then(function() {
                        // Al terminar de pintar, ocultamos el estado de "Cargando..."
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
@endpush