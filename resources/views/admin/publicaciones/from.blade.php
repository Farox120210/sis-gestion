    @extends('layouts.app')

    @section('title', isset($project) ? 'Editar Proyecto' : 'Crear Proyecto')

    @section('content')
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">
                {{ isset($project) ? 'Editar Proyecto' : 'Crear Proyecto' }}
            </h1>
        </div>

        <form action="{{ isset($project) ? route('projects.update', $project) : route('projects.store') }}" 
            method="POST" 
            enctype="multipart/form-data"
            id="projectForm"
            class="space-y-6">
            @csrf
            @if(isset($project))
                @method('PUT')
            @endif

            <!-- Subida de PDF -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">📄 Documento PDF</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subir PDF</label>
                        <input type="file" name="pdf" id="pdfFile" accept="application/pdf" 
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('pdf')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if(isset($project) && $project->pdf_path)
                        <div class="text-sm text-gray-600">
                            PDF actual: <a href="{{ $project->pdf_url }}" target="_blank" class="text-blue-600 hover:underline">Ver documento</a>
                        </div>
                    @endif

                    <button type="button" id="fillWithAI" 
                        class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-3 rounded-lg hover:from-purple-700 hover:to-indigo-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        disabled>
                        <span>🤖</span>
                        <span id="aiButtonText">Llenar con IA</span>
                    </button>
                </div>
            </div>

            <!-- Campos del formulario -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Información del Proyecto</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Título -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Título <span class="text-red-500">*</span>
                            <span id="confidence-titulo" class="ml-2"></span>
                        </label>
                        <input type="text" name="titulo" id="titulo" value="{{ old('titulo', $project->titulo ?? '') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('titulo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Año del proyecto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Año del Proyecto <span class="text-red-500">*</span>
                            <span id="confidence-anio_proyecto" class="ml-2"></span>
                        </label>
                        <input type="number" name="anio_proyecto" id="anio_proyecto" min="1900" max="2100" 
                            value="{{ old('anio_proyecto', $project->anio_proyecto ?? '') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('anio_proyecto')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Presupuesto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Presupuesto
                            <span id="confidence-presupuesto" class="ml-2"></span>
                        </label>
                        <input type="number" name="presupuesto" id="presupuesto" step="0.01" min="0"
                            value="{{ old('presupuesto', $project->presupuesto ?? '') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('presupuesto')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Fecha inicio -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Fecha de Inicio <span class="text-red-500">*</span>
                            <span id="confidence-fecha_inicio" class="ml-2"></span>
                        </label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" 
                            value="{{ old('fecha_inicio', isset($project) ? $project->fecha_inicio->format('Y-m-d') : '') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('fecha_inicio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Fecha fin -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Fecha de Fin <span class="text-red-500">*</span>
                            <span id="confidence-fecha_fin" class="ml-2"></span>
                        </label>
                        <input type="date" name="fecha_fin" id="fecha_fin"
                            value="{{ old('fecha_fin', isset($project) ? $project->fecha_fin->format('Y-m-d') : '') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('fecha_fin')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Área de investigación -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Área de Investigación
                            <span id="confidence-area_investigacion" class="ml-2"></span>
                        </label>
                        <input type="text" name="area_investigacion" id="area_investigacion" maxlength="150"
                            value="{{ old('area_investigacion', $project->area_investigacion ?? '') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('area_investigacion')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tipo de proyecto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de Proyecto
                            <span id="confidence-tipo_proyecto" class="ml-2"></span>
                        </label>
                        <select name="tipo_proyecto" id="tipo_proyecto"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Seleccione...</option>
                            <option value="tesis" {{ old('tipo_proyecto', $project->tipo_proyecto ?? '') == 'tesis' ? 'selected' : '' }}>Tesis</option>
                            <option value="artículo" {{ old('tipo_proyecto', $project->tipo_proyecto ?? '') == 'artículo' ? 'selected' : '' }}>Artículo</option>
                            <option value="proyecto I+D" {{ old('tipo_proyecto', $project->tipo_proyecto ?? '') == 'proyecto I+D' ? 'selected' : '' }}>Proyecto I+D</option>
                            <option value="reporte técnico" {{ old('tipo_proyecto', $project->tipo_proyecto ?? '') == 'reporte técnico' ? 'selected' : '' }}>Reporte Técnico</option>
                        </select>
                        @error('tipo_proyecto')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Estado del proyecto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Estado del Proyecto
                            <span id="confidence-estado_proyecto" class="ml-2"></span>
                        </label>
                        <select name="estado_proyecto" id="estado_proyecto"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Seleccione...</option>
                            <option value="en revisión" {{ old('estado_proyecto', $project->estado_proyecto ?? '') == 'en revisión' ? 'selected' : '' }}>En revisión</option>
                            <option value="aprobado" {{ old('estado_proyecto', $project->estado_proyecto ?? '') == 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                            <option value="publicado" {{ old('estado_proyecto', $project->estado_proyecto ?? '') == 'publicado' ? 'selected' : '' }}>Publicado</option>
                            <option value="archivado" {{ old('estado_proyecto', $project->estado_proyecto ?? '') == 'archivado' ? 'selected' : '' }}>Archivado</option>
                        </select>
                        @error('estado_proyecto')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Autor principal -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Autor Principal
                            <span id="confidence-autor_principal" class="ml-2"></span>
                        </label>
                        <input type="text" name="autor_principal" id="autor_principal" maxlength="150"
                            value="{{ old('autor_principal', $project->autor_principal ?? '') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('autor_principal')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Rol -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Rol
                            <span id="confidence-rol" class="ml-2"></span>
                        </label>
                        <input type="text" name="rol" id="rol" maxlength="100"
                            value="{{ old('rol', $project->rol ?? '') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('rol')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Grupo de investigación -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Grupo de Investigación
                            <span id="confidence-grupo_investigacion" class="ml-2"></span>
                        </label>
                        <input type="text" name="grupo_investigacion" id="grupo_investigacion" maxlength="150"
                            value="{{ old('grupo_investigacion', $project->grupo_investigacion ?? '') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('grupo_investigacion')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Descripción -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Descripción <span class="text-red-500">*</span>
                            <span id="confidence-descripcion" class="ml-2"></span>
                        </label>
                        <textarea name="descripcion" id="descripcion" rows="4" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('descripcion', $project->descripcion ?? '') }}</textarea>
                        @error('descripcion')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Coautores -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Coautores (uno por línea)
                            <span id="confidence-coautores" class="ml-2"></span>
                        </label>
                        <textarea name="coautores_text" id="coautores_text" rows="3"
                            placeholder="Nombre Apellido 1&#10;Nombre Apellido 2&#10;..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('coautores_text', isset($project) && $project->coautores ? implode("\n", $project->coautores) : '') }}</textarea>
                        @error('coautores')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Issues/Advertencias -->
            <div id="issuesContainer" class="hidden bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h3 class="font-semibold text-yellow-900 mb-2">⚠️ Advertencias de la IA</h3>
                <ul id="issuesList" class="list-disc list-inside text-sm text-yellow-800 space-y-1"></ul>
            </div>

            <!-- Botones de acción -->
            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 font-medium">
                    {{ isset($project) ? 'Actualizar Proyecto' : 'Crear Proyecto' }}
                </button>
                <a href="{{ route('projects.index') }}" class="bg-gray-200 text-gray-700 px-8 py-3 rounded-lg hover:bg-gray-300 font-medium">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const pdfInput = document.getElementById('pdfFile');
        const fillButton = document.getElementById('fillWithAI');
        const form = document.getElementById('projectForm');
        const buttonText = document.getElementById('aiButtonText');
        const issuesContainer = document.getElementById('issuesContainer');
        const issuesList = document.getElementById('issuesList');

        // Habilitar botón cuando se selecciona PDF
        pdfInput.addEventListener('change', function() {
            fillButton.disabled = !this.files.length;
        });

        // Procesar coautores antes de enviar
        form.addEventListener('submit', function(e) {
            const coautoresText = document.getElementById('coautores_text').value;
            const coautoresArray = coautoresText.split('\n')
                .map(line => line.trim())
                .filter(line => line.length > 0);
            
            // Eliminar el textarea y crear inputs hidden para cada coautor
            coautoresArray.forEach((coautor, index) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `coautores[${index}]`;
                input.value = coautor;
                form.appendChild(input);
            });
        });

        // Llenar con IA
        fillButton.addEventListener('click', async function() {
            const file = pdfInput.files[0];
            if (!file) {
                showToast('Por favor seleccione un archivo PDF', 'error');
                return;
            }

            // Deshabilitar botón y mostrar loading
            fillButton.disabled = true;
            buttonText.textContent = 'Extrayendo metadata...';
            
            const formData = new FormData();
            formData.append('pdf', file);

            try {
                const response = await fetch('{{ route("extract.article") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Llenar campos
                    fillFormFields(result.data);
                    
                    // Mostrar confidence badges
                    showConfidenceBadges(result.confidence);
                    
                    // Mostrar issues si existen
                    if (result.issues && result.issues.length > 0) {
                        showIssues(result.issues);
                    }

                    showToast('Formulario pre-llenado correctamente. Revise y edite si es necesario.', 'success');
                } else {
                    showToast(result.message || 'Error al extraer metadata', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error al comunicarse con el servicio de extracción', 'error');
            } finally {
                fillButton.disabled = false;
                buttonText.textContent = 'Llenar con IA';
            }
        });

        function fillFormFields(data) {
            Object.keys(data).forEach(key => {
                const element = document.getElementById(key);
                if (element) {
                    if (key === 'coautores' && Array.isArray(data[key])) {
                        document.getElementById('coautores_text').value = data[key].join('\n');
                    } else if (data[key] !== null && data[key] !== undefined) {
                        element.value = data[key];
                    }
                }
            });
        }

        function showConfidenceBadges(confidence) {
            Object.keys(confidence).forEach(key => {
                const badge = document.getElementById(`confidence-${key}`);
                if (badge) {
                    const score = confidence[key];
                    let color = 'bg-green-100 text-green-800';
                    let label = 'Alta';
                    
                    if (score < 0.5) {
                        color = 'bg-red-100 text-red-800';
                        label = 'Baja';
                    } else if (score < 0.75) {
                        color = 'bg-yellow-100 text-yellow-800';
                        label = 'Media';
                    }

                    badge.innerHTML = `<span class="text-xs px-2 py-1 rounded ${color}">${label}: ${Math.round(score * 100)}%</span>`;
                }
            });
        }

        function showIssues(issues) {
            issuesList.innerHTML = '';
            issues.forEach(issue => {
                const li = document.createElement('li');
                li.textContent = issue;
                issuesList.appendChild(li);
            });
            issuesContainer.classList.remove('hidden');
        }
    });
    </script>
    @endsection