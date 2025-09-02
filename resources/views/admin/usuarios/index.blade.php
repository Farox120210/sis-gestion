@extends('layouts.admin')
@section('content')
    <div class="row">
        <div class="h1">Investigador</div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Datos registrados</h3>

                    <div class="card-tools">
                        <a href="{{url('/admin/usuarios/create')}}" class="btn btn-primary">
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
                                <th><center>Nro.</center></th>
                                <th><center>Nombre</center></th>
                                <th><center>Correo</center></th>
                                <th><center>Acciones</center></th>
                            </tr>
                        </thead> 
                        <tbody>
                            @foreach($usuarios as $usuario)
                                <tr>
                                    <td><center>{{$usuario->id}}</center></td>
                                    <td>{{$usuario->name}}</td>
                                    <td>{{$usuario->email}}</td>
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