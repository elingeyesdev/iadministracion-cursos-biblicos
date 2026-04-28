@extends('layouts.app')

@section('title', 'Asignar roles al usuario')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <h1 class="sidebar-title" style="font-size:2rem;">Asignar roles</h1>
        <p class="text-muted mb-0">
            <strong>{{ $user->name }}</strong>
            <span class="text-muted">·</span>
            {{ $user->email }}
        </p>
    </div>
    <a href="{{ route('asignar-roles.index') }}" class="btn btn-outline-church">← Volver al listado</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('asignar-roles.update', $user) }}">
            @csrf
            @method('PUT')

            <p class="text-muted mb-3">Selecciona el rol del usuario.</p>

            <div class="d-flex flex-column gap-2 mb-4">
                @foreach($roles as $rol)
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="radio"
                            name="role"
                            value="{{ $rol }}"
                            id="rol_{{ $rol }}"
                            @checked(old('role', $user->role ?? 'usuario') === $rol)
                        >
                        <label class="form-check-label" for="rol_{{ $rol }}">
                            <span class="fw-medium" style="color: var(--church-text);">{{ $rol }}</span>
                        </label>
                    </div>
                @endforeach
            </div>

            @error('role')
                <div class="alert alert-danger border-0">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn btn-church">Guardar rol</button>
        </form>
    </div>
</div>

@endsection
