@extends('layouts.app')

@section('title', 'Detalle del Cargo')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('cargos.index') }}" class="text-gold-500 text-decoration-none">Cargos</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">{{ $cargo->nombre }}</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="sidebar-title mb-0" style="color: var(--church-gold);"><i class="bi bi-briefcase me-2"></i>{{ $cargo->nombre }}</h2>
        <a href="{{ route('cargos.edit', $cargo->id) }}" class="btn btn-outline-warning">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="p-3 rounded border" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <div class="text-muted small text-uppercase">Nivel Jerárquico</div>
                <div class="fs-5 fw-bold">{{ $cargo->nivel_jerarquico }}</div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="p-3 rounded border" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <div class="text-muted small text-uppercase">Descripción</div>
                <div>{{ $cargo->descripcion ?? 'Sin descripción' }}</div>
            </div>
        </div>
    </div>

    <h5 class="fw-bold mb-3"><i class="bi bi-people me-1"></i> Líderes de Iglesia con este Cargo</h5>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Fecha de Inicio</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cargo->lideresIglesia as $lider)
                <tr>
                    <td>{{ $lider->nombre }}</td>
                    <td>{{ $lider->telefono ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($lider->fecha_inicio)->format('d/m/Y') }}</td>
                    <td>
                        @if($lider->activo)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-secondary">Inactivo</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">No hay líderes asignados a este cargo.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection