@extends('layouts.app')

@section('title', 'Reporte Financiero')
@section('body_class', 'report-page')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}" class="text-gold-500 text-decoration-none">Reportes</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Financiero</li>
@endsection

@section('content')
@php
    $periodo = 'Desde '.\Carbon\Carbon::parse($desde)->format('d/m/Y').' hasta '.\Carbon\Carbon::parse($hasta)->format('d/m/Y');
@endphp

{{-- Interfaz de la aplicaci├│n: filtros y acciones (no se imprime) --}}
<div class="report-controls">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="sidebar-title mb-0" style="color: var(--church-gold);"><i class="bi bi-cash-coin me-2"></i>Reporte Financiero</h2>
        <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    <form method="GET" action="{{ route('reportes.financiero') }}">
        <div class="row g-3">
            <div class="col-md-6">
                <input type="date" name="desde" class="form-control" value="{{ $desde }}" title="Desde">
            </div>
            <div class="col-md-6">
                <input type="date" name="hasta" class="form-control" value="{{ $hasta }}" title="Hasta">
            </div>
        </div>

        <x-report.actions class="mt-3" />
    </form>
</div>

{{-- Documento del reporte (es lo ├║nico que se imprime/exporta) --}}
<x-report.document title="Reporte Financiero" subtitle="Ingresos, egresos y balance del per├¡odo" :period="$periodo">
    <x-report.summary :items="[
        ['label' => 'Ingresos', 'value' => 'Bs. '.number_format($totalIngresos, 2), 'class' => 'is-success'],
        ['label' => 'Egresos', 'value' => '$'.number_format($totalEgresos, 2), 'class' => 'is-danger'],
        ['label' => 'Balance', 'value' => ($balance >= 0 ? '+' : '').number_format($balance, 2), 'class' => $balance >= 0 ? 'is-success' : 'is-danger'],
    ]" />

    <div class="report-grid">
        <div class="report-block">
            <h3 class="report-block-title"><i class="bi bi-arrow-down-circle"></i> Ingresos (Bs.)</h3>
            <x-report.table :columns="[
                ['label' => 'Fecha', 'class' => 'center'],
                ['label' => 'Tipo'],
                ['label' => 'M├®todo'],
                ['label' => 'Monto', 'class' => 'num'],
            ]">
                @forelse($ingresos as $ingreso)
                    <tr>
                        <td class="center">{{ \Carbon\Carbon::parse($ingreso->fecha)->format('d/m/Y') }}</td>
                        <td>{{ $ingreso->tipo ?? 'Ingreso General' }}</td>
                        <td class="text-capitalize">{{ $ingreso->metodo_pago }}</td>
                        <td class="num">Bs. {{ number_format($ingreso->monto_total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="report-empty">Sin ingresos en el per├¡odo.</td>
                    </tr>
                @endforelse
            </x-report.table>

            @if($ingresosPorTipo->isNotEmpty())
                <div class="report-note">
                    <strong>Por tipo:</strong>
                    @foreach($ingresosPorTipo as $tipo => $total)
                        {{ $tipo ?: 'General' }}: Bs. {{ number_format($total, 2) }}@if(!$loop->last) ┬À @endif
                    @endforeach
                </div>
            @endif
        </div>

        <div class="report-block">
            <h3 class="report-block-title"><i class="bi bi-arrow-up-circle"></i> Egresos ($)</h3>
            <x-report.table :columns="[
                ['label' => 'Fecha', 'class' => 'center'],
                ['label' => 'Tipo de egreso'],
                ['label' => 'Responsable'],
                ['label' => 'Monto', 'class' => 'num'],
            ]">
                @forelse($egresos as $egreso)
                    <tr>
                        <td class="center">{{ \Carbon\Carbon::parse($egreso->fecha)->format('d/m/Y') }}</td>
                        <td>{{ $egreso->tipo_egreso }}</td>
                        <td>{{ $egreso->responsable ?? 'N/A' }}</td>
                        <td class="num">${{ number_format($egreso->monto, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="report-empty">Sin egresos en el per├¡odo.</td>
                    </tr>
                @endforelse
            </x-report.table>

            @if($egresosPorTipo->isNotEmpty())
                <div class="report-note">
                    <strong>Por tipo:</strong>
                    @foreach($egresosPorTipo as $tipo => $total)
                        {{ $tipo }}: ${{ number_format($total, 2) }}@if(!$loop->last) ┬À @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-report.document>
@endsection
