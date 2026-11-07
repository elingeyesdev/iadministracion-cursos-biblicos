@extends('layouts.app')

@section('title', 'Detalle del L├¡der de Iglesia')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('lideres.index') }}" class="text-gold-500 text-decoration-none">L├¡deres</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">{{ $lider->nombre }}</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="sidebar-title mb-0" style="color: var(--church-gold);"><i class="bi bi-person-badge me-2"></i>{{ $lider->nombre }}</h2>
        <a href="{{ route('lideres.edit', $lider->id) }}" class="btn btn-outline-warning">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="p-3 rounded border" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <div class="text-muted small text-uppercase">Cargo</div>
                <div class="fs-5 fw-bold">{{ $lider->cargo->nombre ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 rounded border" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <div class="text-muted small text-uppercase">Tel├®fono</div>
                <div class="fs-5 fw-bold">{{ $lider->telefono ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 rounded border" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <div class="text-muted small text-uppercase">Fecha de Inicio</div>
                <div class="fs-5 fw-bold">{{ \Carbon\Carbon::parse($lider->fecha_inicio)->format('d/m/Y') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 rounded border" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <div class="text-muted small text-uppercase">Estado</div>
                <div class="fs-5 fw-bold">
                    @if($lider->activo)
                        <span class="badge bg-success">Activo</span>
                    @else
                        <span class="badge bg-secondary">Inactivo</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
