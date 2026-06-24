@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    
    {{-- ==========================================
         ENCABEZADO
         ========================================== --}}
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0" style="color: var(--unasam-navy); font-weight: 700;">
                <i class="fas fa-user-plus mr-2 text-muted"></i>Nuevo investigador
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
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('investigadores.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf

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
                                <input type="text" id="nombres" name="nombres" value="{{ old('nombres') }}" 
                                       class="form-control @error('nombres') is-invalid @enderror" required maxlength="100">
                                @error('nombres') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="form-group col-md-6 mb-3">
                                <label for="apellidos" class="text-dark font-weight-bold">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" id="apellidos" name="apellidos" value="{{ old('apellidos') }}" 
                                       class="form-control @error('apellidos') is-invalid @enderror" required maxlength="100">
                                @error('apellidos') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-5 mb-3">
                                <label for="tipo_doc" class="text-dark font-weight-bold">Tipo de documento <span class="text-danger">*</span></label>
                                <select id="tipo_doc" name="tipo_doc" class="form-control @error('tipo_doc') is-invalid @enderror" required>
                                    <option value="">— Seleccione —</option>
                                    <option value="DNI" {{ old('tipo_doc') === 'DNI' ? 'selected' : '' }}>DNI</option>
                                    <option value="CE"  {{ old('tipo_doc') === 'CE'  ? 'selected' : '' }}>Carnet de Extranjería</option>
                                    <option value="PAS" {{ old('tipo_doc') === 'PAS' ? 'selected' : '' }}>Pasaporte</option>
                                </select>
                                @error('tipo_doc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group col-md-7 mb-3">
                                <label for="num_doc" class="text-dark font-weight-bold">N° documento <span class="text-danger">*</span></label>
                                <input type="text" id="num_doc" name="num_doc" value="{{ old('num_doc') }}" 
                                       class="form-control @error('num_doc') is-invalid @enderror" required maxlength="20">
                                @error('num_doc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 mb-3">
                                <label for="correo_personal" class="text-dark font-weight-bold">Correo electrónico <span class="text-danger">*</span></label>
                                <input type="email" id="correo_personal" name="correo_personal" value="{{ old('correo_personal') }}" 
                                       class="form-control @error('correo_personal') is-invalid @enderror" required maxlength="150" placeholder="ejemplo@unasam.edu.pe">
                                @error('correo_personal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-group col-md-6 mb-3">
                                <label for="telefono" class="text-dark font-weight-bold">Teléfono</label>
                                <input type="text" id="telefono" name="telefono" value="{{ old('telefono') }}" 
                                       class="form-control @error('telefono') is-invalid @enderror" maxlength="30">
                                @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4 mb-3">
                                <label for="fecha_nac" class="text-dark font-weight-bold">Fecha de nacimiento</label>
                                <input type="date" id="fecha_nac" name="fecha_nac" value="{{ old('fecha_nac') }}" 
                                       class="form-control @error('fecha_nac') is-invalid @enderror">
                                @error('fecha_nac') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="form-group col-md-8 mb-3">
                                <label for="direccion" class="text-dark font-weight-bold">Dirección</label>
                                <input type="text" id="direccion" name="direccion" value="{{ old('direccion') }}" 
                                       class="form-control @error('direccion') is-invalid @enderror" maxlength="200">
                                @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                            <label for="cod_orcid" class="text-dark font-weight-bold">ORCID</label>
                            <input type="text" id="cod_orcid" name="cod_orcid" value="{{ old('cod_orcid') }}" 
                                   class="form-control @error('cod_orcid') is-invalid @enderror" 
                                   placeholder="0000-0000-0000-0000" maxlength="19">
                            @error('cod_orcid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle mr-1"></i>Formato: 0000-0000-0000-0000</small>
                        </div>

                        <div class="form-group mb-4">
                            <label for="grado_academico" class="text-dark font-weight-bold">Grado académico <span class="text-danger">*</span></label>
                            <select id="grado_academico" name="grado_academico" class="form-control @error('grado_academico') is-invalid @enderror">
                                <option value="">— Seleccione —</option>
                                <option value="Estudiante universitario" {{ old('grado_academico') === 'Estudiante universitario' ? 'selected' : '' }}>Estudiante universitario</option>
                                <option value="Bachiller" {{ old('grado_academico') === 'Bachiller' ? 'selected' : '' }}>Bachiller</option>
                                <option value="Titulo universitario" {{ old('grado_academico') === 'Titulo universitario' ? 'selected' : '' }}>Título universitario</option>
                                <option value="Maestría" {{ old('grado_academico') === 'Maestría' ? 'selected' : '' }}>Maestría</option>
                                <option value="Doctorado" {{ old('grado_academico') === 'Doctorado' ? 'selected' : '' }}>Doctorado</option>
                                <option value="Postdoctorado" {{ old('grado_academico') === 'Postdoctorado' ? 'selected' : '' }}>Postdoctorado</option>
                            </select>
                            @error('grado_academico') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="tipo_investigador" class="text-dark font-weight-bold">Tipo de Investigador <span class="text-danger">*</span></label>
                            <input type="text" id="tipo_investigador" name="tipo_investigador" value="{{ old('tipo_investigador') }}" 
                                   class="form-control @error('tipo_investigador') is-invalid @enderror" 
                                   placeholder="Ej: TESISTA PRE, INVESTIGADOR FORMATIVO..." required>
                            @error('tipo_investigador') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="id_area" class="text-dark font-weight-bold">Área de investigación <span class="text-danger">*</span></label>
                            <select id="id_area" name="id_area" class="form-control @error('id_area') is-invalid @enderror" required>
                                <option value="">— Seleccione —</option>
                                @foreach ($areas as $a)
                                    <option value="{{ $a->ID_AREA }}" {{ old('id_area') == $a->ID_AREA ? 'selected' : '' }}>
                                        {{ $a->NOMBRE_AREA }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_area') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="id_grupo" class="text-dark font-weight-bold">Grupo de investigación</label>
                            <select id="id_grupo" name="id_grupo" class="form-control @error('id_grupo') is-invalid @enderror">
                                <option value="">— Ninguno —</option>
                                @foreach ($grupos as $g)
                                    <option value="{{ $g->ID_GRUPO }}" {{ old('id_grupo') == $g->ID_GRUPO ? 'selected' : '' }}>
                                        {{ $g->NOMBRE_GRUPO }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_grupo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-7 mb-0">
                                <label for="institucion" class="text-dark font-weight-bold">Institución</label>
                                <input type="text" id="institucion" name="institucion" value="{{ old('institucion') }}" 
                                       class="form-control @error('institucion') is-invalid @enderror" maxlength="150" placeholder="Ej. UNASAM">
                                @error('institucion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-5 mb-0">
                                <label for="activo" class="text-dark font-weight-bold">Estado en sistema</label>
                                <select id="activo" name="activo" class="form-control @error('activo') is-invalid @enderror">
                                    <option value="1" {{ old('activo', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('activo') === '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                                @error('activo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
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
                    <i class="bi bi-save2 mr-1"></i> Guardar Investigador
                </button>
            </div>
        </div>

    </form>
</div>

{{-- Helpers UX --}}
<script>
    // Quitar espacios al final en nombres/apellidos
    document.getElementById('nombres')?.addEventListener('blur', e => {
        e.target.value = e.target.value.trim();
    });
    document.getElementById('apellidos')?.addEventListener('blur', e => {
        e.target.value = e.target.value.trim();
    });

    // Formato sencillo de ORCID: 0000-0000-0000-0000
    document.getElementById('cod_orcid')?.addEventListener('input', function () {
        let v = this.value.replace(/[^0-9Xx]/g, '').toUpperCase().substr(0, 16);
        v = v.replace(
            /^(.{4})(.{0,4})(.{0,4})(.{0,4}).*$/,
            (m, a, b, c, d) => [a, b, c, d].filter(Boolean).join('-')
        );
        this.value = v;
    });
</script>
@endsection