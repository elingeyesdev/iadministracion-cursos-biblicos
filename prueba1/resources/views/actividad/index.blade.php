@extends('layouts.app')

@section('title', 'Actividades')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <div class="text-muted small mb-1" style="letter-spacing:.18em; text-transform:uppercase;">Gestión</div>
        <h1 class="sidebar-title" style="font-size:2rem;">Actividades</h1>
        <p class="text-muted mb-0">Organiza y administra todas las actividades.</p>
    </div>
    <a href="{{ route('actividad.create') }}" class="btn btn-church">Nueva actividad</a>
</div>

<form method="GET" action="{{ route('actividad.index') }}" class="mb-3">
    <div class="input-group" style="max-width: 520px;">
        <span class="input-group-text">🔍</span>
        <input type="text" name="busqueda" value="{{ $busqueda ?? '' }}" class="form-control" placeholder="Buscar actividad…">
        <button class="btn btn-outline-church" type="submit">Buscar</button>
        @if($busqueda)
            <a class="btn btn-outline-secondary" href="{{ route('actividad.index') }}">✕</a>
        @endif
    </div>
    @if($busqueda)
        <div class="text-muted small mt-2">Resultados para: <strong>"{{ $busqueda }}"</strong></div>
    @endif
</form>

<div class="card shadow-sm">
    @if($actividades->isEmpty())
        <div class="card-body py-5 text-center text-muted">
            @if($busqueda)
                No se encontraron actividades para <strong>"{{ $busqueda }}"</strong>
            @else
                Aún no hay actividades registradas.
            @endif
            @if(!$busqueda)
                <div class="mt-3">
                    <a href="{{ route('actividad.create') }}" class="btn btn-outline-church">Crear la primera actividad</a>
                </div>
            @endif
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width: 4rem;">#</th>
                        <th>Nombre</th>
                        <th class="d-none d-md-table-cell">Categoría</th>
                        <th class="d-none d-md-table-cell">Responsable</th>
                        <th class="d-none d-lg-table-cell">Fechas</th>
                        <th>Estado</th>
                        <th class="text-end" style="width: 14rem;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($actividades as $act)
                        @php
                            $estadoStyle = match ($act->estado) {
                                'pendiente' => 'background:rgba(234,179,8,.15);color:#fcd34d;border:1px solid rgba(234,179,8,.35);',
                                'en_proceso' => 'background:rgba(59,130,246,.15);color:#93c5fd;border:1px solid rgba(59,130,246,.35);',
                                'finalizada' => 'background:rgba(34,197,94,.15);color:#86efac;border:1px solid rgba(34,197,94,.35);',
                                default => 'background:rgba(255,255,255,.06);color:var(--church-muted);border:1px solid var(--church-border);',
                            };
                            $estadoLabel = match ($act->estado) {
                                'pendiente' => 'Pendiente',
                                'en_proceso' => 'En proceso',
                                'finalizada' => 'Finalizada',
                                default => $act->estado,
                            };
                        @endphp
                        <tr>
                            <td class="text-muted small">{{ $act->id }}</td>
                            <td>{{ $act->nombre }}</td>
                            <td class="d-none d-md-table-cell">
                                <span class="badge" style="background: rgba(212,175,83,.12); color: var(--church-gold); border: 1px solid rgba(212,175,83,.35);">
                                    {{ $act->categoria->nombre ?? '—' }}
                                </span>
                            </td>
                            <td class="text-muted d-none d-md-table-cell">{{ $act->responsable->nombre ?? '—' }}</td>
                            <td class="text-muted small d-none d-lg-table-cell">
                                <div>Inicio: {{ $act->fecha_inicio->format('d/m/Y') }}</div>
                                @if($act->fecha_fin)
                                    <div>Fin: {{ $act->fecha_fin->format('d/m/Y') }}</div>
                                @endif
                            </td>
                            <td><span class="badge" style="{{ $estadoStyle }}">{{ $estadoLabel }}</span></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('actividad.show', $act) }}">Ver</a>
                                <a class="btn btn-sm btn-outline-church" href="{{ route('actividad.edit', $act) }}">Editar</a>
                                <form method="POST" action="{{ route('actividad.destroy', $act) }}" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar la actividad «{{ addslashes($act->nombre) }}»?')">
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
        @if($actividades->hasPages())
            <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="text-muted small">
                    Mostrando {{ $actividades->firstItem() }}–{{ $actividades->lastItem() }} de {{ $actividades->total() }} actividades
                </div>
                <div>{{ $actividades->links('pagination::bootstrap-5') }}</div>
            </div>
        @endif
    @endif
</div>

@endsection
