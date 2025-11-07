@extends('layouts.admin')

@section('content')
    <div class="row mb-3">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <h1 class="h3">Detalle del Investigador</h1>
            <a href="{{ route('investigadores.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    {{-- Mensajes flash --}}
    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Información del investigador --}}
    <div class="card card-outline card-primary mb-4">
        <div class="card-header">
            <h3 class="card-title">
                {{ $inv->NOMBRES }} {{ $inv->APELLIDOS }}
            </h3>
        </div>
        <div class="card-body">
            <p><strong>Tipo de Investigador:</strong> {{ $inv->TIPO_INVESTIGADOR }}</p>
            <p><strong>Correo institucional:</strong> {{ $inv->CORREO_INSTITUCIONAL ?? '-' }}</p>
            <p><strong>Correo personal:</strong> {{ $inv->CORREO_PERSONAL ?? '-' }}</p>
            <p><strong>Teléfono:</strong> {{ $inv->TELEFONO ?? '-' }}</p>
            <p><strong>Dirección:</strong> {{ $inv->DIRECCION ?? '-' }}</p>
        </div>
        <div class="card-footer">
            <a href="{{ route('investigadores.edit', $inv->ID_INVESTIGADOR) }}" class="btn btn-warning">
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

    {{-- Grupos de investigación --}}
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">Grupos de investigación</h3>
        </div>
        <div class="card-body table-responsive">
            @if ($grupos->isEmpty())
                <p>Este investigador no pertenece a ningún grupo.</p>
            @else
                <table class="table table-bordered table-hover">
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
