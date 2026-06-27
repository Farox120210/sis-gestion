@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    
    {{-- ==========================================
         ENCABEZADO
         ========================================== --}}
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0" style="color: var(--unasam-navy); font-weight: 700;">
                <i class="fas fa-edit mr-2 text-muted"></i>Editar publicación
            </h2>
            <a href="{{ route('publicaciones.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 6px;">
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

    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('publicaciones.update', $pub->id_publicacion) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- ==========================================
                     TARJETA 1: DATOS DEL PROYECTO
                     ========================================== --}}
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header border-0" style="background-color: var(--unasam-navy); color: white;">
                        <h3 class="card-title mb-0 font-weight-bold" style="font-size: 1.05rem;">
                            <i class="fas fa-microscope mr-2 text-white-50"></i>Datos del proyecto
                        </h3>
                    </div>

                    <div class="card-body bg-white p-4">
                        <div class="row">
                            <div class="col-md-8 mb-4">
                                <label class="form-label text-dark font-weight-bold">Título <span class="text-danger">*</span></label>
                                <input type="text" name="titulo" class="form-control"
                                    value="{{ old('titulo', $pub->titulo) }}" maxlength="200" required>
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label text-dark font-weight-bold">Año del proyecto <span class="text-danger">*</span></label>
                                <input type="number" name="anio" class="form-control"
                                    value="{{ old('anio', $pub->anio_proyecto) }}" min="1900" max="{{ date('Y') + 1 }}" required>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label text-dark font-weight-bold">Fecha inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control"
                                    value="{{ old('fecha_inicio', $pub->fecha_inicio) }}">
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label text-dark font-weight-bold">Fecha fin</label>
                                <input type="date" name="fecha_fin" class="form-control"
                                    value="{{ old('fecha_fin', $pub->fecha_fin) }}">
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label text-dark font-weight-bold">Descripción / Resumen</label>
                                <textarea name="descripcion_proyecto" class="form-control" rows="3">{{ old('descripcion_proyecto', $pub->descripcion) }}</textarea>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label text-dark font-weight-bold">Palabras clave / Keywords</label>
                                <input type="text" name="keywords" id="keywords" class="form-control" value="{{ old('keywords', $pub->keywords) }}" placeholder="Ej: inteligencia artificial, educación, minería de datos (separadas por comas)">
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label text-dark font-weight-bold">Presupuesto (S/)</label>
                                <input type="number" step="0.01" name="presupuesto" class="form-control"
                                    value="{{ old('presupuesto', $pub->presupuesto) }}">
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label text-dark font-weight-bold">Área de investigación <span class="text-danger">*</span></label>
                                <select name="id_area" class="form-control" required>
                                    <option value="">— Seleccione —</option>
                                    @foreach ($areas as $a)
                                        <option value="{{ $a->ID_AREA }}"
                                            {{ (int) old('id_area', $pub->id_area) === (int) $a->ID_AREA ? 'selected' : '' }}>
                                            {{ $a->NOMBRE_AREA }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <label for="id_tipo" class="form-label text-dark font-weight-bold">Tipo de proyecto</label>
                                <select name="id_tipo" id="id_tipo" class="form-control">
                                    <option value="">— Sin tipo —</option>
                                    @foreach ($tipos as $t)
                                        <option value="{{ $t->ID_TIPO }}"
                                            {{ (int) old('id_tipo', $pub->id_tipo) === (int) $t->ID_TIPO ? 'selected' : '' }}>
                                            {{ $t->NOMBRE_TIPO }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label text-dark font-weight-bold">Estado del proyecto <span class="text-danger">*</span></label>
                                <select name="id_estado" class="form-control" required>
                                    <option value="">— Seleccione —</option>
                                    @foreach ($estados as $e)
                                        <option value="{{ $e->ID_ESTADO }}"
                                            {{ (int) old('id_estado', $pub->id_estado) === (int) $e->ID_ESTADO ? 'selected' : '' }}>
                                            {{ $e->NOMBRE_ESTADO }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-8 mb-4">
                                <label class="form-label text-dark font-weight-bold">Autor principal <span class="text-danger">*</span></label>
                                <div class="d-flex">
                                    <select name="id_autor" class="form-control js-select2" required>
                                        <option value="">— Seleccione —</option>
                                        @foreach ($investigadores as $i)
                                            <option value="{{ $i->ID_INVESTIGADOR }}"
                                                {{ (int) old('id_autor', $pub->id_autor) === (int) $i->ID_INVESTIGADOR ? 'selected' : '' }}>
                                                {{ $i->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="rol_autor" class="form-control ml-2 w-50" placeholder="Rol (Opcional)"
                                        value="{{ old('rol_autor', $pub->rol_autor) }}" maxlength="50">
                                </div>
                            </div>

                            <div class="col-md-8 mb-4">
                                <label class="form-label text-dark font-weight-bold">Coautores</label>
                                <select name="coautores[]" id="coautores" class="form-control select2-multiple" multiple data-placeholder="Buscar y añadir coautores...">
                                    @foreach ($investigadores as $i)
                                        <option value="{{ $i->ID_INVESTIGADOR }}"
                                            {{ in_array($i->ID_INVESTIGADOR, old('coautores', $coautorIds)) ? 'selected' : '' }}>
                                            {{ $i->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label text-dark font-weight-bold">Grupo de investigación</label>
                                <select name="id_grupo" class="form-control">
                                    <option value="">— Ninguno —</option>
                                    @foreach ($grupos as $g)
                                        <option value="{{ $g->ID_GRUPO }}"
                                            {{ (int) old('id_grupo', $pub->id_grupo) === (int) $g->ID_GRUPO ? 'selected' : '' }}>
                                            {{ $g->NOMBRE_GRUPO }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 mb-2">
                                <label for="pdf" class="text-dark font-weight-bold">Subir nuevo PDF (opcional)</label>
                                <div class="custom-file shadow-sm" style="border-radius: 6px;">
                                    <input type="file" name="pdf" id="pdf" class="custom-file-input" accept=".pdf">
                                    <label class="custom-file-label text-muted" for="pdf">Elegir archivo PDF...</label>
                                </div>
                                <div class="alert alert-light border mt-2 py-2 px-3 small text-muted">
                                    <i class="fas fa-info-circle text-info mr-1"></i> Este PDF se guardará como el documento final del proyecto. Si ya existe un PDF anterior, al subir uno nuevo <strong>el anterior se borrará permanentemente</strong>. Si no deseas cambiar el PDF actual, deja este campo vacío.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ==========================================
                     TARJETA 2: DATOS DE PUBLICACIÓN (REVISTA)
                     ========================================== --}}
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header border-0" style="background-color: #f8f9fa;">
                        <h3 class="card-title mb-0 font-weight-bold text-dark" style="font-size: 1.05rem;">
                            <i class="fas fa-book-open mr-2 text-muted"></i>Datos de publicación (revista)
                        </h3>
                    </div>
                    <div class="card-body bg-white p-4">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label text-dark font-weight-bold">Revista / Medio <span class="text-danger">*</span></label>
                                <select name="id_revista" class="form-control" required>
                                    <option value="">— Seleccione —</option>
                                    @foreach ($revistas as $r)
                                        <option value="{{ $r->ID_REVISTA }}"
                                            {{ (int) old('id_revista', $pub->id_revista) === (int) $r->ID_REVISTA ? 'selected' : '' }}>
                                            {{ $r->NOMBRE_REVISTA }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label text-dark font-weight-bold">Año de publicación <span class="text-danger">*</span></label>
                                <input type="number" name="anio_publicacion" class="form-control"
                                    value="{{ old('anio_publicacion', $pub->anio_publicacion) }}" min="1900"
                                    max="{{ date('Y') + 1 }}" required>
                            </div>
                        </div>
                    </div>
                    
                    {{-- BOTONES DE GUARDADO --}}
                    <div class="card-footer bg-light d-flex justify-content-end p-3 border-top-0">
                        <a href="{{ route('publicaciones.index') }}" class="btn btn-outline-secondary mr-3" style="border-radius: 6px;">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 font-weight-bold" style="border-radius: 6px; background-color: var(--esat-blue);">
                            <i class="bi bi-save2 mr-1"></i> Guardar cambios
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Inicializar Select2 para Coautores
        $('#coautores').select2({
            width: '100%',
            placeholder: "Buscar y añadir coautores...",
            allowClear: true,
            language: {
                noResults: function() {
                    return "No se encontraron investigadores";
                }
            }
        });

        // Actualizar nombre del archivo en el label de Bootstrap 4 custom-file
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName || 'Elegir archivo PDF...');
        });
    });
</script>
@endpush