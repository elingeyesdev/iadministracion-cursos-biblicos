@extends('layouts.app')

@section('title', 'Roles')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <div class="text-muted small mb-1" style="letter-spacing:.18em; text-transform:uppercase;">Seguridad</div>
        <h1 class="sidebar-title" style="font-size:2rem;">Roles</h1>
        <p class="text-muted mb-0">Vista de solo lectura de los roles disponibles en el sistema.</p>
    </div>
</div>

<form method="GET" action="{{ route('roles.index') }}" class="mb-3">
    <div class="input-group" style="max-width: 520px;">
        <span class="input-group-text">🔍</span>
        <input type="text" name="busqueda" value="{{ $busqueda ?? '' }}" class="form-control" placeholder="Buscar rol…">
        <button class="btn btn-outline-church" type="submit">Buscar</button>
        @if($busqueda)
            <a class="btn btn-outline-secondary" href="{{ route('roles.index') }}">✕</a>
        @endif
    </div>
    @if($busqueda)
        <div class="text-muted small mt-2">Resultados para: <strong>"{{ $busqueda }}"</strong></div>
    @endif
</form>

<div class="card shadow-sm">
    @if($roles->isEmpty())
        <div class="card-body py-5 text-center text-muted">
            @if($busqueda)
                No se encontraron roles para <strong>"{{ $busqueda }}"</strong>
            @else
                Aún no hay roles registrados.
            @endif
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th class="d-none d-lg-table-cell">Guard</th>
                        <th class="text-end" style="width: 14rem;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td>{{ $role->name }}</td>
                            <td class="text-muted d-none d-lg-table-cell">{{ $role->guard_name ?? 'web' }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('roles.show', $role) }}">Ver</a>
                                @if(!in_array($role->name, ['administrador', 'usuario'], true))
                                    <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar el rol «{{ addslashes($role->name) }}»?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($roles->hasPages())
            <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="text-muted small">
                    Mostrando {{ $roles->firstItem() }}–{{ $roles->lastItem() }} de {{ $roles->total() }} roles
                </div>
                <div>{{ $roles->links('pagination::bootstrap-5') }}</div>
            </div>
        @endif
    @endif
</div>

@endsection
