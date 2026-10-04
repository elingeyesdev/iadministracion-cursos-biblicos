@extends('layouts.app')

@section('title', 'Reporte de Asistencia')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}" class="text-gold-500 text-decoration-none">Reportes</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Asistencia</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="sidebar-title mb-0" style="color: var(--church-gold);"><i class="bi bi-clipboard-check me-2"></i>Reporte de Asistencia</h2>
        <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    <form method="GET" action="{{ route('reportes.asistencia') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <select name="curso_id" class="form-select">
                <option value="">-- Curso --</option>
                @foreach($cursos as $curso)
                    <option value="{{ $curso->id }}" {{ $cursoId == $curso->id ? 'selected' : '' }}>{{ $curso->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" name="desde" class="form-control" value="{{ $desde }}" title="Desde">
        </div>
        <div class="col-md-3">
            <input type="date" name="hasta" class="form-control" value="{{ $hasta }}" title="Hasta">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-church w-100"><i class="bi bi-funnel me-1"></i> Filtrar</button>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="p-3 rounded border text-center" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <div class="text-muted small text-uppercase">Presentes</div>
                <div class="fs-4 fw-bold text-success">{{ $resumen['presentes'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 rounded border text-center" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <div class="text-muted small text-uppercase">Ausentes</div>
                <div class="fs-4 fw-bold text-danger">{{ $resumen['ausentes'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 rounded border text-center" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <div class="text-muted small text-uppercase">Tardanzas</div>
                <div class="fs-4 fw-bold text-warning">{{ $resumen['tardanzas'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="p-3 rounded border text-center" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <div class="text-muted small text-uppercase">Justificados</div>
                <div class="fs-4 fw-bold text-info">{{ $resumen['justificados'] }}</div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Miembro</th>
                    <th>Curso</th>
                    <th>Sesión</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registros as $registro)
                <tr>
                    <td><strong>{{ $registro->miembro->nombre ?? 'N/A' }}</strong></td>
                    <td>{{ $registro->sesion?->curso?->nombre ?? 'N/A' }}</td>
                    <td>{{ $registro->sesion?->tema ?? 'N/A' }}</td>
                    <td>{{ isset($registro->sesion->fecha) ? \Carbon\Carbon::parse($registro->sesion->fecha)->format('d/m/Y') : 'N/A' }}</td>
                    <td>
                        @php
                            $colores = ['Presente' => 'success', 'Ausente' => 'danger', 'Tardanza' => 'warning', 'Justificado' => 'info'];
                        @endphp
                        <span class="badge bg-{{ $colores[$registro->estado] ?? 'secondary' }}">{{ $registro->estado }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No hay registros de asistencia que coincidan con los filtros.</td>
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