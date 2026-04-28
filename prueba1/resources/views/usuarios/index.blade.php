@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <h1 class="sidebar-title" style="font-size:2rem;">Usuarios</h1>
        <p class="text-muted mb-0">Alta, búsqueda, detalle, edición y baja de cuentas del sistema.</p>
    </div>
    <a href="{{ route('usuarios.create') }}" class="btn btn-church">
        Nuevo usuario
    </a>
</div>

<form method="GET" action="{{ route('usuarios.index') }}" class="mb-3">
    <div class="input-group" style="max-width: 520px;">
        <span class="input-group-text">🔍</span>
        <input type="text" name="buscar" value="{{ $buscar }}" class="form-control" placeholder="Buscar por nombre o correo…">
        <button class="btn btn-outline-church" type="submit">Buscar</button>
        @if($buscar)
            <a class="btn btn-outline-secondary" href="{{ route('usuarios.index') }}">✕</a>
        @endif
    </div>
    @if($buscar)
        <div class="text-muted small mt-2">Resultados para: <strong>"{{ $buscar }}"</strong></div>
    @endif
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-striped mb-0">
            <thead>
                <tr>
                    <th style="width: 24%;">Nombre</th>
                    <th>Correo</th>
                    <th class="text-end" style="width: 22%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td class="text-muted">{{ $u->email }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('usuarios.show', $u) }}">Ver</a>
                            <a class="btn btn-sm btn-outline-church" href="{{ route('usuarios.edit', $u) }}">Editar</a>
                            <form method="POST" action="{{ route('usuarios.destroy', $u) }}" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar al usuario «{{ addslashes($u->name) }}»? Esta acción no se puede deshacer.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-5">No se encontraron usuarios.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($usuarios->hasPages())
        <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="text-muted small">
                Mostrando {{ $usuarios->firstItem() }}–{{ $usuarios->lastItem() }} de {{ $usuarios->total() }} usuarios
            </div>
            <div>
                {{ $usuarios->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

@endsection
