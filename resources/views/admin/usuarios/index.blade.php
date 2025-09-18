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

                    <div class="card-tools">
                        <a href="{{ url('/admin/usuarios/create') }}" class="btn btn-primary">
                            <i class="bi bi-person-add"></i> Nuevo usuario
                        </a>
                    </div>
                    <!-- /.card-tools -->
                </div>

                <!-- /.card-header -->
                <div class="card-body" style="display: block;">
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
                                $contador = $contador+1;
                            @endphp    
                            <tr>
                                    <td>
                                        <center>{{ $contador }}</center>
                                    </td>
                                    <td>{{ $usuario->name }}</td>
                                    <td>{{ $usuario->email }}</td>
                                   <td style="text-align:center">
                                     <div class="btn-group" role="group" aria-label="Basic example">
                                        <a href="{{route('usuarios.show',$usuario->id)}}" type="button" class="btn btn-warning">            
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                        <a href="{{route('usuarios.edit',$usuario->id)}}" type="buton" class="btn btn-success">            
                                            <i class="bi bi-pencil-square">
                                                </i> Actualizar</a>
                                        <a href="" type="button" class="btn btn-danger">
                                            <i class="bi bi-trash"></i> Eliminar</a>
                                            
                                        
                                     </div>
                                    </td>
                                    <td>
                                        <center>
                                            <!-- Acciones aquí -->
                                        </center>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection
