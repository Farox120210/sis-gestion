@extends('layouts.admin')

@section('content')
    <div class="row">
        <h1>Nuevo usuario</h1>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Datos registrados</h3>
                    <div class="card-tools">
                    </div>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body" style="display: block;">
                    <form action="{{ url('/admin/usuarios') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">Nombre del usuario</label>
                                    <input type="text" value="{{ old('name') }}" name="name"
                                        class="form-control"required>
                                    @error('name')
                                        <small style="color: red">{{ $message }} </small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">Correo</label>
                                    <input type="email" value="{{ old('email') }}" name="email"
                                        class="form-control"required>
                                    @error('email')
                                        <small style="color: red">{{ $message }} </small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="rol">Rol del Usuario *</label>
                                <select name="rol" id="rol" class="form-control" required>
                                    <option value="espectador"
                                        {{ isset($usuario) && $usuario->rol == 'espectador' ? 'selected' : '' }}>
                                        Espectador (Solo lectura)</option>
                                    <option value="publicador"
                                        {{ isset($usuario) && $usuario->rol == 'publicador' ? 'selected' : '' }}>
                                        Publicador (Crear y editar)</option>
                                    <option value="super_admin"
                                        {{ isset($usuario) && $usuario->rol == 'super_admin' ? 'selected' : '' }}>Súper
                                        Administrador (Acceso total)</option>
                                </select>
                                <small class="text-muted">Define los permisos que tendrá este usuario en la
                                    plataforma.</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">Contraseña</label>
                                    <input type="password" value="{{ old('password') }}" name="password"
                                        class="form-control"required>
                                    @error('password')
                                        <small style="color: red">{{ $message }} </small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">Repita la contraseña</label>
                                    <input type="password" name="password_confirmation" class="form-control">

                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary"> <i class="bi bi-floppy"></i>Guardar</button>
                            <a href="{{ url('admin/usuarios') }}" class="btn btn-secondary">Cancelar </a>
                        </div>

                    </form>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection
