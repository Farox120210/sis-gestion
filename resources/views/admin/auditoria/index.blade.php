@extends('layouts.admin')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-history text-info mr-2"></i>Bitácora de Acciones de Usuario
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Auditoría de Acciones</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">

        <!-- Formulario de Filtros -->
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros de Búsqueda</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.auditoria.index') }}">
                    <div class="row">
                        <!-- Filtro por Rol / Cargo -->
                        <div class="col-md-3 mb-2">
                            <label for="rol" class="text-sm">Cargo / Rol:</label>
                            <select name="rol" id="rol" class="form-control form-control-sm">
                                <option value="">-- Todos los Cargos --</option>
                                <option value="super_admin" {{ request('rol') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                <option value="publicador" {{ request('rol') === 'publicador' ? 'selected' : '' }}>Publicador</option>
                                <option value="espectador" {{ request('rol') === 'espectador' ? 'selected' : '' }}>Espectador</option>
                            </select>
                        </div>

                        <!-- Filtro por Usuario -->
                        <div class="col-md-3 mb-2">
                            <label for="usuario" class="text-sm">Nombre de Usuario:</label>
                            <input type="text" name="usuario" id="usuario" class="form-control form-control-sm"
                                   placeholder="Ej. Juan Pérez" value="{{ request('usuario') }}">
                        </div>

                        <!-- Filtro por Módulo -->
                        <div class="col-md-2 mb-2">
                            <label for="modulo" class="text-sm">Módulo:</label>
                            <select name="modulo" id="modulo" class="form-control form-control-sm">
                                <option value="">-- Todos --</option>
                                @foreach($modulos as $mod)
                                    <option value="{{ $mod }}" {{ request('modulo') === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtro Fecha Inicio -->
                        <div class="col-md-2 mb-2">
                            <label for="fecha_inicio" class="text-sm">Fecha Inicio:</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control form-control-sm" value="{{ request('fecha_inicio') }}">
                        </div>

                        <!-- Filtro Fecha Fin -->
                        <div class="col-md-2 mb-2">
                            <label for="fecha_fin" class="text-sm">Fecha Fin:</label>
                            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control form-control-sm" value="{{ request('fecha_fin') }}">
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-8 mb-2">
                            <input type="text" name="q" class="form-control form-control-sm" placeholder="Buscar por palabra clave en la descripción, acción o IP..." value="{{ request('q') }}">
                        </div>
                        <div class="col-md-4 mb-2 text-right">
                            <button type="submit" class="btn btn-info btn-sm">
                                <i class="fas fa-search mr-1"></i> Filtrar
                            </button>
                            <a href="{{ route('admin.auditoria.index') }}" class="btn btn-secondary btn-sm ml-1">
                                <i class="fas fa-undo mr-1"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Auditoría -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h3 class="card-title text-bold"><i class="fas fa-list-alt text-primary mr-1"></i> Registro de Eventos ({{ $logs->total() }})</h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped mb-0 text-sm">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Fecha y Hora</th>
                            <th>Usuario</th>
                            <th>Cargo / Rol</th>
                            <th>Módulo</th>
                            <th>Acción</th>
                            <th>Descripción / Detalles</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>
                                    <span class="text-nowrap"><i class="far fa-clock text-muted mr-1"></i>{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                                    <br><small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <strong>{{ $log->user_name }}</strong>
                                    @if($log->user_id)
                                        <br><small class="text-muted">ID: {{ $log->user_id }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($log->user_role === 'super_admin')
                                        <span class="badge badge-danger">Super Admin</span>
                                    @elseif($log->user_role === 'publicador')
                                        <span class="badge badge-warning">Publicador</span>
                                    @else
                                        <span class="badge badge-secondary">Espectador</span>
                                    @endif
                                </td>
                                <td><span class="badge badge-info">{{ $log->module }}</span></td>
                                <td>
                                    @if($log->action === 'CREAR')
                                        <span class="badge badge-success"><i class="fas fa-plus mr-1"></i>CREAR</span>
                                    @elseif($log->action === 'ACTUALIZAR')
                                        <span class="badge badge-primary"><i class="fas fa-edit mr-1"></i>ACTUALIZAR</span>
                                    @elseif($log->action === 'ELIMINAR')
                                        <span class="badge badge-danger"><i class="fas fa-trash mr-1"></i>ELIMINAR</span>
                                    @elseif($log->action === 'LOGIN')
                                        <span class="badge badge-info"><i class="fas fa-sign-in-alt mr-1"></i>LOGIN</span>
                                    @elseif($log->action === 'LOGOUT')
                                        <span class="badge badge-dark"><i class="fas fa-sign-out-alt mr-1"></i>LOGOUT</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $log->action }}</span>
                                    @endif
                                </td>
                                <td>{{ $log->description }}</td>
                                <td><code>{{ $log->ip_address ?? 'N/A' }}</code></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                                    No se encontraron registros de auditoría con los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $logs->links() }}
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
