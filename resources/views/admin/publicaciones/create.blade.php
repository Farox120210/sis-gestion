@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0" style="color: var(--unasam-navy); font-weight: 700;">
                <i class="fas fa-plus-circle mr-2 text-muted"></i>Nueva publicación
            </h2>
            <a href="{{ route('publicaciones.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 6px;">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm" style="border-radius: 8px;">
            <strong><i class="fas fa-exclamation-triangle mr-2"></i>Por favor, revisa los siguientes campos:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('publicaciones.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- ==========================================
                     SECCIÓN: ASISTENTE IA (Estilo Institucional)
                     ========================================== --}}
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header border-0" style="background: linear-gradient(135deg, var(--unasam-navy) 0%, var(--esat-blue) 100%);">
                        <h3 class="card-title mb-0 text-white font-weight-bold" style="font-size: 1.1rem;">
                            <i class="fas fa-robot mr-2 text-white"></i> Asistente Inteligente (Opcional)
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body bg-light">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info border-0 shadow-sm mb-4" style="background-color: rgba(42, 123, 202, 0.1); color: var(--unasam-navy); border-left: 4px solid var(--esat-blue) !important;">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <strong>¿Tienes el documento PDF?</strong> Súbelo y deja que la IA extraiga automáticamente la información. Luego podrás revisar y editar los datos antes de guardar.
                                </div>
                            </div>

                            <div class="col-md-8 mb-3">
                                <label class="form-label font-weight-bold text-dark">
                                    <i class="fas fa-file-pdf text-danger mr-1"></i> Selecciona tu PDF
                                </label>
                                <div class="custom-file shadow-sm" style="border-radius: 6px;">
                                    <input type="file" class="custom-file-input" id="aiPdfFile" accept="application/pdf">
                                    <label class="custom-file-label text-muted" for="aiPdfFile">Elegir archivo PDF...</label>
                                </div>
                                <small class="form-text text-muted mt-2"><i class="fas fa-shield-alt mr-1"></i>Máximo 25 MB • Solo archivos PDF</small>
                            </div>

                            <div class="col-md-4 mb-3 d-flex align-items-center mt-md-4">
                                <button type="button" id="fillWithAI" class="btn btn-block btn-lg shadow-sm font-weight-bold"
                                    style="background-color: var(--esat-blue); color: white; border-radius: 8px; transition: 0.3s;"
                                    disabled>
                                    <i class="fas fa-magic mr-2" id="aiButtonIcon"></i>
                                    <span id="aiButtonText">Extraer con IA</span>
                                </button>
                            </div>

                            {{-- Área de Issues/Advertencias --}}
                            <div class="col-md-12">
                                <div id="aiIssuesBox" class="alert alert-warning shadow-sm border-0" style="display: none; border-left: 4px solid #F59E0B !important;">
                                    <h6 class="alert-heading font-weight-bold mb-2">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> Revisa estos detalles:
                                    </h6>
                                    <ul id="aiIssuesList" class="mb-0 small"></ul>
                                </div>
                            </div>

                            {{-- Barra de progreso --}}
                            <div class="col-md-12">
                                <div id="aiProgressBar" class="progress shadow-sm" style="display: none; height: 25px; border-radius: 12px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                         style="width: 100%; background: linear-gradient(90deg, var(--esat-blue) 0%, var(--unasam-navy) 100%);">
                                        <span id="aiProgressText" class="font-weight-bold">Analizando documento...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Separador Visual --}}
                <div class="d-flex align-items-center my-4">
                    <hr class="flex-grow-1" style="border-top: 1px dashed #ccc;">
                    <span class="badge badge-light border px-4 py-2 text-muted text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">
                        o llena manualmente
                    </span>
                    <hr class="flex-grow-1" style="border-top: 1px dashed #ccc;">
                </div>

                {{-- ==========================================
                     FORMULARIO: DATOS PRINCIPALES
                     ========================================== --}}
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header border-0" style="background-color: var(--unasam-navy); color: white;">
                        <h3 class="card-title mb-0 font-weight-bold" style="font-size: 1.05rem;">Datos de la Investigación</h3>
                    </div>

                    <div class="card-body bg-white p-4">
                        <div class="row">
                            <div class="col-md-8 mb-4">
                                <label class="form-label text-dark font-weight-bold">Título <span class="text-danger">*</span></label>
                                <input type="text" name="titulo" id="titulo" class="form-control" value="{{ old('titulo') }}" maxlength="200" required placeholder="Ej. Análisis del impacto hidrológico...">
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label text-dark font-weight-bold">Año del proyecto <span class="text-danger">*</span></label>
                                <input type="number" name="anio" id="anio" class="form-control" value="{{ old('anio') }}" min="1900" max="{{ date('Y') + 1 }}" required>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label text-dark font-weight-bold">Fecha inicio</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ old('fecha_inicio') }}">
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label text-dark font-weight-bold">Fecha fin</label>
                                <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ old('fecha_fin') }}">
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label text-dark font-weight-bold">Resumen / Descripción</label>
                                <textarea name="descripcion_proyecto" id="descripcion_proyecto" class="form-control" rows="4" placeholder="Breve descripción del estudio...">{{ old('descripcion_proyecto') }}</textarea>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label text-dark font-weight-bold">Palabras clave / Keywords</label>
                                <input type="text" name="keywords" id="keywords" class="form-control" value="{{ old('keywords') }}" placeholder="Ej: inteligencia artificial, educación, minería de datos (separadas por comas)">
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label text-dark font-weight-bold">Presupuesto (S/)</label>
                                <input type="number" step="0.01" name="presupuesto" id="presupuesto" class="form-control" value="{{ old('presupuesto') }}" placeholder="0.00">
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label text-dark font-weight-bold">Área de investigación <span class="text-danger">*</span></label>
                                <select name="id_area" id="id_area" class="form-control" required>
                                    <option value="">— Seleccione —</option>
                                    @foreach ($areas as $a)
                                        <option value="{{ $a->ID_AREA }}" {{ old('id_area') == $a->ID_AREA ? 'selected' : '' }} data-nombre="{{ $a->NOMBRE_AREA }}">
                                            {{ $a->NOMBRE_AREA }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-4">
                                <label for="id_tipo" class="form-label text-dark font-weight-bold">Tipo de proyecto</label>
                                <select name="id_tipo" id="id_tipo" class="form-control">
                                    <option value="">— Selecciona —</option>
                                    @foreach ($tipos as $t)
                                        <option value="{{ $t->ID_TIPO }}" @selected(old('id_tipo') == $t->ID_TIPO) data-nombre="{{ $t->NOMBRE_TIPO }}">
                                            {{ $t->NOMBRE_TIPO }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_tipo') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label text-dark font-weight-bold">Estado del proyecto <span class="text-danger">*</span></label>
                                <select name="id_estado" id="id_estado" class="form-control" required>
                                    <option value="">— Seleccione —</option>
                                    @foreach ($estados as $e)
                                        <option value="{{ $e->ID_ESTADO }}" {{ old('id_estado') == $e->ID_ESTADO ? 'selected' : '' }} data-nombre="{{ $e->NOMBRE_ESTADO }}">
                                            {{ $e->NOMBRE_ESTADO }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-8 mb-4">
                                <label class="form-label text-dark font-weight-bold">Autor principal <span class="text-danger">*</span></label>
                                <select name="id_autor" id="id_autor" class="form-control js-select2" required>
                                    <option value="">— Buscar investigador —</option>
                                    @foreach ($investigadores as $i)
                                        <option value="{{ $i->ID_INVESTIGADOR }}" {{ old('id_autor') == $i->ID_INVESTIGADOR ? 'selected' : '' }} data-nombre="{{ $i->nombre }}">
                                            {{ $i->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label text-dark font-weight-bold">Grupo de investigación</label>
                                <select name="id_grupo" id="id_grupo" class="form-control">
                                    <option value="">— Ninguno —</option>
                                    @foreach ($grupos as $g)
                                        <option value="{{ $g->ID_GRUPO }}" {{ old('id_grupo') == $g->ID_GRUPO ? 'selected' : '' }} data-nombre="{{ $g->NOMBRE_GRUPO }}">
                                            {{ $g->NOMBRE_GRUPO }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-8 mb-4">
                                <label class="form-label text-dark font-weight-bold">Coautores</label>
                                <select name="coautores[]" id="coautores" class="form-control select2-multiple" multiple data-placeholder="Escribe para buscar y seleccionar...">
                                    @foreach ($investigadores as $i)
                                        <option value="{{ $i->ID_INVESTIGADOR }}" data-nombre="{{ $i->nombre }}" @if (collect(old('coautores', []))->contains($i->ID_INVESTIGADOR)) selected @endif>
                                            {{ $i->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 mb-2">
                                <label for="pdf" class="text-dark font-weight-bold">Subir Archivo (PDF final)</label>
                                <div class="custom-file">
                                    <input type="file" name="pdf" class="custom-file-input" id="pdf" accept="application/pdf">
                                    <label class="custom-file-label text-muted" for="pdf">Elegir PDF para el repositorio...</label>
                                </div>
                                <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle mr-1"></i>Este PDF quedará guardado de manera pública como el documento final.</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ==========================================
                     SECCIÓN: PUBLICACIÓN Y GUARDADO
                     ========================================== --}}
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header border-0" style="background-color: #f8f9fa;">
                        <h3 class="card-title mb-0 font-weight-bold text-dark" style="font-size: 1.05rem;">
                            <i class="fas fa-book-open mr-2 text-muted"></i>Datos de Publicación
                        </h3>
                    </div>
                    <div class="card-body bg-white p-4">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label text-dark font-weight-bold">Colección / Revista <span class="text-danger">*</span></label>
                                <select name="id_revista" id="id_revista" class="form-control" required>
                                    <option value="">— Seleccione —</option>
                                    @foreach ($revistas as $r)
                                        <option value="{{ $r->ID_REVISTA }}" {{ old('id_revista') == $r->ID_REVISTA ? 'selected' : '' }}>
                                            {{ $r->NOMBRE_REVISTA }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-dark font-weight-bold">Año de publicación <span class="text-danger">*</span></label>
                                <input type="number" name="anio_publicacion" id="anio_publicacion" class="form-control" value="{{ old('anio_publicacion') }}" min="1900" max="{{ date('Y') + 1 }}" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-light d-flex justify-content-end p-3 border-top-0">
                        <a href="{{ route('publicaciones.index') }}" class="btn btn-outline-secondary mr-3" style="border-radius: 6px;">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 font-weight-bold" style="border-radius: 6px; background-color: var(--esat-blue);">
                            <i class="bi bi-save2 mr-1"></i> Guardar Publicación
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')

    {{-- TODO TU CÓDIGO JS DE LA IA SE MANTIENE INTACTO AQUÍ ABAJO --}}
    <script>
        // 1. Inicializar Select2 para Coautores con soporte múltiple
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
        $(document).ready(function() {
            const pdfInput = $('#aiPdfFile');
            const fillButton = $('#fillWithAI');
            const buttonText = $('#aiButtonText');
            const buttonIcon = $('#aiButtonIcon');
            const issuesBox = $('#aiIssuesBox');
            const issuesList = $('#aiIssuesList');
            const progressBar = $('#aiProgressBar');
            const progressText = $('#aiProgressText');

            // Habilita el botón si el navegador restauró el archivo tras un reload
            if (pdfInput[0] && pdfInput[0].files && pdfInput[0].files.length > 0) {
                fillButton.prop('disabled', false);
            }

            // Actualizar nombre del archivo en el label (Ambos inputs)
            $('.custom-file-input').on('change', function() {
                const fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html(fileName || 'Elegir archivo PDF...');
                
                // Solo activar el botón de IA si es el input del Asistente
                if($(this).attr('id') === 'aiPdfFile'){
                    fillButton.prop('disabled', !this.files.length);
                    if (this.files.length) {
                        buttonText.text('Extraer Información');
                        buttonIcon.removeClass('fa-magic').addClass('fa-bolt');
                    }
                }
            });

            // Función para mostrar notificaciones elegante e institucionales usando PNotify
            function showToast(message, type = 'info') {
                const title = type === 'success' ? 'Éxito' : type === 'error' ? 'Error' : 'Información';
                new PNotify({
                    title: title,
                    text: message,
                    type: type, // 'success', 'error', 'info', 'notice'
                    delay: 4000,
                    styling: 'brighttheme' // Diseño limpio y moderno
                });
            }

            // Evento principal: Llenar con IA
            fillButton.on('click', async function() {
                const file = pdfInput[0]?.files?.[0];
                if (!file) {
                    showToast('Por favor selecciona un archivo PDF', 'error');
                    return;
                }

                // UI: Deshabilitar y mostrar progreso
                fillButton.prop('disabled', true).css('cursor', 'not-allowed');
                buttonText.text('Procesando...');
                buttonIcon.removeClass('fa-bolt').addClass('fa-spinner fa-spin');
                progressBar.show();
                issuesBox.hide();

                const formData = new FormData();
                formData.append('pdf', file);

                try {
                    const resp = await fetch('{{ route('extract.article') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        body: formData
                    });

                    let result = {};
                    try {
                        result = await resp.json();
                    } catch (e) {
                        result = {};
                    }

                    if (!resp.ok || !result.success) {
                        let msg = result.message || `Error ${resp.status} al extraer información`;
                        if (resp.status === 419) msg = 'Sesión expirada o token CSRF inválido (419). Recarga la página e inténtalo de nuevo.';
                        if (resp.status === 401) msg = 'No autorizado (401). Inicia sesión nuevamente.';
                        if (resp.status === 500) msg = 'Error interno del servidor (500). Intenta con otro PDF o vuelve más tarde.';
                        throw new Error(msg);
                    }

                    // ÉXITO: Llenar campos del formulario
                    fillFormFields(result.data);

                    // Mostrar issues si existen
                    if (Array.isArray(result.issues) && result.issues.length > 0) {
                        showIssues(result.issues);
                    }

                    showToast('✅ Formulario pre-llenado exitosamente. Revisa y edita antes de guardar.', 'success');
                    buttonIcon.removeClass('fa-spinner fa-spin').addClass('fa-check');
                    buttonText.text('Información Extraída');
                } catch (error) {
                    console.error('Error:', error);
                    showToast(error.message || '❌ Error al procesar el PDF. Puedes llenar el formulario manualmente.', 'error');
                    buttonIcon.removeClass('fa-spinner fa-spin').addClass('fa-times');
                    buttonText.text('Error en Extracción');
                } finally {
                    progressBar.hide();
                    setTimeout(() => {
                        fillButton.prop('disabled', false).css('cursor', 'pointer');
                        buttonIcon.removeClass('fa-spinner fa-spin fa-check fa-times').addClass('fa-magic');
                        buttonText.text('Extraer Nuevamente');
                    }, 3000);
                }
            });

            // Función para llenar los campos del formulario
            function fillFormFields(data) {
                // Campos de texto simples
                if (data.titulo) setFieldValue('#titulo', data.titulo);
                if (data.anio_proyecto) setFieldValue('#anio', data.anio_proyecto);
                if (data.fecha_inicio) setFieldValue('#fecha_inicio', data.fecha_inicio);
                if (data.fecha_fin) setFieldValue('#fecha_fin', data.fecha_fin);
                if (data.descripcion) setFieldValue('#descripcion_proyecto', data.descripcion);
                if (data.presupuesto) setFieldValue('#presupuesto', data.presupuesto);
                if (data.keywords) setFieldValue('#keywords', data.keywords);
                if (data.rol) setFieldValue('#rol_autor', data.rol);
                if (data.anio_proyecto) setFieldValue('#anio_publicacion', data.anio_proyecto);

                // Mapear por ID directamente (coincidencia semántica ultra precisa de la IA en backend)
                if (data.id_area !== undefined && data.id_area !== null) {
                    selectById('#id_area', data.id_area);
                } else if (data.area_investigacion) {
                    selectByName('#id_area', data.area_investigacion);
                }

                if (data.id_tipo !== undefined && data.id_tipo !== null) {
                    selectById('#id_tipo', data.id_tipo);
                } else if (data.tipo_proyecto) {
                    selectByName('#id_tipo', data.tipo_proyecto);
                }

                if (data.id_estado !== undefined && data.id_estado !== null) {
                    selectById('#id_estado', data.id_estado);
                } else if (data.estado_proyecto) {
                    selectByName('#id_estado', data.estado_proyecto);
                }

                if (data.id_autor !== undefined && data.id_autor !== null) {
                    selectById('#id_autor', data.id_autor);
                } else if (data.raw_autor_principal) {
                    selectByName('#id_autor', data.raw_autor_principal);
                } else if (data.autor_principal) {
                    selectByName('#id_autor', data.autor_principal);
                }

                if (data.id_grupo !== undefined && data.id_grupo !== null) {
                    selectById('#id_grupo', data.id_grupo);
                } else if (data.grupo_investigacion) {
                    selectByName('#id_grupo', data.grupo_investigacion);
                }

                // Coautores mapeados directamente por sus IDs
                if (data.coautores && Array.isArray(data.coautores) && data.coautores.length > 0) {
                    selectCoautoresByIds(data.coautores);
                } else if (data.coautores_no_registrados && Array.isArray(data.coautores_no_registrados) && data.coautores_no_registrados.length > 0) {
                    selectCoautoresByName(data.coautores_no_registrados);
                }
            }

            function setFieldValue(selector, value) {
                const field = $(selector);
                if (field.length && value != null) {
                    field.val(value);
                    field.addClass('is-valid');
                    const label = field.closest('.form-group, .mb-4').find('label');
                    if (label.find('.badge-success').length === 0) {
                        label.append(' <span class="badge badge-success ml-1" style="font-size:0.7rem;">IA</span>');
                    }
                    setTimeout(() => field.removeClass('is-valid'), 3000);
                }
            }

            // Seleccionar un elemento por su ID de manera limpia (Ideal para Select2 también)
            function selectById(selector, id) {
                if (id == null) return;
                const select = $(selector);
                if (select.length) {
                    select.val(id);
                    // Actualizar en caso de que esté usando Select2
                    if (select.hasClass('js-select2') || select.hasClass('select2-hidden-accessible')) {
                        select.trigger('change');
                    }
                    select.addClass('is-valid');
                    const label = select.closest('.form-group, .mb-4').find('label');
                    if (label.find('.badge-success').length === 0) {
                        label.append(' <span class="badge badge-success ml-1" style="font-size:0.7rem;">IA</span>');
                    }
                    setTimeout(() => select.removeClass('is-valid'), 3000);
                }
            }

            // Seleccionar múltiples coautores por sus IDs de manera limpia
            function selectCoautoresByIds(ids) {
                const select = $('#coautores');
                if (select.length && Array.isArray(ids)) {
                    select.val(ids);
                    select.trigger('change'); // Le avisa a Select2 para que dibuje las etiquetas gráficas
                    
                    select.addClass('is-valid');
                    const label = select.closest('.form-group, .mb-4').find('label');
                    if (label.find('.badge-success').length === 0) {
                        label.append(' <span class="badge badge-success ml-1" style="font-size:0.7rem;">IA (' + ids.length + ')</span>');
                    }
                    setTimeout(() => select.removeClass('is-valid'), 3000);
                }
            }

            // Mapeador de respaldo por coincidencia de texto (Heurística)
            function selectByName(selector, nombreBuscado) {
                if (!nombreBuscado) return;
                const select = $(selector);
                let optionFound = false;
                const target = nombreBuscado.toString().toLowerCase().trim();

                // Búsqueda exacta
                select.find('option').each(function() {
                    const nombreOption = (($(this).data('nombre') || $(this).text()) + '').toLowerCase().trim();
                    if (nombreOption === target) {
                        $(this).prop('selected', true);
                        if (select.hasClass('js-select2') || select.hasClass('select2-hidden-accessible')) {
                            select.trigger('change');
                        }
                        optionFound = true;
                        return false;
                    }
                });

                // Búsqueda parcial
                if (!optionFound) {
                    select.find('option').each(function() {
                        const nombreOption = (($(this).data('nombre') || $(this).text()) + '').toLowerCase();
                        if (nombreOption.includes(target) || target.includes(nombreOption)) {
                            $(this).prop('selected', true);
                            if (select.hasClass('js-select2') || select.hasClass('select2-hidden-accessible')) {
                                select.trigger('change');
                            }
                            optionFound = true;
                            return false;
                        }
                    });
                }

                if (optionFound) {
                    select.addClass('is-valid');
                    const label = select.closest('.form-group, .mb-4').find('label');
                    if (label.find('.badge-warning').length === 0) {
                        label.append(' <span class="badge badge-warning ml-1" style="font-size:0.7rem;">IA (verificar)</span>');
                    }
                    setTimeout(() => select.removeClass('is-valid'), 3000);
                }
            }

            // Mapeador de coautores de respaldo por coincidencia de texto (Heurística)
            function selectCoautoresByName(nombresCoautores) {
                const select = $('#coautores');
                if (!select.length) return;
                
                const selectedValues = select.val() || [];
                
                (nombresCoautores || []).forEach(nombreBuscado => {
                    const target = (nombreBuscado + '').toLowerCase().trim();
                    select.find('option').each(function() {
                        const nombreOption = (($(this).data('nombre') || $(this).text()) + '').toLowerCase().trim();
                        if (nombreOption.includes(target) || target.includes(nombreOption)) {
                            const val = $(this).val();
                            if (!selectedValues.includes(val)) {
                                selectedValues.push(val);
                            }
                        }
                    });
                });

                select.val(selectedValues);
                select.trigger('change');

                if (nombresCoautores && nombresCoautores.length > 0) {
                    select.addClass('is-valid');
                    const label = select.closest('.form-group, .mb-4').find('label');
                    if (label.find('.badge-info').length === 0) {
                        label.append(' <span class="badge badge-info ml-1" style="font-size:0.7rem;">IA (' + nombresCoautores.length + ')</span>');
                    }
                    setTimeout(() => select.removeClass('is-valid'), 3000);
                }
            }

            function showIssues(issues) {
                issuesList.empty();
                (issues || []).forEach(issue => {
                    issuesList.append(`<li>${issue}</li>`);
                });
                issuesBox.show();
            }
        });
    </script>
@endpush