@extends('layouts.app')

@section('title', 'Detalle del Miembro')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <h1 class="sidebar-title" style="font-size:2rem;">Detalle del Miembro</h1>
        <p class="text-muted mb-0">Información registrada.</p>
    </div>
    <a href="{{ route('miembro.index') }}" class="btn btn-outline-church">← Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <dl class="row mb-0">
            <dt class="col-sm-3 text-muted">Nombre</dt>
            <dd class="col-sm-9">{{ $miembro->nombre }}</dd>

            <dt class="col-sm-3 text-muted">Apellido</dt>
            <dd class="col-sm-9">{{ $miembro->apellido }}</dd>

            <dt class="col-sm-3 text-muted">Correo</dt>
            <dd class="col-sm-9">{{ $miembro->correo }}</dd>

            <dt class="col-sm-3 text-muted">Teléfono</dt>
            <dd class="col-sm-9">{{ $miembro->telefono ?? '—' }}</dd>

            <dt class="col-sm-3 text-muted">Rol</dt>
            <dd class="col-sm-9">
                <span class="badge" style="background: rgba(212,175,83,.12); color: var(--church-gold); border: 1px solid rgba(212,175,83,.35);">
                    {{ ucfirst($miembro->rol) }}
                </span>
            </dd>

            <dt class="col-sm-3 text-muted">Registrado</dt>
            <dd class="col-sm-9 text-muted">{{ $miembro->created_at->format('d/m/Y') }}</dd>
        </dl>

        <div class="d-flex gap-2 mt-4">
            <a href="{{ route('miembro.edit', $miembro) }}" class="btn btn-outline-church">Editar</a>
            <form method="POST" action="{{ route('miembro.destroy', $miembro) }}"
                  onsubmit="return confirm('¿Eliminar este miembro?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">Eliminar</button>
            </form>
        </div>
    </div>
</div>

@endsection