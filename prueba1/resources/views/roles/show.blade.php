@extends('layouts.app')

@section('title', 'Detalle de Rol')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <h1 class="sidebar-title" style="font-size:2rem;">Detalle del rol</h1>
        <p class="text-muted mb-0">Información del rol seleccionado.</p>
    </div>
    <a href="{{ route('roles.index') }}" class="btn btn-outline-church">← Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <dl class="row mb-0">
            <dt class="col-sm-3 text-muted">ID</dt>
            <dd class="col-sm-9">{{ $role->id }}</dd>
            <dt class="col-sm-3 text-muted">Nombre</dt>
            <dd class="col-sm-9">{{ $role->name }}</dd>
            <dt class="col-sm-3 text-muted">Guard</dt>
            <dd class="col-sm-9">{{ $role->guard_name ?? 'web' }}</dd>
            <dt class="col-sm-3 text-muted">Creado</dt>
            <dd class="col-sm-9 text-muted">{{ optional($role->created_at)->format('d/m/Y') ?? '—' }}</dd>
            <dt class="col-sm-3 text-muted">Usuarios con este rol</dt>
            <dd class="col-sm-9">{{ $usuariosConRol }}</dd>
        </dl>
        <div class="d-flex gap-2 mt-4">
            @if(!in_array($role->name, ['administrador', 'usuario'], true))
                <form action="{{ route('roles.destroy', $role) }}" method="POST"
                      onsubmit="return confirm('¿Eliminar el rol «{{ addslashes($role->name) }}»?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">Eliminar</button>
                </form>
            @endif
        </div>
    </div>
</div>

@endsection
