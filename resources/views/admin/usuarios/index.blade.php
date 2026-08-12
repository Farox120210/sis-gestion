@extends('layouts.admin')
@section('content')
    <div class="row">
        <div class="h1">Panel Usuarios</div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Datos registrados</h3>

                    @can('gestionar-usuarios')
                        <div class="card-tools">
                            <a href="{{ url('/admin/usuarios/create') }}" class="btn btn-primary">
                                <i class="bi bi-person-add"></i> Nuevo usuario
                            </a>
                        </div>
                    @endcan
                    </div>

                <div class="card-body" style="display: block;">
                    {{-- Alertas de sesión --}}
                    @if (session('ok'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle mr-1"></i> {{ session('ok') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <table class="table table-bordered table-sm table-striped table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <center>Nro.</center>
                                </th>
                                <th>
                                    <center>Nombre</center>
                                </th>
                                <th>
                                    <center>Correo</center>
                                </th>
                                <th>
                                    <center>Estado</center>
                                </th>
                                <th>
                                    <center>Acciones</center>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $contador = 0;
                            @endphp
                            @foreach ($usuarios as $usuario)
                                @php
                                    $contador = $contador + 1;
                                @endphp
                                <tr>
                                    <td>
                                        <center>{{ $contador }}</center>
                                    </td>
                                    <td>{{ $usuario->name }}</td>
                                    <td>{{ $usuario->email }}</td>
                                    <td style="text-align:center; vertical-align: middle;">
                                        @if($usuario->status === 'active')
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Activo</span>
                                        @elseif($usuario->activation_token)
                                            <span class="badge badge-warning px-2 py-1 text-dark"><i class="fas fa-clock mr-1"></i>Pendiente</span>
                                        @else
                                            <span class="badge badge-danger px-2 py-1"><i class="fas fa-ban mr-1"></i>Inactivo</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center">
                                        <div class="btn-group" role="group" aria-label="Basic example">
                                            
                                            <a href="{{ route('usuarios.show', $usuario->id) }}" type="button" class="btn btn-warning" title="Ver detalles">
                                                <i class="bi bi-eye"></i> Ver
                                            </a>
                                            
                                            @can('gestionar-usuarios')
                                                <a href="{{ route('usuarios.edit', $usuario->id) }}" type="button" class="btn btn-success" title="Editar">
                                                    <i class="bi bi-pencil-square"></i> Actualizar
                                                </a>
                                                
                                                @if($usuario->status === 'active')
                                                    <form action="{{ route('usuarios.toggle-status', $usuario->id) }}" method="POST" style="display:inline-block;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-secondary" title="Dar de baja / Desactivar" onclick="return confirm('¿Seguro que deseas dar de baja a este usuario?')" @if(auth()->id() == $usuario->id) disabled @endif>
                                                            <i class="bi bi-person-x-fill"></i> Dar de baja
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('usuarios.toggle-status', $usuario->id) }}" method="POST" style="display:inline-block;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-info text-white" title="Activar cuenta" onclick="return confirm('¿Seguro que deseas activar a este usuario?')">
                                                            <i class="bi bi-person-check-fill"></i> Activar
                                                        </button>
                                                    </form>
                                                    
                                                    @if($usuario->activation_token)
                                                        <form action="{{ route('usuarios.resend-invitation', $usuario->id) }}" method="POST" style="display:inline-block;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-primary" title="Reenviar correo de activación" onclick="return confirm('¿Reenviar invitación al correo {{ $usuario->email }}?')">
                                                                <i class="bi bi-envelope-fill"></i> Reenviar
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif

                                                <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este usuario?')" title="Eliminar">
                                                        <i class="bi bi-trash"></i> Eliminar
                                                    </button>
                                                </form>
                                            @endcan

                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                </div>
            </div>
    </div>
@endsection