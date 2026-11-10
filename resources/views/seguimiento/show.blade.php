@extends('layouts.app')

@section('title', 'Detalle de Seguimiento')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <h1 class="sidebar-title" style="font-size:2rem;">Detalle de seguimiento</h1>
        <p class="text-muted mb-0">Información del avance registrado.</p>
    </div>
    <a href="{{ route('seguimiento.index') }}" class="btn btn-outline-church">← Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <dl class="row mb-0">
            <dt class="col-sm-3 text-muted">ID</dt>
            <dd class="col-sm-9">{{ $seguimiento->id }}</dd>
            <dt class="col-sm-3 text-muted">Fecha</dt>
            <dd class="col-sm-9">{{ $seguimiento->fecha->format('d/m/Y') }}</dd>
            <dt class="col-sm-3 text-muted">Actividad</dt>
            <dd class="col-sm-9">{{ $seguimiento->actividad->nombre ?? '—' }}</dd>
            <dt class="col-sm-3 text-muted">Avance</dt>
            <dd class="col-sm-9">{{ $seguimiento->porcentaje_avance }}%</dd>
            <dt class="col-sm-3 text-muted">Estado</dt>
            <dd class="col-sm-9">{{ str_replace('_', ' ', ucfirst($seguimiento->estado)) }}</dd>
            <dt class="col-sm-3 text-muted">Comentario</dt>
            <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $seguimiento->comentario ?? '—' }}</dd>
        </dl>
        <div class="d-flex gap-2 mt-4">
            <a href="{{ route('seguimiento.edit', $seguimiento) }}" class="btn btn-outline-church">Editar</a>
            <form method="POST" action="{{ route('seguimiento.destroy', $seguimiento) }}"
                  onsubmit="return confirm('¿Eliminar este seguimiento?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">Eliminar</button>
            </form>
        </div>
    </div>
</div>

@endsection
