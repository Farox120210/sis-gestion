@extends('layouts.admin')
@section('content')
<div class="row">
    <div class="h1">Nueva publicación</div>
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

<div class="row">
    <div class="col-md-12">
        <form action="{{ route('publicaciones.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- NUEVA SECCIÓN: ASISTENTE IA --}}
            <div class="card card-outline card-purple mb-3">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h3 class="card-title mb-0 text-white">
                        <i class="fas fa-robot"></i> Asistente Inteligente (Opcional)
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle"></i>
                                <strong>¿Tienes un PDF?</strong> Sube el documento y deja que la IA extraiga automáticamente la
                                información del proyecto. Luego podrás revisar y editar antes de guardar.
                            </div>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="form-label font-weight-bold">
                                <i class="fas fa-file-pdf text-danger"></i> Selecciona tu PDF
                            </label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="aiPdfFile" accept="application/pdf">
                                <label class="custom-file-label" for="aiPdfFile">Elegir archivo PDF...</label>
                            </div>
                            <small class="form-text text-muted">Máximo 25 MB • Solo archivos PDF</small>
                        </div>

                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <button type="button" id="fillWithAI" class="btn btn-block btn-lg"
                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;"
                                disabled>
                                <i class="fas fa-magic" id="aiButtonIcon"></i>
                                <span id="aiButtonText">Extraer con IA</span>
                            </button>
                        </div>

                        {{-- Área de Issues/Advertencias --}}
                        <div class="col-md-12">
                            <div id="aiIssuesBox" class="alert alert-warning" style="display: none;">
                                <h5 class="alert-heading">
                                    <i class="fas fa-exclamation-triangle"></i> Revisa estos campos:
                                </h5>
                                <ul id="aiIssuesList" class="mb-0"></ul>
                            </div>
                        </div>

                        {{-- Barra de progreso --}}
                        <div class="col-md-12">
                            <div id="aiProgressBar" class="progress" style="display: none; height: 25px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                     style="width: 100%; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);">
                                    <span id="aiProgressText">Extrayendo información...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Separador --}}
            <div class="text-center my-3">
                <span class="badge badge-secondary px-4 py-2" style="font-size: 0.9rem;">
                    <i class="fas fa-grip-lines-vertical"></i> o llena manualmente <i class="fas fa-grip-lines-vertical"></i>
                </span>
            </div>

            {{-- TU FORMULARIO ORIGINAL (con IDs agregados) --}}
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title mb-0">Datos de producción científica</h3>
                    <div class="card-tools">
                        <a href="{{ route('publicaciones.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>

                        {{-- === NUEVO BOTÓN: REPORTE EXCEL === --}}
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#exportExcelModal">
                            <i class="fas fa-file-excel"></i> Reporte Excel
                        </button>
                        {{-- === /NUEVO BOTÓN === --}}

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save2"></i> Guardar
                        </button>
                    </div>
                </div>

                <div class="card-body" style="display:block;">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Título *</label>
                            <input type="text" name="titulo" id="titulo" class="form-control"
                                   value="{{ old('titulo') }}" maxlength="200" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Año del proyecto *</label>
                            <input type="number" name="anio" id="anio" class="form-control"
                                   value="{{ old('anio') }}" min="1900" max="{{ date('Y') + 1 }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha inicio</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control"
                                   value="{{ old('fecha_inicio') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha fin</label>
                            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control"
                                   value="{{ old('fecha_fin') }}">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion_proyecto" id="descripcion_proyecto" class="form-control" rows="3">{{ old('descripcion_proyecto') }}</textarea>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Presupuesto</label>
                            <input type="number" step="0.01" name="presupuesto" id="presupuesto"
                                   class="form-control" value="{{ old('presupuesto') }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Área de investigación *</label>
                            <select name="id_area" id="id_area" class="form-control" required>
                                <option value="">— Seleccione —</option>
                                @foreach ($areas as $a)
                                    <option value="{{ $a->ID_AREA }}"
                                        {{ old('id_area') == $a->ID_AREA ? 'selected' : '' }}
                                        data-nombre="{{ $a->NOMBRE_AREA }}">
                                        {{ $a->NOMBRE_AREA }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="id_tipo" class="form-label">Tipo de proyecto</label>
                            <select name="id_tipo" id="id_tipo" class="form-select">
                                <option value="">— Selecciona —</option>
                                @foreach ($tipos as $t)
                                    <option value="{{ $t->ID_TIPO }}" @selected(old('id_tipo') == $t->ID_TIPO)
                                        data-nombre="{{ $t->NOMBRE_TIPO }}">
                                        {{ $t->NOMBRE_TIPO }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_tipo')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado del proyecto *</label>
                            <select name="id_estado" id="id_estado" class="form-control" required>
                                <option value="">— Seleccione —</option>
                                @foreach ($estados as $e)
                                    <option value="{{ $e->ID_ESTADO }}"
                                        {{ old('id_estado') == $e->ID_ESTADO ? 'selected' : '' }}
                                        data-nombre="{{ $e->NOMBRE_ESTADO }}">
                                        {{ $e->NOMBRE_ESTADO }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Autor principal *</label>
                            <select name="id_autor" id="id_autor" class="form-control js-select2" required>
                                <option value="">— Seleccione —</option>
                                @foreach ($investigadores as $i)
                                    <option value="{{ $i->ID_INVESTIGADOR }}"
                                        {{ old('id_autor') == $i->ID_INVESTIGADOR ? 'selected' : '' }}
                                        data-nombre="{{ $i->nombre }}">
                                        {{ $i->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Rol (opcional):</small>
                            <input type="text" name="rol_autor" id="rol_autor" class="form-control"
                                   value="{{ old('rol_autor') }}" maxlength="50">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Grupo de investigación</label>
                            <select name="id_grupo" id="id_grupo" class="form-control">
                                <option value="">— Ninguno —</option>
                                @foreach ($grupos as $g)
                                    <option value="{{ $g->ID_GRUPO }}"
                                        {{ old('id_grupo') == $g->ID_GRUPO ? 'selected' : '' }}
                                        data-nombre="{{ $g->NOMBRE_GRUPO }}">
                                        {{ $g->NOMBRE_GRUPO }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Coautores (Ctrl/Cmd para múltiple)</label>
                            <select name="coautores[]" id="coautores" class="form-control" multiple size="6">
                                @foreach ($investigadores as $i)
                                    <option value="{{ $i->ID_INVESTIGADOR }}" data-nombre="{{ $i->nombre }}"
                                        @if (collect(old('coautores', []))->contains($i->ID_INVESTIGADOR)) selected @endif>
                                        {{ $i->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="pdf">Subir PDF (para archivo final)</label>
                                <input type="file" name="pdf" class="form-control">
                                <small class="text-muted">Este PDF se guardará como el documento final del proyecto</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title mb-0">Datos de publicación (revista)</h3>
                </div>
                <div class="card-body" style="display:block;">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Revista *</label>
                            <select name="id_revista" id="id_revista" class="form-control" required>
                                <option value="">— Seleccione —</option>
                                @foreach ($revistas as $r)
                                    <option value="{{ $r->ID_REVISTA }}"
                                        {{ old('id_revista') == $r->ID_REVISTA ? 'selected' : '' }}>
                                        {{ $r->NOMBRE_REVISTA }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Año de publicación *</label>
                            <input type="number" name="anio_publicacion" id="anio_publicacion" class="form-control"
                                   value="{{ old('anio_publicacion') }}" min="1900" max="{{ date('Y') + 1 }}" required>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <a href="{{ route('publicaciones.index') }}" class="btn btn-outline-secondary mr-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save2"></i> Guardar</button>
                </div>
            </div>
        </form>

        {{-- ===== MODAL EXPORTAR EXCEL (FUERA DEL <form> PARA EVITAR FORM ANIDADO) ===== --}}
        <div class="modal fade" id="exportExcelModal" tabindex="-1" role="dialog" aria-labelledby="exportExcelModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <form method="GET" action="{{ route('publicaciones.export') }}" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exportExcelModalLabel">
                            <i class="fas fa-file-excel"></i> Exportar a Excel
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-2">
                            <label for="export_from">Desde (creación)</label>
                            <input type="date" name="from" id="export_from" class="form-control">
                        </div>
                        <div class="form-group mb-0">
                            <label for="export_to">Hasta (creación)</label>
                            <input type="date" name="to" id="export_to" class="form-control">
                        </div>
                        <small class="text-muted d-block mt-2">
                            Si dejas ambos vacíos, exportaré todos los registros.
                        </small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-download"></i> Descargar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        {{-- ===== /MODAL EXPORTAR EXCEL ===== --}}
    </div>
</div>
@endsection

@push('scripts')
<script>
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

        // Actualizar nombre del archivo en el label
        pdfInput.on('change', function() {
            const fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName || 'Elegir archivo PDF...');
            fillButton.prop('disabled', !this.files.length);

            if (this.files.length) {
                buttonText.text('Extraer Información');
                buttonIcon.removeClass('fa-magic').addClass('fa-bolt');
            }
        });

        // Función para mostrar notificaciones
        function showToast(message, type = 'info') {
            try {
                const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-times-circle' : 'fa-info-circle';
                const bgColor = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
                $(document).Toasts('create', {
                    class: bgColor,
                    title: type === 'success' ? 'Éxito' : type === 'error' ? 'Error' : 'Información',
                    subtitle: '',
                    body: message,
                    icon: `fas ${icon}`,
                    autohide: true,
                    delay: 5000,
                });
            } catch (e) {
                alert(message);
            }
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
                try { result = await resp.json(); } catch (e) { result = {}; }

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
            if (data.titulo) setFieldValue('#titulo', data.titulo);
            if (data.anio_proyecto) setFieldValue('#anio', data.anio_proyecto);
            if (data.fecha_inicio) setFieldValue('#fecha_inicio', data.fecha_inicio);
            if (data.fecha_fin) setFieldValue('#fecha_fin', data.fecha_fin);
            if (data.descripcion) setFieldValue('#descripcion_proyecto', data.descripcion);
            if (data.presupuesto) setFieldValue('#presupuesto', data.presupuesto);
            if (data.rol) setFieldValue('#rol_autor', data.rol);
            if (data.anio_proyecto) setFieldValue('#anio_publicacion', data.anio_proyecto);

            if (data.area_investigacion) selectByName('#id_area', data.area_investigacion);
            if (data.tipo_proyecto) selectByName('#id_tipo', data.tipo_proyecto);
            if (data.estado_proyecto) selectByName('#id_estado', data.estado_proyecto);
            if (data.autor_principal) selectByName('#id_autor', data.autor_principal);
            if (data.grupo_investigacion) selectByName('#id_grupo', data.grupo_investigacion);

            if (data.coautores && Array.isArray(data.coautores)) {
                selectCoautoresByName(data.coautores);
            }
        }

        function setFieldValue(selector, value) {
            const field = $(selector);
            if (field.length && value != null) {
                field.val(value);
                field.addClass('is-valid');
                const label = field.closest('.form-group, .mb-3').find('label');
                if (label.find('.badge-success').length === 0) {
                    label.append(' <span class="badge badge-success">IA</span>');
                }
                setTimeout(() => field.removeClass('is-valid'), 3000);
            }
        }

        function selectByName(selector, nombreBuscado) {
            if (!nombreBuscado) return;
            const select = $(selector);
            let optionFound = false;
            const target = nombreBuscado.toString().toLowerCase().trim();

            // exacta
            select.find('option').each(function() {
                const nombreOption = (($(this).data('nombre') || $(this).text()) + '').toLowerCase().trim();
                if (nombreOption === target) {
                    $(this).prop('selected', true);
                    optionFound = true;
                    return false;
                }
            });

            // parcial
            if (!optionFound) {
                select.find('option').each(function() {
                    const nombreOption = (($(this).data('nombre') || $(this).text()) + '').toLowerCase();
                    if (nombreOption.includes(target) || target.includes(nombreOption)) {
                        $(this).prop('selected', true);
                        optionFound = true;
                        return false;
                    }
                });
            }

            if (optionFound) {
                select.addClass('is-valid');
                const label = select.closest('.form-group, .mb-3').find('label');
                if (label.find('.badge-warning').length === 0) {
                    label.append(' <span class="badge badge-warning">IA (verificar)</span>');
                }
                setTimeout(() => select.removeClass('is-valid'), 3000);
            }
        }

        function selectCoautoresByName(nombresCoautores) {
            const select = $('#coautores');
            (nombresCoautores || []).forEach(nombreBuscado => {
                const target = (nombreBuscado + '').toLowerCase();
                select.find('option').each(function() {
                    const nombreOption = (($(this).data('nombre') || $(this).text()) + '').toLowerCase();
                    if (nombreOption.includes(target) || target.includes(nombreOption)) {
                        $(this).prop('selected', true);
                    }
                });
            });

            if (nombresCoautores && nombresCoautores.length > 0) {
                select.addClass('is-valid');
                const label = select.closest('.form-group, .mb-3').find('label');
                if (label.find('.badge-info').length === 0) {
                    label.append(' <span class="badge badge-info">IA (' + nombresCoautores.length + ')</span>');
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
