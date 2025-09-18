@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Detalle de publicación</h1>
            <div>
                <a href="{{ route('publicaciones.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <a href="{{ route('publicaciones.edit', $pub->id_publicacion) }}" class="btn btn-warning">
                    <i class="bi bi-pencil-square"></i> Editar
                </a>
            </div>
        </div>
    </div>

    <hr>

    {{-- Encabezado --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <h2 class="h4 mb-2">{{ $pub->titulo }}</h2>
                    <div class="text-muted">
                        <span class="mr-3"><strong>Año proyecto:</strong> {{ $pub->anio_proyecto }}</span>
                        <span class="mr-3"><strong>Año publicación:</strong> {{ $pub->anio_publicacion }}</span>
                        @if ($pub->estado)
                            <span class="badge badge-info">{{ $pub->estado }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Datos del proyecto --}}
    <div class="row">
        <div class="col-md-7">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title mb-0">Datos del proyecto</h3>
                </div>
                {{-- Archivo principal (PDF) --}}

                <div class="card-body">
                    <p class="mb-3">{{ $pub->descripcion ?: '—' }}</p>

                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <strong>Área de investigación:</strong><br>
                            {{ $pub->area ?: '—' }}
                        </div>
                        <div class="col-sm-6 mb-2">
                            <strong>Presupuesto:</strong><br>
                            {{ is_null($pub->presupuesto) ? '—' : number_format($pub->presupuesto, 2) }}
                        </div>
                        <div class="col-sm-6 mb-2">
                            <strong>Fecha inicio:</strong><br>
                            {{ $pub->fecha_inicio ?: '—' }}
                        </div>
                        <div class="col-sm-6 mb-2">
                            <strong>Fecha fin:</strong><br>
                            {{ $pub->fecha_fin ?: '—' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Publicación (revista) --}}
        <div class="col-md-5">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title mb-0">Revista</h3>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Nombre:</strong><br>
                        {{ $pub->revista }}
                    </div>
                    <div class="mb-2">
                        <strong>Editorial:</strong><br>
                        {{ $pub->editorial ?: '—' }}
                    </div>
                    <div class="mb-0">
                        <strong>País:</strong><br>
                        {{ $pub->pais ?: '—' }}
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
                                <strong>Tamaño:</strong> {{ $tam }}<br>
                                <strong>Subido:</strong>
                                {{ \Carbon\Carbon::parse($archivoPrincipal->FECHA_SUBIDA)->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    @else
                        <span class="text-muted">No hay PDF principal asignado aún.</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    {{-- Autores --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title mb-0">Autores</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-1">
                            <i class="bi bi-person-fill"></i>
                            <strong>{{ $pub->autor_principal }}</strong>
                            <span class="badge badge-primary">Autor principal</span>
                        </li>
                        @foreach ($autores as $a)
                            @continue($a->nombre === $pub->autor_principal) {{-- evita duplicar autor principal --}}
                            <li class="mb-1">
                                <i class="bi bi-person"></i> {{ $a->nombre }}
                                @if ($a->ROL_EN_PROYECTO)
                                    <span class="text-muted">— {{ $a->ROL_EN_PROYECTO }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Acciones inferiores --}}
    <div class="row">
        <div class="col-12 text-right">
            <a href="{{ route('publicaciones.index') }}" class="btn btn-outline-secondary">Volver al listado</a>
            <a href="{{ route('publicaciones.edit', $pub->id_publicacion) }}" class="btn btn-warning">
                <i class="bi bi-pencil-square"></i> Editar
            </a>

            <form action="{{ route('publicaciones.destroy', $pub->id_publicacion) }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" onclick="return confirm('¿Eliminar esta publicación?')" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </form>

        </div>
    </div>
@endsection
