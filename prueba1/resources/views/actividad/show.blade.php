@extends('layouts.app')

@section('title', 'Ver Actividad')

@section('content')

@php
    $estadoStyle = match ($actividad->estado) {
        'pendiente' => 'background:rgba(234,179,8,.15);color:#fcd34d;border:1px solid rgba(234,179,8,.35);',
        'en_proceso' => 'background:rgba(59,130,246,.15);color:#93c5fd;border:1px solid rgba(59,130,246,.35);',
        'finalizada' => 'background:rgba(34,197,94,.15);color:#86efac;border:1px solid rgba(34,197,94,.35);',
        default => 'background:rgba(255,255,255,.06);color:var(--church-muted);border:1px solid var(--church-border);',
    };
    $estadoLabel = match ($actividad->estado) {
        'pendiente' => 'Pendiente',
        'en_proceso' => 'En proceso',
        'finalizada' => 'Finalizada',
        default => $actividad->estado,
    };
@endphp

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <h1 class="sidebar-title" style="font-size:2rem;">{{ $actividad->nombre }}</h1>
        <p class="text-muted mb-0">Detalle de la actividad.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('actividad.edit', $actividad) }}" class="btn btn-church">Editar</a>
        <a href="{{ route('actividad.index') }}" class="btn btn-outline-church">← Volver</a>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <span class="text-muted small font-monospace">ID #{{ $actividad->id }}</span>
            <span class="badge" style="{{ $estadoStyle }}">{{ $estadoLabel }}</span>
        </div>
        <dl class="row mb-0">
            <dt class="col-sm-3 text-muted">Categoría</dt>
            <dd class="col-sm-9">
                @if($actividad->categoria)
                    <span class="badge" style="background: rgba(212,175,83,.12); color: var(--church-gold); border: 1px solid rgba(212,175,83,.35);">
                        {{ $actividad->categoria->nombre }}
                    </span>
                @else
                    <span class="text-muted">Sin categoría</span>
                @endif
            </dd>
            <dt class="col-sm-3 text-muted">Responsable</dt>
            <dd class="col-sm-9">{{ $actividad->responsable->nombre ?? '—' }}</dd>
            <dt class="col-sm-3 text-muted">Fecha de inicio</dt>
            <dd class="col-sm-9">{{ $actividad->fecha_inicio->format('d/m/Y') }}</dd>
            <dt class="col-sm-3 text-muted">Fecha de fin</dt>
            <dd class="col-sm-9">{{ $actividad->fecha_fin ? $actividad->fecha_fin->format('d/m/Y') : '—' }}</dd>
            <dt class="col-sm-3 text-muted">Descripción</dt>
            <dd class="col-sm-9">{{ $actividad->descripcion ?: '—' }}</dd>
            <dt class="col-sm-3 text-muted">Creado</dt>
            <dd class="col-sm-9 text-muted">{{ $actividad->created_at->format('d/m/Y H:i') }}</dd>
            <dt class="col-sm-3 text-muted">Actualizado</dt>
            <dd class="col-sm-9 text-muted">{{ $actividad->updated_at->format('d/m/Y H:i') }}</dd>
        </dl>
    </div>
</div>

<div class="card shadow-sm border-danger border-opacity-50">
    <div class="card-body p-4">
        <div class="text-muted small mb-2" style="letter-spacing:.18em; text-transform:uppercase;">Zona de peligro</div>
        <p class="text-muted mb-3">Una vez eliminada, esta acción no se puede deshacer.</p>
        <form method="POST" action="{{ route('actividad.destroy', $actividad) }}"
              onsubmit="return confirm('¿Seguro que deseas eliminar «{{ addslashes($actividad->nombre) }}»?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">Eliminar actividad</button>
        </form>
    </div>
</div>

@endsection
