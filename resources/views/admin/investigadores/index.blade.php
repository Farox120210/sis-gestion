@extends('layouts.admin')

@section('content')
    {{-- Encabezado: Título + Buscador --}}
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <h1 class="h3 mb-0">Panel de investigadores</h1>
        </div>
        <div class="col-md-6">
            <form action="{{ route('investigadores.index') }}" method="GET" class="d-flex justify-content-end" role="search">
                <input type="text"
                       name="q"
                       class="form-control me-2"
                       placeholder="Buscar..."
                       value="{{ request('q') }}">
                <button class="btn btn-outline-primary" type="submit" aria-label="Buscar">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </div>

    <hr>

    {{-- Listado --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Datos registrados</h3>
                    <div class="card-tools">
                        <a href="{{ route('investigadores.create') }}" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Nuevo investigador
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 70px;">Nro.</th>
                                    <th>Nombre</th>
                                    <th class="text-center">Correo institucional</th>
                                    <th class="text-center" style="width: 160px;">Tipo</th>
                                    <th>Grupos de investigación</th>
                                    <th class="text-center" style="width: 260px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($investigadores as $inv)
                                    <tr>
                                        <td class="text-center">
                                            {{ $loop->iteration + ($investigadores->firstItem() - 1) }}
                                        </td>
                                        <td>{{ $inv->nombre_completo }}</td>
                                        <td class="text-center">{{ $inv->correo ?? '—' }}</td>
                                        <td class="text-center">{{ $inv->tipo ?? '—' }}</td>
                                        <td>{{ $inv->grupos ?: '—' }}</td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group" aria-label="Acciones">
                                                <a href="{{ route('investigadores.show', $inv->id) }}" class="btn btn-warning btn-sm">
                                                    <i class="bi bi-eye"></i> Ver
                                                </a>
                                                <a href="{{ route('investigadores.edit', $inv->id) }}" class="btn btn-success btn-sm">
                                                    <i class="bi bi-pencil-square"></i> Actualizar
                                                </a>
                                                <form action="{{ route('investigadores.destroy', $inv->id) }}"
                                                      method="POST"
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-danger btn-sm"
                                                            onclick="return confirm('¿Eliminar este investigador?')">
                                                        <i class="bi bi-trash"></i> Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">
                                            No hay investigadores registrados todavía.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginación --}}
                    @if (method_exists($investigadores, 'links'))
                        <div class="mt-2 d-flex justify-content-center">
                            {{ $investigadores->appends(['q' => request('q')])->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Exportar datos --}}
    <div class="row mt-3">
        <div class="col-12">
            <div class="card card-outline card-success">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Exportar datos</h3>
                </div>

                <div class="card-body">
                    <div class="row g-2 align-items-center">
                        {{-- Botón: reporte general --}}
                        <div class="col-auto">
                            <a href="{{ route('investigadores.export.general') }}"
                               class="btn btn-success btn-sm d-flex align-items-center">
                                <i class="bi bi-file-earmark-excel me-1"></i>
                                Exportar todos a Excel
                            </a>
                        </div>

                        {{-- Formulario: reportes filtrados --}}
                        <div class="col">
                            <form action="{{ route('investigadores.export.filtrado') }}" method="GET">
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-md-auto">
                                        <select name="grupo"
                                                class="form-select form-select-sm"
                                                aria-label="Filtrar por grupo">
                                            <option value="">— Grupo —</option>
                                            @foreach (($grupos ?? collect()) as $g)
                                                <option value="{{ $g->ID_GRUPO }}"
                                                    {{ request('grupo') == $g->ID_GRUPO ? 'selected' : '' }}>
                                                    {{ $g->NOMBRE_GRUPO }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-auto">
                                        {{-- Si prefieres texto libre, cambia el select por un input --}}
                                        <select name="tipo"
                                                class="form-select form-select-sm"
                                                aria-label="Filtrar por tipo">
                                            <option value="">— Tipo —</option>
                                            <option value="Tesista"             {{ request('tipo') == 'Tesista' ? 'selected' : '' }}>Tesista</option>
                                            <option value="Voluntario"          {{ request('tipo') == 'Voluntario' ? 'selected' : '' }}>Voluntario</option>
                                            <option value="Investigador Junior" {{ request('tipo') == 'Investigador Junior' ? 'selected' : '' }}>Investigador Junior</option>
                                            <option value="Investigador Senior" {{ request('tipo') == 'Investigador Senior' ? 'selected' : '' }}>Investigador Senior</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-auto">
                                        <button type="submit" class="btn btn-outline-success btn-sm" id="btn-export-filtrado">
                                            <i class="bi bi-download me-1"></i>
                                            Exportar filtrado
                                        </button>
                                    </div>

                                    @if (request('grupo') || request('tipo'))
                                        <div class="col-12 col-md-auto">
                                            <a href="{{ route('investigadores.index') }}"
                                               class="btn btn-outline-secondary btn-sm">
                                                Limpiar
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- UX: deshabilitar exportación si no hay filtros --}}
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const grupo = document.querySelector('select[name="grupo"]');
                            const tipo  = document.querySelector('select[name="tipo"]');
                            const btn   = document.getElementById('btn-export-filtrado');

                            function toggle() {
                                if (!btn) return;
                                btn.disabled = !(grupo?.value || (tipo?.value || '').trim());
                            }
                            toggle();
                            grupo?.addEventListener('change', toggle);
                            tipo?.addEventListener('change', toggle);
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
@endsection
