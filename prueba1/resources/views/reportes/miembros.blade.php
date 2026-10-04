@extends('layouts.app')

@section('title', 'Reporte de Miembros')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}" class="text-gold-500 text-decoration-none">Reportes</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Miembros</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="sidebar-title mb-0" style="color: var(--church-gold);"><i class="bi bi-people me-2"></i>Reporte de Miembros</h2>
        <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    <form method="GET" action="{{ route('reportes.miembros') }}" class="row g-3 mb-4">
        <div class="col-md-3">
            <select name="estado" class="form-select">
                <option value="">-- Estado --</option>
                @foreach(['activo', 'inactivo', 'visita'] as $e)
                    <option value="{{ $e }}" {{ $estado === $e ? 'selected' : '' }}>{{ ucfirst($e) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" name="desde" class="form-control" value="{{ $desde }}" title="Desde">
        </div>
        <div class="col-md-3">
            <input type="date" name="hasta" class="form-control" value="{{ $hasta }}" title="Hasta">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-church w-100"><i class="bi bi-funnel me-1"></i> Filtrar</button>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="p-3 rounded border text-center" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <div class="text-muted small text-uppercase">Total</div>
                <div class="fs-3 fw-bold">{{ $totales['total'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 rounded border text-center" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <div class="text-muted small text-uppercase">Activos</div>
                <div class="fs-3 fw-bold text-success">{{ $totales['activos'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 rounded border text-center" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <div class="text-muted small text-uppercase">Inactivos</div>
                <div class="fs-3 fw-bold text-danger">{{ $totales['inactivos'] }}</div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Fecha de Ingreso</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($miembros as $miembro)
                <tr>
                    <td><strong>{{ $miembro->nombre }}</strong></td>
                    <td>{{ $miembro->telefono ?? 'N/A' }}</td>
                    <td>{{ $miembro->fecha_ingreso ? \Carbon\Carbon::parse($miembro->fecha_ingreso)->format('d/m/Y') : 'N/A' }}</td>
                    <td>
                        @if($miembro->estado === 'activo')
                            <span class="badge bg-success">Activo</span>
                        @elseif($miembro->estado === 'visita')
                            <span class="badge bg-info text-dark">Visita</span>
                        @else
                            <span class="badge bg-secondary">Inactivo</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">No hay miembros que coincidan con los filtros.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3 d-flex gap-2">
        <button class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Imprimir
        </button>
    </div>
</div>
@endsection