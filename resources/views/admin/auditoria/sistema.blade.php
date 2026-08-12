@extends('layouts.admin')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-server text-primary mr-2"></i>Estado del Sistema y Respaldos de Seguridad
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Inicio</a></li>
                    <li class="breadcrumb-item active">Estado y Respaldos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">

        @if(session('ok'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('ok') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Tarjetas de Información del Sistema -->
        <div class="row">
            <!-- Versión del Sistema -->
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-code-branch"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Versión del Sistema</span>
                        <span class="info-box-number text-lg">{{ $systemVersion }}</span>
                        <small class="text-muted">Laravel {{ $laravelVersion }} (PHP {{ $phpVersion }})</small>
                    </div>
                </div>
            </div>

            <!-- Estado de la Base de Datos -->
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-database"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Base de Datos MySQL</span>
                        <span class="info-box-number text-lg">{{ $dbSizeMb }} MB</span>
                        <small class="text-muted">{{ $mysqlVersion }}</small>
                    </div>
                </div>
            </div>

            <!-- Último Respaldo -->
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-shield-alt text-white"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Último Respaldo</span>
                        @if($lastBackup)
                            <span class="info-box-number text-sm">{{ $lastBackup->created_at->format('d/m/Y H:i') }}</span>
                            <small class="text-muted">{{ round($lastBackup->file_size / 1024 / 1024, 2) }} MB</small>
                        @else
                            <span class="info-box-number text-sm text-danger">Sin respaldos</span>
                            <small class="text-muted">No se ha registrado copia</small>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sincronización Google Drive -->
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box shadow-sm">
                    @if($gdriveActive)
                        <span class="info-box-icon bg-primary elevation-1"><i class="fab fa-google-drive"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Google Drive Sync</span>
                            <span class="info-box-number text-success text-sm"><i class="fas fa-check-circle mr-1"></i>ACTIVA</span>
                            <small class="text-muted">Credenciales conectadas</small>
                        </div>
                    @else
                        <span class="info-box-icon bg-secondary elevation-1"><i class="fab fa-google-drive"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Google Drive Sync</span>
                            <span class="info-box-number text-warning text-sm"><i class="fas fa-exclamation-triangle mr-1"></i>INACTIVA</span>
                            <small class="text-muted">Faltan credenciales</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sección de Respaldos de Seguridad -->
            <div class="col-md-8">
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title text-bold mb-0">
                            <i class="fas fa-hdd mr-1"></i> Historial de Copias de Seguridad de la Base de Datos
                        </h3>
                        <div class="ml-auto">
                            <form action="{{ route('admin.auditoria.backup.run') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm font-weight-bold shadow-sm">
                                    <i class="fas fa-download mr-1"></i> Generar Respaldo Ahora
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-hover table-striped mb-0 text-sm">
                            <thead class="bg-light">
                                <tr>
                                    <th>Fecha y Hora</th>
                                    <th>Nombre del Archivo</th>
                                    <th>Tamaño</th>
                                    <th>Estado Local</th>
                                    <th>Sincronización Google Drive</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($backups as $b)
                                    <tr>
                                        <td>
                                            <i class="far fa-calendar-alt text-muted mr-1"></i>{{ $b->created_at->format('d/m/Y H:i:s') }}
                                            <br><small class="text-muted">{{ $b->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td><code>{{ $b->filename }}</code></td>
                                        <td><span class="badge badge-secondary">{{ round($b->file_size / 1024 / 1024, 2) }} MB</span></td>
                                        <td>
                                            @if($b->status === 'success')
                                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>EXITOSO</span>
                                            @else
                                                <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>FALLIDO</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($b->gdrive_status === 'uploaded')
                                                <span class="badge badge-primary"><i class="fab fa-google-drive mr-1"></i>SUBIDO</span>
                                            @elseif($b->gdrive_status === 'not_configured')
                                                <span class="badge badge-secondary"><i class="fas fa-plug mr-1"></i>NO CONFIGURADO</span>
                                            @else
                                                <span class="badge badge-warning"><i class="fas fa-exclamation-triangle mr-1"></i>FALLÓ SUBIDA</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.auditoria.backup.download', $b->id) }}" class="btn btn-info btn-xs mr-1" title="Descargar .sql">
                                                <i class="fas fa-download"></i> Descargar
                                            </a>
                                            <form action="{{ route('admin.auditoria.backup.delete', $b->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar esta copia de seguridad?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                                            No se han generado copias de seguridad aún. Haz clic en "Generar Respaldo Ahora".
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Panel de Configuración Google Drive & Tareas Programadas -->
            <div class="col-md-4">
                <!-- Tareas Programadas -->
                <div class="card card-outline card-info shadow-sm mb-3">
                    <div class="card-header">
                        <h3 class="card-title text-bold"><i class="fas fa-clock text-info mr-1"></i> Respaldos Automáticos</h3>
                    </div>
                    <div class="card-body text-sm">
                        <p class="text-muted">El sistema tiene programado un respaldo automático periódico de la base de datos:</p>
                        <ul class="pl-3">
                            <li><strong>Frecuencia:</strong> Diaria (02:00 AM)</li>
                            <li><strong>Comando Artisan:</strong> <code>php artisan backup:database</code></li>
                            <li><strong>Almacenamiento:</strong> <code>storage/app/backups/</code></li>
                        </ul>
                        <div class="callout callout-info py-2 my-2">
                            <small><i class="fas fa-info-circle mr-1"></i> En el servidor de producción o Windows, asegúrate de tener configurado el planificador de tareas (Cron Task / Windows Task Scheduler) con <code>php artisan schedule:run</code>.</small>
                        </div>
                    </div>
                </div>

                <!-- Configuración Google Drive -->
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title text-bold"><i class="fab fa-google-drive text-primary mr-1"></i> Conexión Google Drive</h3>
                    </div>
                    <div class="card-body text-sm">
                        <p class="text-muted">Configura las credenciales OAuth2 para subir automáticamente copias a Google Drive:</p>
                        <form method="POST" action="{{ route('admin.auditoria.gdrive-config') }}">
                            @csrf
                            <div class="form-group mb-2">
                                <label for="client_id" class="text-xs mb-1">Google Client ID:</label>
                                <input type="text" name="client_id" id="client_id" class="form-control form-control-sm" value="{{ env('GOOGLE_DRIVE_CLIENT_ID') }}" placeholder="xxxxxx.apps.googleusercontent.com">
                            </div>
                            <div class="form-group mb-2">
                                <label for="client_secret" class="text-xs mb-1">Google Client Secret:</label>
                                <input type="password" name="client_secret" id="client_secret" class="form-control form-control-sm" value="{{ env('GOOGLE_DRIVE_CLIENT_SECRET') }}" placeholder="GOCS-xxxxxx">
                            </div>
                            <div class="form-group mb-2">
                                <label for="refresh_token" class="text-xs mb-1">Google Refresh Token:</label>
                                <input type="password" name="refresh_token" id="refresh_token" class="form-control form-control-sm" value="{{ env('GOOGLE_DRIVE_REFRESH_TOKEN') }}" placeholder="1//04xxxxx">
                            </div>
                            <div class="form-group mb-3">
                                <label for="folder_id" class="text-xs mb-1">ID de Carpeta Google Drive (Opcional):</label>
                                <input type="text" name="folder_id" id="folder_id" class="form-control form-control-sm" value="{{ env('GOOGLE_DRIVE_FOLDER_ID') }}" placeholder="1A2B3C4D5E...">
                            </div>

                            <button type="submit" class="btn btn-primary btn-block btn-sm font-weight-bold">
                                <i class="fas fa-save mr-1"></i> Guardar Credenciales
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
