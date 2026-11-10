@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}" class="text-gold-500 text-decoration-none">Usuarios</a></li>
    <li class="breadcrumb-item active text-white" aria-current="page">Editar Usuario</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Editar Usuario</h1>
            <p class="text-muted mb-0">Actualiza las credenciales, los detalles de perfil y los roles de la cuenta.</p>
        </div>
        <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
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

    <form method="POST" action="{{ route('usuarios.update', $usuario->id) }}">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Sección: Datos de Acceso -->
            <div class="col-12 col-lg-6">
                <div class="card border-0 h-100 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                    <div class="card-header border-0 bg-dark py-3">
                        <h5 class="card-title text-white mb-0"><i class="bi bi-shield-lock-fill text-gold-500 me-2"></i>Datos de Acceso</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label text-white fw-semibold">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control bg-transparent text-white border-secondary @error('name') is-invalid @enderror" value="{{ old('name', $usuario->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label text-white fw-semibold">Correo Electrónico <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control bg-transparent text-white border-secondary @error('email') is-invalid @enderror" value="{{ old('email', $usuario->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label text-white fw-semibold">Contraseña</label>
                                <input type="password" name="password" id="password" class="form-control bg-transparent text-white border-secondary @error('password') is-invalid @enderror" placeholder="Dejar vacío para no cambiar">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label text-white fw-semibold">Confirmar Contraseña</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control bg-transparent text-white border-secondary" placeholder="Dejar vacío para no cambiar">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección: Perfil -->
            <div class="col-12 col-lg-6">
                <div class="card border-0 h-100 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                    <div class="card-header border-0 bg-dark py-3">
                        <h5 class="card-title text-white mb-0"><i class="bi bi-person-badge-fill text-gold-500 me-2"></i>Perfil de Usuario</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="username" class="form-label text-white fw-semibold">Nombre de Usuario (Username)</label>
                            <input type="text" name="username" id="username" class="form-control bg-transparent text-white border-secondary @error('username') is-invalid @enderror" value="{{ old('username', optional($usuario->profile)->username) }}">
                            @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label text-white fw-semibold">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" class="form-control bg-transparent text-white border-secondary @error('telefono') is-invalid @enderror" value="{{ old('telefono', optional($usuario->profile)->telefono) }}">
                            @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="estado" class="form-label text-white fw-semibold">Estado <span class="text-danger">*</span></label>
                            <select name="estado" id="estado" class="form-select bg-transparent text-white border-secondary @error('estado') is-invalid @enderror" required style="background-color: var(--church-surface, #1e1e2d) !important;">
                                <option value="activo" {{ old('estado', optional($usuario->profile)->estado) === 'activo' ? 'selected' : '' }} style="background: #1e1e2d; color: #fff;">Activo</option>
                                <option value="inactivo" {{ old('estado', optional($usuario->profile)->estado) === 'inactivo' ? 'selected' : '' }} style="background: #1e1e2d; color: #fff;">Inactivo</option>
                            </select>
                            @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex gap-3">
            <button type="submit" class="btn btn-primary px-4 py-2">
                <i class="bi bi-save-fill me-1"></i> Actualizar Usuario
            </button>
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary px-4 py-2">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
