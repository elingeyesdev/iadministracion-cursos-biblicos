@extends('layouts.app')

@section('title', 'Detalle de Asignación')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <h1 class="sidebar-title" style="font-size:2rem;">Detalle de asignación</h1>
        <p class="text-muted mb-0">Información de la asignación.</p>
    </div>
    <a href="{{ route('asignacionactividad.index') }}" class="btn btn-outline-church">← Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <dl class="row mb-0">
            <dt class="col-sm-3 text-muted">ID</dt>
            <dd class="col-sm-9">{{ $asignacion->id }}</dd>
            <dt class="col-sm-3 text-muted">Fecha de asignación</dt>
            <dd class="col-sm-9">{{ $asignacion->fecha_asignacion ? $asignacion->fecha_asignacion->format('d/m/Y') : '—' }}</dd>
            <dt class="col-sm-3 text-muted">Actividad</dt>
            <dd class="col-sm-9">{{ $asignacion->actividad->nombre ?? '—' }}</dd>
            <dt class="col-sm-3 text-muted">Miembro</dt>
            <dd class="col-sm-9">
                {{ ($asignacion->miembro->nombre ?? '') }} {{ ($asignacion->miembro->apellido ?? '') }}
                @if($asignacion->miembro?->correo)
                    <span class="text-muted small">({{ $asignacion->miembro->correo }})</span>
                @endif
            </dd>
            <dt class="col-sm-3 text-muted">Rol en actividad</dt>
            <dd class="col-sm-9">{{ $asignacion->rol_en_actividad ?? '—' }}</dd>
        </dl>
        <div class="d-flex gap-2 mt-4">
            <a href="{{ route('asignacionactividad.edit', $asignacion) }}" class="btn btn-outline-church">Editar</a>
            <form method="POST" action="{{ route('asignacionactividad.destroy', $asignacion) }}"
                  onsubmit="return confirm('¿Eliminar esta asignación?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">Eliminar</button>
            </form>
        </div>
    </div>
</div>

@endsection
