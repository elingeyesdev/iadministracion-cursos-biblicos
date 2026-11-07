@extends('layouts.app')

@section('title', 'Reporte de Miembros')
@section('body_class', 'report-page')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}" class="text-gold-500 text-decoration-none">Reportes</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Miembros</li>
@endsection

@section('content')
@php
    $periodo = ($desde || $hasta)
        ? trim(($desde ? 'Desde '.\Carbon\Carbon::parse($desde)->format('d/m/Y') : '')
            .($hasta ? ' hasta '.\Carbon\Carbon::parse($hasta)->format('d/m/Y') : ''))
        : 'Todos los registros';
@endphp

{{-- Interfaz de la aplicaci├│n: filtros y acciones (no se imprime) --}}
<div class="report-controls">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="sidebar-title mb-0" style="color: var(--church-gold);"><i class="bi bi-people me-2"></i>Reporte de Miembros</h2>
        <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    <form method="GET" action="{{ route('reportes.miembros') }}">
        <div class="row g-3">
            <div class="col-md-4">
                <select name="estado" class="form-select">
                    <option value="">-- Estado --</option>
                    @foreach(['activo', 'inactivo', 'visita'] as $e)
                        <option value="{{ $e }}" {{ $estado === $e ? 'selected' : '' }}>{{ ucfirst($e) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <input type="date" name="desde" class="form-control" value="{{ $desde }}" title="Desde">
            </div>
            <div class="col-md-4">
                <input type="date" name="hasta" class="form-control" value="{{ $hasta }}" title="Hasta">
            </div>
        </div>

        <x-report.actions class="mt-3" />
    </form>
</div>

{{-- Documento del reporte (es lo ├║nico que se imprime/exporta) --}}
<x-report.document title="Reporte de Miembros" subtitle="Listado y estado de los miembros" :period="$periodo">
    <x-report.summary :items="[
        ['label' => 'Total', 'value' => $totales['total']],
        ['label' => 'Activos', 'value' => $totales['activos'], 'class' => 'is-success'],
        ['label' => 'Inactivos', 'value' => $totales['inactivos'], 'class' => 'is-danger'],
    ]" />

    <x-report.table :columns="[
        ['label' => 'N.┬║', 'class' => 'center'],
        ['label' => 'Nombre completo'],
        ['label' => 'Tel├®fono'],
        ['label' => 'Fecha de ingreso', 'class' => 'center'],
        ['label' => 'Estado', 'class' => 'center'],
    ]">
        @forelse($miembros as $i => $miembro)
            @php
                $estadoClase = match($miembro->estado) {
                    'activo' => 'is-success',
                    'visita' => 'is-info',
                    default => 'is-secondary',
                };
            @endphp
            <tr>
                <td class="cell-num">{{ $i + 1 }}</td>
                <td class="cell-strong">{{ $miembro->nombre }}</td>
                <td>{{ $miembro->telefono ?: 'ÔÇö' }}</td>
                <td class="center">{{ $miembro->fecha_ingreso ? \Carbon\Carbon::parse($miembro->fecha_ingreso)->format('d/m/Y') : 'ÔÇö' }}</td>
                <td class="center">
                    <span class="report-badge {{ $estadoClase }}">{{ $miembro->estado ? strtoupper($miembro->estado) : 'ÔÇö' }}</span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="report-empty">No hay miembros que coincidan con los filtros.</td>
            </tr>
        @endforelse
    </x-report.table>
</x-report.document>
@endsection
