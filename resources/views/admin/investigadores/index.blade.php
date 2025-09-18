@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="h1">Panel de investigadores</div>
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
                        <a href="{{ route('investigadores.create') }}" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Nuevo investigador
                        </a>
                    </div>
                </div>

                <div class="card-body" style="display:block;">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th><center>Nro.</center></th>
                                    <th><center>Nombre</center></th>
                                    <th><center>Correo institucional</center></th>
                                    <th><center>Tipo</center></th>
                                    <th><center>Grupos de investigación</center></th>
                                    <th><center>Acciones</center></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($investigadores as $inv)
                                    <tr>
                                        <td><center>{{ $loop->iteration + ($investigadores->firstItem() - 1) }}</center></td>
                                        <td>{{ $inv->nombre_completo }}</td>
                                        <td><center>{{ $inv->correo ?? '—' }}</center></td>
                                        <td><center>{{ $inv->tipo ?? '—' }}</center></td>
                                        <td>{{ $inv->grupos ?: '—' }}</td>

                                        <td class="text-center">
                                            <div class="btn-group" role="group" aria-label="Acciones">
                                                <a href="{{ route('investigadores.show', $inv->id) }}" class="btn btn-warning btn-sm">
                                                    <i class="bi bi-eye"></i> Ver
                                                </a>
                                                <a href="{{ route('investigadores.edit', $inv->id) }}" class="btn btn-success btn-sm">
                                                    <i class="bi bi-pencil-square"></i> Actualizar
                                                </a>
                                                <form action="{{ route('investigadores.destroy', $inv->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                            onclick="return confirm('¿Eliminar este investigador?')">
                                                        <i class="bi bi-trash"></i> Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">No hay investigadores registrados todavía.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (method_exists($investigadores, 'links'))
                        <div class="mt-2">
                            {{ $investigadores->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
