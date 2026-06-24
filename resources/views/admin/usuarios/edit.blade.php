@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    
    {{-- ==========================================
         ENCABEZADO
         ========================================== --}}
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0" style="color: var(--unasam-navy); font-weight: 700;">
                <i class="fas fa-user-edit mr-2 text-muted"></i>Editar usuario
            </h2>
            <a href="{{ url('admin/usuarios') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 6px;">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Formulario centrado para pantallas grandes --}}
        <div class="col-md-8 col-lg-6 mx-auto">
            
            <form action="{{ url('/admin/usuarios', $usuario->id) }}" method="POST" novalidate>
                @csrf
                @method('PUT')

                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header border-0" style="background-color: var(--unasam-navy); color: white;">
                        <h3 class="card-title mb-0 font-weight-bold" style="font-size: 1.05rem;">
                            <i class="fas fa-user-shield mr-2 text-white-50"></i>Actualizar datos registrados
                        </h3>
                    </div>
                    
                    <div class="card-body bg-white p-4">
                        
                        <div class="form-group mb-4">
                            <label for="name" class="text-dark font-weight-bold">Nombre del usuario <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name', $usuario->name) }}" 
                                   class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="email" class="text-dark font-weight-bold">Correo electrónico <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email', $usuario->email) }}" 
                                   class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Campo de Rol incorporado para mantener coherencia con la vista de Creación --}}
                        <div class="form-group mb-4">
                            <label for="rol" class="text-dark font-weight-bold">Rol del Usuario <span class="text-danger">*</span></label>
                            <select name="rol" id="rol" class="form-control @error('rol') is-invalid @enderror" required>
                                <option value="espectador" {{ old('rol', $usuario->rol ?? '') == 'espectador' ? 'selected' : '' }}>
                                    Espectador (Solo lectura)
                                </option>
                                <option value="publicador" {{ old('rol', $usuario->rol ?? '') == 'publicador' ? 'selected' : '' }}>
                                    Publicador (Crear y editar)
                                </option>
                                <option value="super_admin" {{ old('rol', $usuario->rol ?? '') == 'super_admin' ? 'selected' : '' }}>
                                    Súper Administrador (Acceso total)
                                </option>
                            </select>
                            @error('rol')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Mensaje de UX sobre la contraseña --}}
                        <div class="alert alert-light border mt-4 mb-3 py-2 px-3 small text-muted" style="border-radius: 6px;">
                            <i class="fas fa-info-circle text-info mr-1"></i> <strong>¿Cambio de contraseña?</strong> Si no deseas cambiar la contraseña actual de este usuario, simplemente deja los siguientes campos en blanco.
                        </div>

                        {{-- Agrupamos las contraseñas en una sola fila --}}
                        <div class="form-row mb-2">
                            <div class="form-group col-md-6 mb-3">
                                <label for="password" class="text-dark font-weight-bold">Nueva Contraseña</label>
                                <input type="password" id="password" name="password" 
                                       class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group col-md-6 mb-3">
                                <label for="password_confirmation" class="text-dark font-weight-bold">Repita la contraseña</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                            </div>
                        </div>

                    </div>
                    
                    {{-- ==========================================
                         BOTONES DE ACCIÓN
                         ========================================== --}}
                    <div class="card-footer bg-light d-flex justify-content-end p-3 border-top-0">
                        <a href="{{ url('admin/usuarios') }}" class="btn btn-outline-secondary mr-3" style="border-radius: 6px;">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 font-weight-bold" style="border-radius: 6px; background-color: var(--esat-blue);">
                            <i class="bi bi-save2 mr-1"></i> Guardar cambios
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection