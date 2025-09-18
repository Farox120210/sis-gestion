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

    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title mb-0">Datos registrados</h3>
                    <div class="card-tools">
                        <a href="{{ route('publicaciones.create') }}" class="btn btn-primary">
                            <i class="bi bi-file-earmark-plus"></i> Nueva publicación
                        </a>
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
                                    <th><center>Enlaces</center></th>
                                    <th><center>Acciones</center></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($publicaciones as $pub)
                                    <tr>
                                        <td><center>{{ $loop->iteration }}</center></td>
                                        <td>{{ $pub->titulo }}</td>
                                        <td>{{ $pub->autores ?? '—' }}</td>
                                        <td><center>{{ $pub->grupo ?? '—' }}</center></td>
                                        <td><center>{{ $pub->area ?? '—' }}</center></td>

                                        <td class="text-center">
                                            @if (!empty($pub->doi ?? null))
                                                <a href="https://doi.org/{{ $pub->doi }}" target="_blank" class="btn btn-outline-dark btn-sm">
                                                    <i class="bi bi-link-45deg"></i> DOI
                                                </a>
                                            @endif
                                            @if (!empty($pub->pdf_url ?? null))
                                                <a href="{{ $pub->pdf_url }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                                </a>
                                            @endif
                                            @if (empty($pub->doi ?? null) && empty($pub->pdf_url ?? null))
                                                —
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <div class="btn-group" role="group" aria-label="Acciones">
                                                <a href="{{ route('publicaciones.show', $pub->id) }}" class="btn btn-warning btn-sm">
                                                    <i class="bi bi-eye"></i> Ver
                                                </a>
                                                <a href ="{{route('publicaciones.edit',$pub->id)}} " class="btn btn-success btn-sm">
                                                    <i class="bi bi-pencil-square"></i> Actualizar
                                                </a>
                                                <form method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                            onclick="return confirm('¿Eliminar esta publicación?')">
                                                        <i class="bi bi-trash"></i> Eliminar
                                                    </button>
                                                </form>
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

                    {{-- Paginación (si usas ->paginate() en el controlador) --}}
                    @if (method_exists($publicaciones, 'links'))
                        <div class="mt-2">
                            {{ $publicaciones->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
