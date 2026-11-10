@extends('layouts.app')

@section('title', 'Seguimiento')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <div class="text-muted small mb-1" style="letter-spacing:.18em; text-transform:uppercase;">Gestión</div>
        <h1 class="sidebar-title" style="font-size:2rem;">Seguimiento</h1>
        <p class="text-muted mb-0">Registros de avance de actividades.</p>
    </div>
    <a href="{{ route('seguimiento.create') }}" class="btn btn-church">Nuevo seguimiento</a>
</div>

<form method="GET" action="{{ route('seguimiento.index') }}" class="mb-3">
    <div class="input-group" style="max-width: 520px;">
        <span class="input-group-text">­ƒöì</span>
        <input type="text" name="busqueda" value="{{ $busqueda ?? '' }}" class="form-control" placeholder="Buscar seguimiento…">
        <button class="btn btn-outline-church" type="submit">Buscar</button>
        @if($busqueda)
            <a class="btn btn-outline-secondary" href="{{ route('seguimiento.index') }}">✕</a>
        @endif
    </div>
    @if($busqueda)
        <div class="text-muted small mt-2">Resultados para: <strong>"{{ $busqueda }}"</strong></div>
    @endif
</form>

<div class="card shadow-sm">
    @if($seguimientos->isEmpty())
        <div class="card-body py-5 text-center text-muted">
            @if($busqueda)
                No se encontraron seguimientos para <strong>"{{ $busqueda }}"</strong>
            @else
                Aún no hay seguimientos registrados.
            @endif
            @if(!$busqueda)
                <div class="mt-3">
                    <a href="{{ route('seguimiento.create') }}" class="btn btn-outline-church">Crear el primer seguimiento</a>
                </div>
            @endif
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width: 4rem;">#</th>
                        <th>Actividad</th>
                        <th>Fecha</th>
                        <th>Avance</th>
                        <th class="d-none d-md-table-cell">Estado</th>
                        <th class="text-end" style="width: 14rem;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($seguimientos as $seg)
                        @php
                            $estadoStyle = match ($seg->estado) {
                                'pendiente' => 'background:rgba(234,179,8,.15);color:#fcd34d;border:1px solid rgba(234,179,8,.35);',
                                'en_proceso' => 'background:rgba(59,130,246,.15);color:#93c5fd;border:1px solid rgba(59,130,246,.35);',
                                'finalizada' => 'background:rgba(34,197,94,.15);color:#86efac;border:1px solid rgba(34,197,94,.35);',
                                default => 'background:rgba(255,255,255,.06);color:var(--church-muted);border:1px solid var(--church-border);',
                            };
                            $estadoLabel = match ($seg->estado) {
                                'pendiente' => 'Pendiente',
                                'en_proceso' => 'En proceso',
                                'finalizada' => 'Finalizada',
                                default => $seg->estado,
                            };
                        @endphp
                        <tr>
                            <td class="text-muted small">{{ $seg->id }}</td>
                            <td>{{ $seg->actividad->nombre ?? '—' }}</td>
                            <td class="text-muted small">{{ $seg->fecha->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge" style="background: rgba(212,175,83,.12); color: var(--church-gold); border: 1px solid rgba(212,175,83,.35);">
                                    {{ $seg->porcentaje_avance }}%
                                </span>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <span class="badge" style="{{ $estadoStyle }}">{{ $estadoLabel }}</span>
                            </td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('seguimiento.show', $seg) }}">Ver</a>
                                <a class="btn btn-sm btn-outline-church" href="{{ route('seguimiento.edit', $seg) }}">Editar</a>
                                <form method="POST" action="{{ route('seguimiento.destroy', $seg) }}" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar este seguimiento?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($seguimientos->hasPages())
            <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="text-muted small">
                    Mostrando {{ $seguimientos->firstItem() }}–{{ $seguimientos->lastItem() }} de {{ $seguimientos->total() }} registros
                </div>
                <div>{{ $seguimientos->links('pagination::bootstrap-5') }}</div>
            </div>
        @endif
    @endif
</div>

@endsection
