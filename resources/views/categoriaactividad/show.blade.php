@extends('layouts.app')

@section('title', 'Ver Categor├¡a')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <h1 class="sidebar-title" style="font-size:2rem;">{{ $categoria->nombre }}</h1>
        <p class="text-muted mb-0">Detalle de la categor├¡a.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('categoriaactividad.edit', $categoria) }}" class="btn btn-church">Editar</a>
        <a href="{{ route('categoriaactividad.index') }}" class="btn btn-outline-church">ÔåÉ Volver</a>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <span class="text-muted small font-monospace">ID #{{ $categoria->id }}</span>
            @if($categoria->estado)
                <span class="badge" style="background: rgba(34,197,94,.15); color: #86efac; border: 1px solid rgba(34,197,94,.35);">Activa</span>
            @else
                <span class="badge" style="background: rgba(255,255,255,.06); color: var(--church-muted); border: 1px solid var(--church-border);">Inactiva</span>
            @endif
        </div>
        <dl class="row mb-0">
            <dt class="col-sm-3 text-muted">Nombre</dt>
            <dd class="col-sm-9">{{ $categoria->nombre }}</dd>
            <dt class="col-sm-3 text-muted">Descripci├│n</dt>
            <dd class="col-sm-9">{{ $categoria->descripcion ?: 'ÔÇö' }}</dd>
            <dt class="col-sm-3 text-muted">Creado</dt>
            <dd class="col-sm-9 text-muted">{{ $categoria->created_at->format('d/m/Y H:i') }}</dd>
            <dt class="col-sm-3 text-muted">Actualizado</dt>
            <dd class="col-sm-9 text-muted">{{ $categoria->updated_at->format('d/m/Y H:i') }}</dd>
        </dl>
    </div>
</div>

<div class="card shadow-sm border-danger border-opacity-50">
    <div class="card-body p-4">
        <div class="text-muted small mb-2" style="letter-spacing:.18em; text-transform:uppercase;">Zona de peligro</div>
        <p class="text-muted mb-3">Una vez eliminada, esta acci├│n no se puede deshacer.</p>
        <form method="POST" action="{{ route('categoriaactividad.destroy', $categoria) }}"
              onsubmit="return confirm('┬┐Seguro que deseas eliminar ┬½{{ addslashes($categoria->nombre) }}┬╗?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">Eliminar categor├¡a</button>
        </form>
    </div>
</div>

@endsection
