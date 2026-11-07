@extends('layouts.app')

@section('title', 'Reporte de Asistencia')
@section('body_class', 'report-page')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}" class="text-gold-500 text-decoration-none">Reportes</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Asistencia</li>
@endsection

@section('content')
@php
    $cursoSeleccionado = $cursoId ? $cursos->firstWhere('id', $cursoId) : null;
    $subtitulo = $cursoSeleccionado ? 'Curso: '.$cursoSeleccionado->nombre : 'Registro de asistencias por curso';

    $periodo = ($desde || $hasta)
        ? trim(($desde ? 'Desde '.\Carbon\Carbon::parse($desde)->format('d/m/Y') : '')
            .($hasta ? ' hasta '.\Carbon\Carbon::parse($hasta)->format('d/m/Y') : ''))
        : 'Todos los registros';

    $estadosAsistencia = [
        'Presente' => 'is-success',
        'Ausente' => 'is-danger',
        'Tardanza' => 'is-warning',
        'Justificado' => 'is-info',
    ];
@endphp

{{-- Interfaz de la aplicaci├│n: filtros y acciones (no se imprime) --}}
<div class="report-controls">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="sidebar-title mb-0" style="color: var(--church-gold);"><i class="bi bi-clipboard-check me-2"></i>Reporte de Asistencia</h2>
        <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    <form method="GET" action="{{ route('reportes.asistencia') }}">
        <div class="row g-3">
            <div class="col-md-4">
                <select name="curso_id" class="form-select">
                    <option value="">-- Curso --</option>
                    @foreach($cursos as $curso)
                        <option value="{{ $curso->id }}" {{ $cursoId == $curso->id ? 'selected' : '' }}>{{ $curso->nombre }}</option>
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
<x-report.document title="Reporte de Asistencia" :subtitle="$subtitulo" :period="$periodo">
    <x-report.summary :items="[
        ['label' => 'Presentes', 'value' => $resumen['presentes'], 'class' => 'is-success'],
        ['label' => 'Ausentes', 'value' => $resumen['ausentes'], 'class' => 'is-danger'],
        ['label' => 'Tardanzas', 'value' => $resumen['tardanzas'], 'class' => 'is-warning'],
        ['label' => 'Justificados', 'value' => $resumen['justificados'], 'class' => 'is-info'],
    ]" />

    <x-report.table :columns="[
        ['label' => 'N.┬║', 'class' => 'center'],
        ['label' => 'Miembro'],
        ['label' => 'Curso'],
        ['label' => 'Sesi├│n'],
        ['label' => 'Fecha', 'class' => 'center'],
        ['label' => 'Estado', 'class' => 'center'],
    ]">
        @forelse($registros as $i => $registro)
            <tr>
                <td class="cell-num">{{ $i + 1 }}</td>
                <td class="cell-strong">{{ $registro->miembro->nombre ?? 'N/A' }}</td>
                <td>{{ $registro->sesion?->curso?->nombre ?? 'N/A' }}</td>
                <td>{{ $registro->sesion?->tema ?? 'N/A' }}</td>
                <td class="center">{{ $registro->sesion?->fecha ? \Carbon\Carbon::parse($registro->sesion->fecha)->format('d/m/Y') : 'ÔÇö' }}</td>
                <td class="center">
                    <span class="report-badge {{ $estadosAsistencia[$registro->estado] ?? 'is-secondary' }}">{{ $registro->estado }}</span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="report-empty">No hay registros de asistencia que coincidan con los filtros.</td>
            </tr>
        @endforelse
    </x-report.table>
</x-report.document>
@endsection
