@extends('layouts.app')

@section('title', 'Nuevo Rol')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}" class="text-gold-500 text-decoration-none">Roles</a></li>
    <li class="breadcrumb-item active text-white" aria-current="page">Nuevo Rol</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Nuevo Rol</h1>
            <p class="text-muted mb-0">Crea un rol con nombre, descripción y estado para asignar permisos en la plataforma.</p>
        </div>
        <a href="{{ route('roles.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 mb-4 bg-danger text-white">
            <h5 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Por favor corrige los siguientes errores:</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important; max-width: 800px;">
        <div class="card-header border-0 bg-dark py-3">
            <h5 class="card-title text-white mb-0"><i class="bi bi-shield-lock-fill text-gold-500 me-2"></i>Detalles del Rol</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('roles.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="nombre" class="form-label text-white fw-semibold">Nombre del Rol <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" id="nombre" class="form-control bg-transparent text-white border-secondary @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" placeholder="Ej: Administrador, Secretario, Tesorero..." required>
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label text-white fw-semibold">Descripción</label>
                    <textarea name="descripcion" id="descripcion" rows="4" class="form-control bg-transparent text-white border-secondary @error('descripcion') is-invalid @enderror" placeholder="Describe brevemente las responsabilidades o permisos asociados a este rol...">{{ old('descripcion') }}</textarea>
                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="estado" class="form-label text-white fw-semibold">Estado <span class="text-danger">*</span></label>
                    <select name="estado" id="estado" class="form-select bg-transparent text-white border-secondary @error('estado') is-invalid @enderror" required style="background-color: var(--church-surface, #1e1e2d) !important;">
                        <option value="activo" {{ old('estado') !== 'inactivo' ? 'selected' : '' }} style="background: #1e1e2d; color: #fff;">Activo</option>
                        <option value="inactivo" {{ old('estado') === 'inactivo' ? 'selected' : '' }} style="background: #1e1e2d; color: #fff;">Inactivo</option>
                    </select>
                    @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mt-4 d-flex gap-3">
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="bi bi-save-fill me-1"></i> Guardar Rol
                    </button>
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary px-4 py-2">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
