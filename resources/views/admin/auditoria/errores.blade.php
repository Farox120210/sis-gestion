@extends('layouts.admin')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-exclamation-triangle text-danger mr-2"></i>Log de Errores del Sistema
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Log de Errores</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">

        <!-- Formulario de Filtros -->
        <div class="card card-outline card-danger shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros de Errores</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.auditoria.errores') }}">
                    <div class="row">
                        <!-- Filtro por Nivel -->
                        <div class="col-md-3 mb-2">
                            <label for="nivel" class="text-sm">Nivel de Error:</label>
                            <select name="nivel" id="nivel" class="form-control form-control-sm">
                                <option value="">-- Todos los Niveles --</option>
                                <option value="CRITICAL" {{ request('nivel') === 'CRITICAL' ? 'selected' : '' }}>CRÍTICO</option>
                                <option value="ERROR" {{ request('nivel') === 'ERROR' ? 'selected' : '' }}>ERROR</option>
                                <option value="WARNING" {{ request('nivel') === 'WARNING' ? 'selected' : '' }}>ADVERTENCIA</option>
                                <option value="INFO" {{ request('nivel') === 'INFO' ? 'selected' : '' }}>INFORMACIÓN</option>
                            </select>
                        </div>

                        <!-- Filtro por Módulo -->
                        <div class="col-md-3 mb-2">
                            <label for="modulo" class="text-sm">Módulo afectado:</label>
                            <input type="text" name="modulo" id="modulo" class="form-control form-control-sm"
                                   placeholder="Ej. Publicaciones, Usuarios..." value="{{ request('modulo') }}">
                        </div>

                        <!-- Filtro por Fecha -->
                        <div class="col-md-3 mb-2">
                            <label for="fecha" class="text-sm">Fecha de Evento:</label>
                            <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" value="{{ request('fecha') }}">
                        </div>

                        <!-- Búsqueda texto -->
                        <div class="col-md-3 mb-2">
                            <label for="q" class="text-sm">Buscar en mensaje:</label>
                            <input type="text" name="q" id="q" class="form-control form-control-sm"
                                   placeholder="Palabra clave..." value="{{ request('q') }}">
                        </div>
                    </div>

                    <div class="text-right mt-2">
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-search mr-1"></i> Filtrar Errores
                        </button>
                        <a href="{{ route('admin.auditoria.errores') }}" class="btn btn-secondary btn-sm ml-1">
                            <i class="fas fa-undo mr-1"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Errores -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h3 class="card-title text-bold"><i class="fas fa-bug text-danger mr-1"></i> Eventos y Excepciones ({{ $errores->total() }})</h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped mb-0 text-sm">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th>Nivel</th>
                            <th>Fecha y Hora</th>
                            <th>Módulo Dónde Falló</th>
                            <th>Usuario / Origen</th>
                            <th>Mensaje / Descripción del Error</th>
                            <th>Detalles / Traza</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($errores as $err)
                            <tr>
                                <td>{{ $err->id }}</td>
                                <td>
                                    @if($err->level === 'CRITICAL')
                                        <span class="badge badge-danger px-2 py-1"><i class="fas fa-radiation mr-1"></i>CRÍTICO</span>
                                    @elseif($err->level === 'ERROR')
                                        <span class="badge badge-danger px-2 py-1"><i class="fas fa-exclamation-circle mr-1"></i>ERROR</span>
                                    @elseif($err->level === 'WARNING')
                                        <span class="badge badge-warning px-2 py-1"><i class="fas fa-exclamation-triangle mr-1"></i>WARNING</span>
                                    @else
                                        <span class="badge badge-info px-2 py-1"><i class="fas fa-info-circle mr-1"></i>INFO</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-nowrap"><i class="far fa-clock text-muted mr-1"></i>{{ $err->created_at->format('d/m/Y H:i:s') }}</span>
                                    <br><small class="text-muted">{{ $err->created_at->diffForHumans() }}</small>
                                </td>
                                <td><span class="badge badge-dark">{{ $err->module }}</span></td>
                                <td>
                                    <strong>{{ $err->user_name ?? 'Invitado / Sistema' }}</strong>
                                    <br><code>{{ $err->ip_address ?? '127.0.0.1' }}</code>
                                </td>
                                <td>
                                    <span class="text-danger font-weight-bold">{{ $err->message }}</span>
                                </td>
                                <td>
                                    @if($err->trace)
                                        <button type="button" class="btn btn-outline-danger btn-xs" data-toggle="modal" data-target="#modalTrace{{ $err->id }}">
                                            <i class="fas fa-code mr-1"></i> Ver Traza
                                        </button>

                                        <!-- Modal para ver Stack Trace -->
                                        <div class="modal fade" id="modalTrace{{ $err->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white py-2">
                                                        <h5 class="modal-title text-sm"><i class="fas fa-bug mr-1"></i> Detalle de Error #{{ $err->id }}</h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>Mensaje:</strong> {{ $err->message }}</p>
                                                        <p><strong>Módulo:</strong> {{ $err->module }} | <strong>Fecha:</strong> {{ $err->created_at->format('d/m/Y H:i:s') }}</p>
                                                        <hr>
                                                        <label class="text-sm">Stack Trace:</label>
                                                        <pre class="bg-dark text-light p-3 rounded" style="max-height: 400px; font-size: 11px; white-space: pre-wrap;">{{ $err->trace }}</pre>
                                                    </div>
                                                    <div class="modal-footer py-1">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted text-xs">Sin traza</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-check-circle text-success fa-2x mb-2 d-block"></i>
                                    No hay errores registrados con los criterios seleccionados. ¡El sistema está funcionando correctamente!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($errores->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $errores->links() }}
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
