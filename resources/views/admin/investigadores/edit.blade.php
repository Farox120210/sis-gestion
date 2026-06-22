@extends('layouts.admin')

@section('content')
    <div class="row mb-3">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <h1 class="h3">Editar Investigador</h1>
            <a href="{{ route('investigadores.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    {{-- Errores de validación --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulario --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Actualizar datos</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('investigadores.update', $inv->ID_INVESTIGADOR) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    {{-- Datos personales --}}
                    <div class="col-md-6 mb-3">
                        <label for="nombres" class="form-label">Nombres</label>
                        <input type="text" name="nombres" id="nombres" class="form-control"
                            value="{{ old('nombres', $inv->NOMBRES) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="apellidos" class="form-label">Apellidos</label>
                        <input type="text" name="apellidos" id="apellidos" class="form-control"
                            value="{{ old('apellidos', $inv->APELLIDOS) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input type="text" name="direccion" id="direccion" class="form-control"
                            value="{{ old('direccion', $inv->DIRECCION) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" name="telefono" id="telefono" class="form-control"
                            value="{{ old('telefono', $inv->TELEFONO) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="correo_personal" class="form-label">Correo personal</label>
                        <input type="email" name="correo_personal" id="correo_personal" class="form-control"
                            value="{{ old('correo_personal', $inv->CORREO_PERSONAL) }}">
                    </div>

                    {{-- DATOS NUEVOS DEL INVESTIGADOR --}}
                    <div class="col-md-4 mb-3">
                        <label for="cod_orcid" class="form-label">Código ORCID</label>
                        <input type="text" name="cod_orcid" id="cod_orcid" class="form-control"
                            value="{{ old('cod_orcid', $inv->cod_orcid) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="grado_academico" class="form-label">Grado académico</label>
                        <input type="text" name="grado_academico" id="grado_academico" class="form-control"
                            value="{{ old('grado_academico', $inv->grado_academico) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="institucion" class="form-label">Institución</label>
                        <input type="text" name="institucion" id="institucion" class="form-control"
                            value="{{ old('institucion', $inv->institucion) }}">
                    </div>

                    {{-- Datos de investigador --}}
                    <div class="col-md-6 mb-3">
                        <label for="tipo_investigador" class="form-label">Tipo de Investigador</label>
                        <input type="text" name="tipo_investigador" id="tipo_investigador" class="form-control"
                            value="{{ old('tipo_investigador', $inv->TIPO_INVESTIGADOR) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="correo_institucional" class="form-label">Correo institucional</label>
                        <input type="email" name="correo_institucional" id="correo_institucional" class="form-control"
                            value="{{ old('correo_institucional', $inv->CORREO_INSTITUCIONAL) }}">
                    </div>

                    {{-- Grupos de investigación --}}
                    <div class="col-md-12 mb-3">
                        <label for="grupos" class="form-label">Grupos de investigación</label>
                        <select name="grupos[]" id="grupos" class="form-select" multiple>
                            @foreach ($grupos as $g)
                                <option value="{{ $g->ID_GRUPO }}"
                                    {{ in_array($g->ID_GRUPO, old('grupos', $gruposActuales)) ? 'selected' : '' }}>
                                    {{ $g->NOMBRE_GRUPO }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Mantén pulsado Ctrl o Cmd para seleccionar varios.</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
