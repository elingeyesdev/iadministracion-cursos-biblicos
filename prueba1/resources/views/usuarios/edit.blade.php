@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <h1 class="sidebar-title" style="font-size:2rem;">Editar usuario</h1>
        <p class="text-muted mb-0">Actualiza nombre, correo o contraseña.</p>
    </div>
    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-church">← Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('usuarios.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Nombre</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" autocomplete="name">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Correo</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" autocomplete="username">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Nueva contraseña (opcional)</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-church">Actualizar</button>
                <a href="{{ route('usuarios.show', $user) }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@endsection
