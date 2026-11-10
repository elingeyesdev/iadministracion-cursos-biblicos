@extends('layouts.app')

@section('title', 'Nuevo Usuario')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}" class="text-gold-500 text-decoration-none">Usuarios</a></li>
    <li class="breadcrumb-item active text-white" aria-current="page">Nuevo Usuario</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Nuevo Usuario</h1>
            <p class="text-muted mb-0">Crea una cuenta de acceso para el personal de la iglesia.</p>
        </div>
        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 mb-4">
            <h5 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Corrige los siguientes errores:</h5>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(auth()->user()->puede('usuarios', 'crear'))
        <div class="card border-0 shadow-sm" style="max-width: 720px;">
            <div class="card-header border-0 bg-body-secondary py-3">
                <h5 class="card-title mb-0"><i class="bi bi-person-plus-fill text-gold-500 me-2"></i>Datos de la Cuenta</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('usuarios.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Nombre Completo <span class="text-danger">*</span></label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror"
                               pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]+" title="Solo letras y espacios"
                               oninput="soloLetras(this)" required autocomplete="name">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Correo Electrónico <span class="text-danger">*</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror"
                               required autocomplete="email">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label fw-semibold">Contraseña <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input id="password" type="password" name="password"
                                       class="form-control pe-5 @error('password') is-invalid @enderror"
                                       required autocomplete="new-password">
                                <button type="button" class="btn-toggle-password position-absolute top-50 end-0 translate-middle-y me-2"
                                        onclick="togglePasswordVisibility('password', this)" aria-label="Mostrar u ocultar contraseña">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password-confirm" class="form-label fw-semibold">Confirmar Contraseña <span class="text-danger">*</span></label>
                            <input id="password-confirm" type="password" name="password_confirmation"
                                   class="form-control" required autocomplete="new-password">
                        </div>
                    </div>

                    <p class="text-muted small">La contraseña debe tener al menos 12 caracteres. Los roles se asignan desde «Roles Usuario».</p>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-church px-4">
                            <i class="bi bi-save-fill me-1"></i> Crear Usuario
                        </button>
                        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function soloLetras(input) {
        input.value = input.value.replace(/[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]/g, '');
    }

    function togglePasswordVisibility(id, button) {
        const input = document.getElementById(id);
        const icon = button ? button.querySelector('i') : null;

        if (!input) {
            return;
        }

        const visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';

        if (icon) {
            icon.classList.toggle('bi-eye', visible);
            icon.classList.toggle('bi-eye-slash', !visible);
        }
    }
</script>
@endsection
