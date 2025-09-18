@extends('layouts.admin')

@section('content')
<div class="row">
    <h1>Datos del usuario</h1>
</div>
<hr>
     <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-info">
              <div class="card-header">
                <h3 class="card-title">Datos del usuario</h3>
                <div class="card-tools">
                </div>
                <!-- /.card-tools -->
              </div>
              <!-- /.card-header -->
                <div class="card-body" style="display: block;">  
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">Nombre del usuario</label>
                                   <p>{{$usuario->name}} </p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">Correo</label>
                                    <p>{{$usuario->email}} </p>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        <div class="col-md-12">
                               <a href="{{url('admin/usuarios')}}" class="btn btn-secondary" >Volver </a>
                            </div>
                        
                </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
    </div>
@endsection