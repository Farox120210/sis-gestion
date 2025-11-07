@extends('layouts.admin')

@section('content')
    <div class="row">
        <h1 class="h3 mb-0">Nuevo investigador</h1>
    </div>
    <hr>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Revisa los campos:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('investigadores.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf

        <div class="row">
            {{-- Datos de la persona --}}
            <div class="col-md-7">
                <div class="card card-outline card-primary mb-3">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Datos personales</h3>
                    </div>
                    <div class="card-body">

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="nombres">Nombres *</label>
                                <input type="text" id="nombres" name="nombres" value="{{ old('nombres') }}"
                                    class="form-control @error('nombres') is-invalid @enderror" required maxlength="100">
                                @error('nombres')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="apellidos">Apellidos *</label>
                                <input type="text" id="apellidos" name="apellidos" value="{{ old('apellidos') }}"
                                    class="form-control @error('apellidos') is-invalid @enderror" required maxlength="100">
                                @error('apellidos')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="tipo_doc">Tipo de documento *</label>
                                <select id="tipo_doc" name="tipo_doc"
                                    class="form-control @error('tipo_doc') is-invalid @enderror" required>
                                    <option value="">— Seleccione —</option>
                                    <option value="DNI" {{ old('tipo_doc') === 'DNI' ? 'selected' : '' }}>DNI</option>
                                    <option value="CE" {{ old('tipo_doc') === 'CE' ? 'selected' : '' }}>Carnet de
                                        Extranjería</option>
                                    <option value="PAS" {{ old('tipo_doc') === 'PAS' ? 'selected' : '' }}>Pasaporte</option>
                                </select>
                                @error('tipo_doc')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="num_doc">N° documento *</label>
                                <input type="text" id="num_doc" name="num_doc" value="{{ old('num_doc') }}"
                                    class="form-control @error('num_doc') is-invalid @enderror" required maxlength="20">
                                @error('num_doc')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="sexo">Sexo</label>
                                <select id="sexo" name="sexo"
                                    class="form-control @error('sexo') is-invalid @enderror">
                                    <option value="">—</option>
                                    <option value="M" {{ old('sexo') === 'M' ? 'selected' : '' }}>Masculino</option>
                                    <option value="F" {{ old('sexo') === 'F' ? 'selected' : '' }}>Femenino</option>
                                </select>
                                @error('sexo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="email">Correo *</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                    class="form-control @error('email') is-invalid @enderror" required maxlength="150">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="telefono">Teléfono</label>
                                <input type="text" id="telefono" name="telefono" value="{{ old('telefono') }}"
                                    class="form-control @error('telefono') is-invalid @enderror" maxlength="30">
                                @error('telefono')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="fecha_nac">Fecha de nacimiento</label>
                                <input type="date" id="fecha_nac" name="fecha_nac" value="{{ old('fecha_nac') }}"
                                    class="form-control @error('fecha_nac') is-invalid @enderror">
                                @error('fecha_nac')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="direccion">Dirección</label>
                                <input type="text" id="direccion" name="direccion" value="{{ old('direccion') }}"
                                    class="form-control @error('direccion') is-invalid @enderror" maxlength="200">
                                @error('direccion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Datos del investigador --}}
            <div class="col-md-5">
                <div class="card card-outline card-info mb-3">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Perfil de investigación</h3>
                    </div>
                    <div class="card-body">

                        <div class="form-group">
                            <label for="orcid">ORCID</label>
                            <input type="text" id="orcid" name="orcid" value="{{ old('orcid') }}"
                                class="form-control @error('orcid') is-invalid @enderror"
                                placeholder="0000-0000-0000-0000" maxlength="19">
                            @error('orcid')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Formato: 0000-0000-0000-0000</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="grado">Grado académico</label>
                                <select id="grado" name="grado"
                                    class="form-control @error('grado') is-invalid @enderror">
                                    <option value="">—</option>
                                    <option value="Bachiller" {{ old('grado') === 'Bachiller' ? 'selected' : '' }}>Bachiller
                                    </option>
                                    <option value="Licenciatura"{{ old('grado') === 'Licenciatura' ? 'selected' : '' }}>
                                        Licenciatura</option>
                                    <option value="Maestría" {{ old('grado') === 'Maestría' ? 'selected' : '' }}>Maestría
                                    </option>
                                    <option value="Doctorado" {{ old('grado') === 'Doctorado' ? 'selected' : '' }}>Doctorado
                                    </option>
                                </select>
                                @error('grado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="tipo_investigador">Tipo de Investigador *</label>
                                <input type="text" id="tipo_investigador" name="tipo_investigador"
                                    value="{{ old('tipo_investigador') }}"
                                    class="form-control @error('tipo_investigador') is-invalid @enderror"
                                    placeholder="Ej: TESISTA PRE, TESISTA POST, INVESTIGADOR FORMATIVO, VOLUNTARIO ..." required>
                                @error('tipo_investigador')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="categoria">Categoría</label>
                                <select id="categoria" name="categoria"
                                    class="form-control @error('categoria') is-invalid @enderror">
                                    <option value="">—</option>
                                    <option value="Principal" {{ old('categoria') === 'Principal' ? 'selected' : '' }}>
                                        Principal</option>
                                    <option value="Asociado" {{ old('categoria') === 'Asociado' ? 'selected' : '' }}>Asociado
                                    </option>
                                    <option value="Auxiliar" {{ old('categoria') === 'Auxiliar' ? 'selected' : '' }}>Auxiliar
                                    </option>
                                    <option value="Invitado" {{ old('categoria') === 'Invitado' ? 'selected' : '' }}>Invitado
                                    </option>
                                </select>
                                @error('categoria')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="id_area">Área de investigación *</label>
                            <select id="id_area" name="id_area"
                                class="form-control @error('id_area') is-invalid @enderror" required>
                                <option value="">— Seleccione —</option>
                                @foreach ($areas as $a)
                                    <option value="{{ $a->ID_AREA }}"
                                        {{ old('id_area') == $a->ID_AREA ? 'selected' : '' }}>
                                        {{ $a->NOMBRE_AREA }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_area')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="id_grupo">Grupo de investigación</label>
                            <select id="id_grupo" name="id_grupo"
                                class="form-control @error('id_grupo') is-invalid @enderror">
                                <option value="">— Ninguno —</option>
                                @foreach ($grupos as $g)
                                    <option value="{{ $g->ID_GRUPO }}"
                                        {{ old('id_grupo') == $g->ID_GRUPO ? 'selected' : '' }}>
                                        {{ $g->NOMBRE_GRUPO }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_grupo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="institucion">Institución</label>
                                <input type="text" id="institucion" name="institucion"
                                    value="{{ old('institucion') }}"
                                    class="form-control @error('institucion') is-invalid @enderror" maxlength="150">
                                @error('institucion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="activo">Estado</label>
                                <select id="activo" name="activo"
                                    class="form-control @error('activo') is-invalid @enderror">
                                    <option value="1" {{ old('activo', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('activo') === '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                                @error('activo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cv_pdf">CV (PDF) — opcional</label>
                            <input type="file" id="cv_pdf" name="cv_pdf" accept="application/pdf"
                                class="form-control-file @error('cv_pdf') is-invalid @enderror">
                            @error('cv_pdf')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 d-flex justify-content-end">
                <a href="{{ route('investigadores.index') }}" class="btn btn-outline-secondary mr-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save2"></i> Guardar
                </button>
            </div>
        </div>

    </form>

    {{-- (Opcional) Pequeños helpers UX --}}
    <script>
        // Fuerza mayúsculas en nombres/apellidos (opcional)
        document.getElementById('nombres')?.addEventListener('blur', e => e.target.value = e.target.value.trim());
        document.getElementById('apellidos')?.addEventListener('blur', e => e.target.value = e.target.value.trim());

        // Formato sencillo de ORCID: 0000-0000-0000-0000
        document.getElementById('orcid')?.addEventListener('input', function() {
            let v = this.value.replace(/[^0-9Xx]/g, '').toUpperCase().substr(0, 16);
            v = v.replace(/^(.{4})(.{0,4})(.{0,4})(.{0,4}).*$/, (m, a, b, c, d) => [a, b, c, d].filter(Boolean)
                .join('-'));
            this.value = v;
        });
    </script>
@endsection
