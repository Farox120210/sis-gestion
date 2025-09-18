@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="h1">Editar publicación</div>
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
            <form action="{{ route('publicaciones.update', $pub->id_publicacion) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card card-outline card-primary">
                    <div class="card-header ">
                        <h3 class="card-title mb-0">Datos del proyecto</h3>
                        <div class="card-tools">
                            <a href="{{ route('publicaciones.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save2"></i> Guardar cambios
                            </button>
                        </div>
                    </div>

                    <div class="card-body" style="display:block;">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Título *</label>
                                <input type="text" name="titulo" class="form-control"
                                    value="{{ old('titulo', $pub->titulo) }}" maxlength="200" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Año del proyecto *</label>
                                <input type="number" name="anio" class="form-control"
                                    value="{{ old('anio', $pub->anio_proyecto) }}" min="1900" max="{{ date('Y') + 1 }}"
                                    required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control"
                                    value="{{ old('fecha_inicio', $pub->fecha_inicio) }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha fin</label>
                                <input type="date" name="fecha_fin" class="form-control"
                                    value="{{ old('fecha_fin', $pub->fecha_fin) }}">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion_proyecto" class="form-control" rows="3">{{ old('descripcion_proyecto', $pub->descripcion) }}</textarea>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Presupuesto</label>
                                <input type="number" step="0.01" name="presupuesto" class="form-control"
                                    value="{{ old('presupuesto', $pub->presupuesto) }}">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Área de investigación *</label>
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

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Estado del proyecto *</label>
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

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Autor principal *</label>
                                <select name="id_autor" class="form-control" required>
                                    <option value="">— Seleccione —</option>
                                    @foreach ($investigadores as $i)
                                        <option value="{{ $i->ID_INVESTIGADOR }}"
                                            {{ (int) old('id_autor', $pub->id_autor) === (int) $i->ID_INVESTIGADOR ? 'selected' : '' }}>
                                            {{ $i->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Rol (opcional):</small>
                                <input type="text" name="rol_autor" class="form-control"
                                    value="{{ old('rol_autor', $pub->rol_autor) }}" maxlength="50">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Coautores (Ctrl/Cmd para múltiple)</label>
                                <select name="coautores[]" class="form-control" multiple size="6">
                                    @foreach ($investigadores as $i)
                                        <option value="{{ $i->ID_INVESTIGADOR }}"
                                            {{ in_array($i->ID_INVESTIGADOR, old('coautores', $coautorIds)) ? 'selected' : '' }}>
                                            {{ $i->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Grupo de investigación</label>
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

                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Datos de publicación (revista)</h3>
                    </div>
                    <div class="card-body" style="display:block;">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Revista *</label>
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
                                <label class="form-label">Año de publicación *</label>
                                <input type="number" name="anio_publicacion" class="form-control"
                                    value="{{ old('anio_publicacion', $pub->anio_publicacion) }}" min="1900"
                                    max="{{ date('Y') + 1 }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end">
                        <a href="{{ route('publicaciones.index') }}" class="btn btn-outline-secondary mr-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save2"></i> Guardar
                            cambios</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
