@extends('layouts.app')

@section('title', 'Editar Rol')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <h1 class="sidebar-title" style="font-size:2rem;">Editar rol</h1>
        <p class="text-muted mb-0">Actualiza el nombre del rol.</p>
    </div>
    <a href="{{ route('roles.index') }}" class="btn btn-outline-church">← Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('roles.update', $role) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Nombre</label>
                <input type="text" id="name" name="name" value="{{ old('name', $role->name) }}"
                       class="form-control @error('name') is-invalid @enderror">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-church">Actualizar</button>
                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@endsection
