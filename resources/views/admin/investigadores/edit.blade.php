@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    
    {{-- ==========================================
         ENCABEZADO
         ========================================== --}}
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0" style="color: var(--unasam-navy); font-weight: 700;">
                <i class="fas fa-user-edit mr-2 text-muted"></i>Editar Investigador
            </h2>
            <a href="{{ route('investigadores.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 6px;">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    {{-- ==========================================
         ALERTAS DE VALIDACIÓN
         ========================================== --}}
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm mb-4" style="border-radius: 8px;">
            <strong><i class="fas fa-exclamation-triangle mr-2"></i>Revisa los siguientes campos:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('investigadores.update', $inv->ID_INVESTIGADOR) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- ==========================================
                 COLUMNA IZQUIERDA: DATOS DE LA PERSONA
                 ========================================== --}}
            <div class="col-md-7">
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header border-0" style="background-color: var(--unasam-navy); color: white;">
                        <h3 class="card-title mb-0 font-weight-bold" style="font-size: 1.05rem;">
                            <i class="fas fa-id-card mr-2 text-white-50"></i>Datos personales
                        </h3>
                    </div>
                    
                    <div class="card-body bg-white p-4">
                        <div class="form-row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="nombres" class="text-dark font-weight-bold">Nombres <span class="text-danger">*</span></label>
                                <input type="text" name="nombres" id="nombres" class="form-control @error('nombres') is-invalid @enderror"
                                    value="{{ old('nombres', $inv->NOMBRES) }}" required>
                            </div>

                            <div class="form-group col-md-6 mb-3">
                                <label for="apellidos" class="text-dark font-weight-bold">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" name="apellidos" id="apellidos" class="form-control @error('apellidos') is-invalid @enderror"
                                    value="{{ old('apellidos', $inv->APELLIDOS) }}" required>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="direccion" class="text-dark font-weight-bold">Dirección</label>
                            <input type="text" name="direccion" id="direccion" class="form-control"
                                value="{{ old('direccion', $inv->DIRECCION) }}">
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="telefono" class="text-dark font-weight-bold">Teléfono</label>
                                <input type="text" name="telefono" id="telefono" class="form-control"
                                    value="{{ old('telefono', $inv->TELEFONO) }}">
                            </div>

                            <div class="form-group col-md-6 mb-3">
                                <label for="correo_personal" class="text-dark font-weight-bold">Correo personal</label>
                                <input type="email" name="correo_personal" id="correo_personal" class="form-control"
                                    value="{{ old('correo_personal', $inv->CORREO_PERSONAL) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ==========================================
                 COLUMNA DERECHA: DATOS DEL INVESTIGADOR
                 ========================================== --}}
            <div class="col-md-5">
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header border-0" style="background-color: var(--unasam-navy); color: white;">
                        <h3 class="card-title mb-0 font-weight-bold" style="font-size: 1.05rem;">
                            <i class="fas fa-microscope mr-2 text-white-50"></i>Perfil de investigación
                        </h3>
                    </div>
                    
                    <div class="card-body bg-white p-4">
                        <div class="form-group mb-4">
                            <label for="cod_orcid" class="text-dark font-weight-bold">Código ORCID</label>
                            <input type="text" name="cod_orcid" id="cod_orcid" class="form-control"
                                value="{{ old('cod_orcid', $inv->cod_orcid) }}" placeholder="0000-0000-0000-0000">
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 mb-4">
                                <label for="grado_academico" class="text-dark font-weight-bold">Grado académico</label>
                                <input type="text" name="grado_academico" id="grado_academico" class="form-control"
                                    value="{{ old('grado_academico', $inv->grado_academico) }}">
                            </div>

                            <div class="form-group col-md-6 mb-4">
                                <label for="institucion" class="text-dark font-weight-bold">Institución</label>
                                <input type="text" name="institucion" id="institucion" class="form-control"
                                    value="{{ old('institucion', $inv->institucion) }}">
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="tipo_investigador" class="text-dark font-weight-bold">Tipo de Investigador <span class="text-danger">*</span></label>
                            <input type="text" name="tipo_investigador" id="tipo_investigador" class="form-control"
                                value="{{ old('tipo_investigador', $inv->TIPO_INVESTIGADOR) }}" required>
                        </div>

                        <div class="form-group mb-4">
                            <label for="correo_institucional" class="text-dark font-weight-bold">Correo institucional</label>
                            <input type="email" name="correo_institucional" id="correo_institucional" class="form-control"
                                value="{{ old('correo_institucional', $inv->CORREO_INSTITUCIONAL) }}">
                        </div>

                        <div class="form-group mb-2">
                            <label for="grupos" class="text-dark font-weight-bold">Grupos de investigación</label>
                            <select name="grupos[]" id="grupos" class="form-control select2-multiple" multiple data-placeholder="Buscar grupos...">
                                @foreach ($grupos as $g)
                                    <option value="{{ $g->ID_GRUPO }}"
                                        {{ in_array($g->ID_GRUPO, old('grupos', $gruposActuales)) ? 'selected' : '' }}>
                                        {{ $g->NOMBRE_GRUPO }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==========================================
             BOTONES DE ACCIÓN
             ========================================== --}}
        <div class="row">
            <div class="col-12 d-flex justify-content-end mb-4">
                <a href="{{ route('investigadores.index') }}" class="btn btn-outline-secondary mr-3" style="border-radius: 6px;">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary px-4 font-weight-bold" style="border-radius: 6px; background-color: var(--esat-blue);">
                    <i class="bi bi-save2 mr-1"></i> Guardar cambios
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Inicializar Select2 para los Grupos de Investigación
        $('#grupos').select2({
            width: '100%',
            placeholder: "Buscar y seleccionar grupos...",
            allowClear: true
        });

        // Formato automático de ORCID
        document.getElementById('cod_orcid')?.addEventListener('input', function () {
            let v = this.value.replace(/[^0-9Xx]/g, '').toUpperCase().substr(0, 16);
            v = v.replace(
                /^(.{4})(.{0,4})(.{0,4})(.{0,4}).*$/,
                (m, a, b, c, d) => [a, b, c, d].filter(Boolean).join('-')
            );
            this.value = v;
        });
    });
</script>
@endpush