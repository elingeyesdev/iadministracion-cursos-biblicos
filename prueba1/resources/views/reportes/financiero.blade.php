@extends('layouts.app')

@section('title', 'Reporte Financiero')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}" class="text-gold-500 text-decoration-none">Reportes</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Financiero</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="sidebar-title mb-0" style="color: var(--church-gold);"><i class="bi bi-cash-coin me-2"></i>Reporte Financiero</h2>
        <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    <form method="GET" action="{{ route('reportes.financiero') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="date" name="desde" class="form-control" value="{{ $desde }}" title="Desde">
        </div>
        <div class="col-md-4">
            <input type="date" name="hasta" class="form-control" value="{{ $hasta }}" title="Hasta">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-church w-100"><i class="bi bi-funnel me-1"></i> Filtrar</button>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="p-3 rounded border text-center" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <div class="text-muted small text-uppercase">Ingresos</div>
                <div class="fs-4 fw-bold text-success">Bs. {{ number_format($totalIngresos, 2) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 rounded border text-center" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <div class="text-muted small text-uppercase">Egresos</div>
                <div class="fs-4 fw-bold text-danger">${{ number_format($totalEgresos, 2) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 rounded border text-center" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <div class="text-muted small text-uppercase">Balance</div>
                <div class="fs-4 fw-bold {{ $balance >= 0 ? 'text-success' : 'text-danger' }}">{{ $balance >= 0 ? '+' : '' }}{{ number_format($balance, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <h5 class="fw-bold mb-3"><i class="bi bi-arrow-down-circle text-success me-1"></i> Ingresos (Bs.)</h5>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Método</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ingresos as $ingreso)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($ingreso->fecha)->format('d/m/Y') }}</td>
                            <td>{{ $ingreso->tipo ?? 'Ingreso General' }}</td>
                            <td class="text-capitalize">{{ $ingreso->metodo_pago }}</td>
                            <td class="text-end text-success fw-bold">Bs. {{ number_format($ingreso->monto_total, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Sin ingresos en el período.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($ingresosPorTipo->isNotEmpty())
            <div class="mt-3 small">
                <strong>Por tipo:</strong>
                @foreach($ingresosPorTipo as $tipo => $total)
                    <span class="badge bg-success me-1">{{ $tipo ?: 'General' }}: Bs. {{ number_format($total, 2) }}</span>
                @endforeach
            </div>
            @endif
        </div>

        <div class="col-lg-6">
            <h5 class="fw-bold mb-3"><i class="bi bi-arrow-up-circle text-danger me-1"></i> Egresos ($)</h5>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo de Egreso</th>
                            <th>Responsable</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($egresos as $egreso)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($egreso->fecha)->format('d/m/Y') }}</td>
                            <td>{{ $egreso->tipo_egreso }}</td>
                            <td>{{ $egreso->responsable ?? 'N/A' }}</td>
                            <td class="text-end text-danger fw-bold">${{ number_format($egreso->monto, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Sin egresos en el período.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($egresosPorTipo->isNotEmpty())
            <div class="mt-3 small">
                <strong>Por tipo:</strong>
                @foreach($egresosPorTipo as $tipo => $total)
                    <span class="badge bg-danger me-1">{{ $tipo }}: ${{ number_format($total, 2) }}</span>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Imprimir
        </button>
    </div>
</div>
@endsection