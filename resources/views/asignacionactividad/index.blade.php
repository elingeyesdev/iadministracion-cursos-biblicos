@extends('layouts.app')

@section('title', 'Asignación de Actividades')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <div class="text-muted small mb-1" style="letter-spacing:.18em; text-transform:uppercase;">Gestión</div>
        <h1 class="sidebar-title" style="font-size:2rem;">Asignación de actividades</h1>
        <p class="text-muted mb-0">Asigna miembros a actividades.</p>
    </div>
    <a href="{{ route('asignacionactividad.create') }}" class="btn btn-church">Nueva asignación</a>
</div>

<form method="GET" action="{{ route('asignacionactividad.index') }}" class="mb-3">
    <div class="input-group" style="max-width: 520px;">
        <span class="input-group-text">­ƒöì</span>
        <input type="text" name="busqueda" value="{{ $busqueda ?? '' }}" class="form-control" placeholder="Buscar asignación…">
        <button class="btn btn-outline-church" type="submit">Buscar</button>
        @if($busqueda)
            <a class="btn btn-outline-secondary" href="{{ route('asignacionactividad.index') }}">✕</a>
        @endif
    </div>
    @if($busqueda)
        <div class="text-muted small mt-2">Resultados para: <strong>"{{ $busqueda }}"</strong></div>
    @endif
</form>

<div class="card shadow-sm">
    @if($asignaciones->isEmpty())
        <div class="card-body py-5 text-center text-muted">
            @if($busqueda)
                No se encontraron asignaciones para <strong>"{{ $busqueda }}"</strong>
            @else
                Aún no hay asignaciones registradas.
            @endif
            @if(!$busqueda)
                <div class="mt-3">
                    <a href="{{ route('asignacionactividad.create') }}" class="btn btn-outline-church">Crear la primera asignación</a>
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
                        <th>Miembro</th>
                        <th class="d-none d-md-table-cell">Rol</th>
                        <th class="d-none d-lg-table-cell">Fecha</th>
                        <th class="text-end" style="width: 14rem;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($asignaciones as $asig)
                        <tr>
                            <td class="text-muted small">{{ $asig->id }}</td>
                            <td>{{ $asig->actividad->nombre ?? '—' }}</td>
                            <td>{{ ($asig->miembro->nombre ?? '') }} {{ ($asig->miembro->apellido ?? '') }}</td>
                            <td class="text-muted d-none d-md-table-cell">{{ $asig->rol_en_actividad ?? '—' }}</td>
                            <td class="text-muted small d-none d-lg-table-cell">
                                {{ $asig->fecha_asignacion ? $asig->fecha_asignacion->format('d/m/Y') : '—' }}
                            </td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('asignacionactividad.show', $asig) }}">Ver</a>
                                <a class="btn btn-sm btn-outline-church" href="{{ route('asignacionactividad.edit', $asig) }}">Editar</a>
                                <form method="POST" action="{{ route('asignacionactividad.destroy', $asig) }}" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar esta asignación?')">
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
        @if($asignaciones->hasPages())
            <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="text-muted small">
                    Mostrando {{ $asignaciones->firstItem() }}–{{ $asignaciones->lastItem() }} de {{ $asignaciones->total() }} asignaciones
                </div>
                <div>{{ $asignaciones->links('pagination::bootstrap-5') }}</div>
            </div>
        @endif
    @endif
</div>

@endsection
