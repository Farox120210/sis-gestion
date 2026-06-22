@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="h1">Panel de publicaciones científicas</div>
    </div>
    <hr>

    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    
    {{-- FILTROS --}}
    <form method="GET" action="{{ route('publicaciones.index') }}" class="mb-3">
        <div class="row">
            {{-- Filtro por tipo de investigación --}}
            <div class="col-md-4">
                <label for="tipo" class="form-label">Tipo de investigación</label>
                <select name="tipo" id="tipo" class="form-control">
                    <option value="">-- Todos --</option>
                    @foreach ($tiposInvestigacion as $tipo)
                        <option value="{{ $tipo }}" {{ request('tipo') == $tipo ? 'selected' : '' }}>
                            {{ $tipo }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Buscador general --}}
            <div class="col-md-5">
                <label for="q" class="form-label">Buscar</label>
                <input type="text" name="q" id="q" class="form-control" value="{{ request('q') }}"
                    placeholder="Autor, grupo, área, título...">
            </div>

            {{-- Botones --}}
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search"></i> Filtrar
                </button>

                <a href="{{ route('publicaciones.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Datos registrados</h3>
                        <div class="card-tools">
                            {{-- Botón Nueva Publicación: Solo Admin y Publicador --}}
                            @can('gestionar-publicaciones')
                                <a href="{{ route('publicaciones.create') }}" class="btn btn-primary">
                                    <i class="bi bi-file-earmark-plus"></i> Nueva publicación
                                </a>
                            @endcan
                        </div>
                    </div>

                    <div class="card-body" style="display:block;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-striped table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th><center>Nro.</center></th>
                                        <th><center>Título</center></th>
                                        <th><center>Autores</center></th>
                                        <th><center>Grupo de investigación</center></th>
                                        <th><center>Área investigativa</center></th>
                                        <th><center>Tipo de investigacion</center></th>
                                        <th><center>Acciones</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($publicaciones as $pub)
                                        <tr>
                                            <td>
                                                <center>{{ $loop->iteration }}</center>
                                            </td>
                                            <td>{{ $pub->titulo }}</td>
                                            <td>{{ $pub->autores ?? '—' }}</td>
                                            <td>
                                                <center>{{ $pub->grupo ?? '—' }}</center>
                                            </td>
                                            <td>
                                                <center>{{ $pub->area ?? '—' }}</center>
                                            </td>
                                            <td class="text-center">
                                                {{ $pub->tipo ?? '—' }}
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group" aria-label="Acciones">
                                                    @if (!empty($pub->id))
                                                        {{-- Botón Ver: Visible para todos --}}
                                                        <a href="{{ route('publicaciones.show', $pub->id) }}"
                                                            class="btn btn-warning btn-sm">
                                                            <i class="bi bi-eye"></i> Ver
                                                        </a>
                                                        
                                                        {{-- Botón Actualizar: Solo Admin y Publicador --}}
                                                        @can('gestionar-publicaciones')
                                                            <a href="{{ route('publicaciones.edit', $pub->id) }}"
                                                                class="btn btn-success btn-sm">
                                                                <i class="bi bi-pencil-square"></i> Actualizar
                                                            </a>
                                                        @endcan
                                                        
                                                        {{-- Botón Eliminar: Solo Super Admin --}}
                                                        @can('eliminar-publicaciones')
                                                            <form action="{{ route('publicaciones.destroy', $pub->id) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm"
                                                                    onclick="return confirm('¿Eliminar esta publicación?')">
                                                                    <i class="bi bi-trash"></i> Eliminar
                                                                </button>
                                                            </form>
                                                        @endcan
                                                    @else
                                                        {{-- Botón Añadir medio: Solo Admin y Publicador --}}
                                                        @can('gestionar-publicaciones')
                                                            <a href="{{ route('publicaciones.create') }}?project={{ $pub->id_proyecto }}"
                                                                class="btn btn-primary btn-sm">
                                                                <i class="bi bi-plus-circle"></i> Añadir medio
                                                            </a>
                                                        @endcan
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-3">No hay proyectos registrados todavía.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Paginación --}}
                        @if (method_exists($publicaciones, 'links'))
                            <div class="mt-2">
                                {{ $publicaciones->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection