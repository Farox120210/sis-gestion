@extends('layouts.admin')

@section('content')
    <div class="row">
        <h1>Actualización de datos de usuario</h1>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Actualizar datosc registrados</h3>
                    <div class="card-tools">
                    </div>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body" style="display: block;">
                    <form action="{{ url('/admin/usuarios', $usuario->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">Nombre del usuario</label>
                                    <input type="text" value="{{ $usuario->name }}" name="name"
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
                                    <input type="email" value="{{ $usuario->email }}" name="email"
                                        class="form-control"required>
                                    @error('email')
                                        <small style="color: red">{{ $message }} </small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">Contraseña</label>
                                    <input type="password" name="password" class="form-control"> {{-- sin required --}}
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
                            <button type="submit" class="btn btn-success"><i class="bi bi-pencil"></i></i>Editar</button>
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
