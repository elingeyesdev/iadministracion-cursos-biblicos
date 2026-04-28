@extends('layouts.app')

@section('title', 'Nuevo usuario')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <h1 class="sidebar-title" style="font-size:2rem;">Nuevo usuario</h1>
        <p class="text-muted mb-0">Crea una cuenta con correo y contraseña.</p>
    </div>
    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-church">← Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('usuarios.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Nombre</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" autocomplete="name">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Correo</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" autocomplete="username">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Contraseña</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                </div>
            </div>

            <p class="text-muted small mt-3 mb-0">Los roles se asignan desde el apartado <a href="{{ route('asignar-roles.index') }}" style="color: var(--church-gold);">Asignar roles</a> una vez creada la cuenta.</p>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-church">Guardar</button>
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@endsection
