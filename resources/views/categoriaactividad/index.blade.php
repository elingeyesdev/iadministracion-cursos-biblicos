@extends('layouts.app')

@section('title', 'Categor├¡as de Actividad')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <div class="text-muted small mb-1" style="letter-spacing:.18em; text-transform:uppercase;">Gesti├│n</div>
        <h1 class="sidebar-title" style="font-size:2rem;">Categor├¡as de actividad</h1>
        <p class="text-muted mb-0">Organiza las actividades por categor├¡a.</p>
    </div>
    <a href="{{ route('categoriaactividad.create') }}" class="btn btn-church">Nueva categor├¡a</a>
</div>

<form method="GET" action="{{ route('categoriaactividad.index') }}" class="mb-3">
    <div class="input-group" style="max-width: 520px;">
        <span class="input-group-text">­ƒöì</span>
        <input type="text" name="busqueda" value="{{ $busqueda ?? '' }}" class="form-control" placeholder="Buscar categor├¡aÔÇª">
        <button class="btn btn-outline-church" type="submit">Buscar</button>
        @if($busqueda)
            <a class="btn btn-outline-secondary" href="{{ route('categoriaactividad.index') }}">Ô£ò</a>
        @endif
    </div>
    @if($busqueda)
        <div class="text-muted small mt-2">Resultados para: <strong>"{{ $busqueda }}"</strong></div>
    @endif
</form>

<div class="card shadow-sm">
    @if($categorias->isEmpty())
        <div class="card-body py-5 text-center text-muted">
            @if($busqueda)
                No se encontraron categor├¡as para <strong>"{{ $busqueda }}"</strong>
            @else
                A├║n no hay categor├¡as registradas.
            @endif
            @if(!$busqueda)
                <div class="mt-3">
                    <a href="{{ route('categoriaactividad.create') }}" class="btn btn-outline-church">Crear la primera categor├¡a</a>
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
                        <th class="d-none d-md-table-cell">Descripci├│n</th>
                        <th>Estado</th>
                        <th class="d-none d-lg-table-cell">Creado</th>
                        <th class="text-end" style="width: 14rem;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categorias as $cat)
                        <tr>
                            <td class="text-muted small">{{ $cat->id }}</td>
                            <td>{{ $cat->nombre }}</td>
                            <td class="text-muted small d-none d-md-table-cell" style="max-width: 14rem;">
                                {{ $cat->descripcion ? \Illuminate\Support\Str::limit($cat->descripcion, 65) : 'ÔÇö' }}
                            </td>
                            <td>
                                @if($cat->estado)
                                    <span class="badge" style="background: rgba(34,197,94,.15); color: #86efac; border: 1px solid rgba(34,197,94,.35);">Activa</span>
                                @else
                                    <span class="badge" style="background: rgba(255,255,255,.06); color: var(--church-muted); border: 1px solid var(--church-border);">Inactiva</span>
                                @endif
                            </td>
                            <td class="text-muted small d-none d-lg-table-cell">{{ $cat->created_at->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('categoriaactividad.show', $cat) }}">Ver</a>
                                <a class="btn btn-sm btn-outline-church" href="{{ route('categoriaactividad.edit', $cat) }}">Editar</a>
                                <form method="POST" action="{{ route('categoriaactividad.destroy', $cat) }}" class="d-inline"
                                      onsubmit="return confirm('┬┐Eliminar la categor├¡a ┬½{{ addslashes($cat->nombre) }}┬╗?')">
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
        @if($categorias->hasPages())
            <div class="card-body py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="text-muted small">
                    Mostrando {{ $categorias->firstItem() }}ÔÇô{{ $categorias->lastItem() }} de {{ $categorias->total() }} categor├¡as
                </div>
                <div>{{ $categorias->links('pagination::bootstrap-5') }}</div>
            </div>
        @endif
    @endif
</div>

@endsection
