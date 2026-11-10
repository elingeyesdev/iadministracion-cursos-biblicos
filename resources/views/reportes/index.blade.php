@extends('layouts.app')

@section('title', 'Reportes')

@section('breadcrumb')
    <li class="breadcrumb-item active text-muted" aria-current="page">Reportes</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <h2 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-graph-up me-2"></i>Reportes</h2>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="p-4 rounded border text-center h-100" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <i class="bi bi-people display-4 text-warning"></i>
                <h5 class="fw-bold mt-3">Reporte de Miembros</h5>
                <p class="text-muted small">Listado y detalle de miembros según estado y fecha de ingreso.</p>
                <a href="{{ route('reportes.miembros') }}" class="btn btn-church w-100">
                    <i class="bi bi-eye me-1"></i> Ver Reporte
                </a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 rounded border text-center h-100" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <i class="bi bi-clipboard-check display-4 text-warning"></i>
                <h5 class="fw-bold mt-3">Reporte de Asistencia</h5>
                <p class="text-muted small">Resumen de asistencias por curso y por rango de fechas.</p>
                <a href="{{ route('reportes.asistencia') }}" class="btn btn-church w-100">
                    <i class="bi bi-eye me-1"></i> Ver Reporte
                </a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 rounded border text-center h-100" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <i class="bi bi-cash-coin display-4 text-warning"></i>
                <h5 class="fw-bold mt-3">Reporte Financiero</h5>
                <p class="text-muted small">Ingresos, egresos y balance del período seleccionado.</p>
                <a href="{{ route('reportes.financiero') }}" class="btn btn-church w-100">
                    <i class="bi bi-eye me-1"></i> Ver Reporte
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
