@extends('layouts.app')

@section('title', 'Detalle del usuario')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <h1 class="sidebar-title" style="font-size:2rem;">Detalle del usuario</h1>
        <p class="text-muted mb-0">Información de la cuenta.</p>
    </div>
    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-church">← Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <dl class="row mb-0">
            <dt class="col-sm-3 text-muted">Nombre</dt>
            <dd class="col-sm-9">{{ $user->name }}</dd>

            <dt class="col-sm-3 text-muted">Correo</dt>
            <dd class="col-sm-9">{{ $user->email }}</dd>

            <dt class="col-sm-3 text-muted">Correo verificado</dt>
            <dd class="col-sm-9 text-muted">{{ $user->email_verified_at ? $user->email_verified_at->format('d/m/Y H:i') : '—' }}</dd>

            <dt class="col-sm-3 text-muted">Registrado</dt>
            <dd class="col-sm-9 text-muted">{{ $user->created_at->format('d/m/Y') }}</dd>
        </dl>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <a href="{{ route('usuarios.edit', $user) }}" class="btn btn-outline-church">Editar</a>
            <form method="POST" action="{{ route('usuarios.destroy', $user) }}"
                  onsubmit="return confirm('¿Eliminar al usuario «{{ addslashes($user->name) }}»?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">Eliminar</button>
            </form>
        </div>
    </div>
</div>

@endsection
