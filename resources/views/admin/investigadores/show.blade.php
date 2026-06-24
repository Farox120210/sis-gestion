@extends('layouts.admin')

@section('content')
    {{-- Título y botones de acción --}}
    <div class="row mb-3">
        <div class="col-md-6 d-flex align-items-center">
            <h1 class="h3 mb-0">Detalle del Investigador</h1>
        </div>
        <div class="col-md-6 d-flex justify-content-end align-items-center">
            <a href="{{ route('investigadores.index') }}" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left"></i> Volver
            </a>

            <a href="{{ route('investigadores.edit', $inv->ID_INVESTIGADOR) }}" class="btn btn-warning me-2">
                <i class="bi bi-pencil"></i> Editar
            </a>

            <form action="{{ route('investigadores.destroy', $inv->ID_INVESTIGADOR) }}" method="POST"
                  class="d-inline"
                  onsubmit="return confirm('¿Seguro que deseas eliminar este investigador?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </form>
        </div>
    </div>


    {{-- Datos personales + Perfil de investigación --}}
    <div class="row mb-4">
        {{-- Datos personales --}}
        <div class="col-md-6">
            <div class="card card-outline card-primary h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0">Datos personales</h3>
                </div>
                <div class="card-body">
                    <p><strong>Nombres:</strong> {{ $inv->NOMBRES }}</p>
                    <p><strong>Apellidos:</strong> {{ $inv->APELLIDOS }}</p>

                    <p><strong>Tipo de Investigador:</strong> {{ $inv->TIPO_INVESTIGADOR ?? '-' }}</p>
                    <p><strong>Correo institucional:</strong> {{ $inv->CORREO_PERSONAL ?? '-' }}</p>
                    <p><strong>Teléfono:</strong> {{ $inv->TELEFONO ?? '-' }}</p>
                    <p><strong>Dirección:</strong> {{ $inv->DIRECCION ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Perfil de investigación --}}
        <div class="col-md-6">
            <div class="card card-outline card-info h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0">Perfil de investigación</h3>
                </div>
                <div class="card-body">
                    <p><strong>ORCID:</strong> {{ $inv->cod_orcid ?? '-' }}</p>
                    <p><strong>Grado académico:</strong> {{ $inv->grado_academico ?? '-' }}</p>
                    <p><strong>Institución:</strong> {{ $inv->institucion ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Grupos de investigación --}}
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title mb-0">Grupos de investigación</h3>
        </div>
        <div class="card-body table-responsive">
            @if ($grupos->isEmpty())
                <p>Este investigador no pertenece a ningún grupo.</p>
            @else
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Grupo</th>
                            <th>Fecha de ingreso</th>
                            <th>Fecha de salida</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($grupos as $g)
                            <tr>
                                <td>{{ $g->NOMBRE_GRUPO }}</td>
                                <td>{{ $g->FECHA_INGRESO ?? '-' }}</td>
                                <td>{{ $g->FECHA_SALIDA ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
