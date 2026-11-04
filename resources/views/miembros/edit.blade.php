@extends('layouts.app')

@section('title', 'Editar Miembro')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('miembros.index') }}" class="text-gold-500 text-decoration-none">Miembros</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Editar</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <h2 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-pencil-square me-2"></i>Editar Miembro: {{ $miembro->nombre }}</h2>

    <form action="{{ route('miembros.update', $miembro->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre', $miembro->nombre) }}" required placeholder="Ej. Juan Pérez">
                @error('nombre')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $miembro->email) }}" required placeholder="juan@ejemplo.com">
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control @error('telefono') is-invalid @enderror" id="telefono" name="telefono" value="{{ old('telefono', $miembro->telefono) }}" placeholder="Ej. +56912345678" pattern="[0-9+\s\-()]+" title="El teléfono no puede contener letras.">
                @error('telefono')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="f_nacimiento" class="form-label">Fecha de Nacimiento</label>
                <input type="date" class="form-control @error('f_nacimiento') is-invalid @enderror" id="f_nacimiento" name="f_nacimiento" value="{{ old('f_nacimiento', $miembro->f_nacimiento) }}" max="{{ date('Y-m-d') }}">
                @error('f_nacimiento')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="sexo" class="form-label">Sexo</label>
                <select class="form-select @error('sexo') is-invalid @enderror" id="sexo" name="sexo">
                    <option value="" disabled>Seleccione...</option>
                    <option value="M" {{ old('sexo', $miembro->sexo) == 'M' ? 'selected' : '' }}>Masculino</option>
                    <option value="F" {{ old('sexo', $miembro->sexo) == 'F' ? 'selected' : '' }}>Femenino</option>
                    <option value="otro" {{ old('sexo', $miembro->sexo) == 'otro' ? 'selected' : '' }}>Otro</option>
                </select>
                @error('sexo')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                    <option value="activo" {{ old('estado', $miembro->estado) == 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ old('estado', $miembro->estado) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    <option value="visita" {{ old('estado', $miembro->estado) == 'visita' ? 'selected' : '' }}>Visita</option>
                </select>
                @error('estado')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="fecha_ingreso" class="form-label">Fecha de Ingreso</label>
                <input type="date" class="form-control @error('fecha_ingreso') is-invalid @enderror" id="fecha_ingreso" name="fecha_ingreso" value="{{ old('fecha_ingreso', $miembro->fecha_ingreso) }}" max="{{ date('Y-m-d') }}">
                @error('fecha_ingreso')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control @error('direccion') is-invalid @enderror" id="direccion" name="direccion" value="{{ old('direccion', $miembro->direccion) }}" placeholder="Ej. Calle Principal #123, Ciudad">
                @error('direccion')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-church">
                <i class="bi bi-save me-1"></i> Actualizar
            </button>
            <a href="{{ route('miembros.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i> Cancelar
            </a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const telefonoInput = document.getElementById('telefono');
        if (telefonoInput) {
            telefonoInput.addEventListener('input', function (e) {
                // Elimina letras y cualquier carácter que no sea número, espacio, +, -, ( o )
                e.target.value = e.target.value.replace(/[^0-9+\s\-()]/g, '');
            });
        }
    });
</script>
@endsection
