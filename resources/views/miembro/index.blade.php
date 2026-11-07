@extends('layouts.app')

@section('title', 'Miembros')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <h1 class="sidebar-title" style="font-size:2rem;">Miembros</h1>
        <p class="text-muted mb-0">Administra miembros con b├║squeda, detalle y edici├│n.</p>
    </div>
    <a href="{{ route('miembro.create') }}" class="btn btn-church">
        Nuevo miembro
    </a>
</div>

<form method="GET" action="{{ route('miembro.index') }}" class="mb-3">
    <div class="input-group" style="max-width: 520px;">
        <span class="input-group-text">­ƒöì</span>
        <input type="text" name="buscar" value="{{ $buscar }}" class="form-control" placeholder="Buscar por nombre, apellido o correoÔÇª">
        <button class="btn btn-outline-church" type="submit">Buscar</button>
        @if($buscar)
            <a class="btn btn-outline-secondary" href="{{ route('miembro.index') }}">Ô£ò</a>
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
                    <th style="width: 28%;">Nombre</th>
                    <th>Correo</th>
                    <th style="width: 14%;">Tel├®fono</th>
                    <th style="width: 12%;">Rol</th>
                    <th class="text-end" style="width: 18%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($miembros as $miembro)
                    <tr>
                        <td>{{ $miembro->nombre }} {{ $miembro->apellido }}</td>
                        <td class="text-muted">{{ $miembro->correo }}</td>
                        <td class="text-muted">{{ $miembro->telefono ?? 'ÔÇö' }}</td>
                        <td>
                            <span class="badge" style="background: rgba(212,175,83,.12); color: var(--church-gold); border: 1px solid rgba(212,175,83,.35);">
                                {{ ucfirst($miembro->rol) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('miembro.show', $miembro) }}">Ver</a>
                            <a class="btn btn-sm btn-outline-church" href="{{ route('miembro.edit', $miembro) }}">Editar</a>
                            <form method="POST" action="{{ route('miembro.destroy', $miembro) }}" class="d-inline"
                                  onsubmit="return confirm('┬┐Eliminar este miembro?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">No se encontraron miembros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($miembros->hasPages())
        <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="text-muted small">
                Mostrando {{ $miembros->firstItem() }}ÔÇô{{ $miembros->lastItem() }} de {{ $miembros->total() }} miembros
            </div>
            <div>
                {{ $miembros->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

@endsection
