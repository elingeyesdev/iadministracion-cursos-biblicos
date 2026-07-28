@extends('layouts.app')

@section('title', 'Detalle del Seguimiento')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('seguimientos.index') }}" class="text-gold-500 text-decoration-none">Seguimientos</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Detalle</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <h3 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-journal-check me-2"></i>Detalle de Seguimiento</h3>

    <div class="row g-4">
        <div class="col-md-6">
            <table class="table table-borderless">
                <tr>
                    <th class="ps-0 py-2 text-muted" style="width: 35%;">Actividad:</th>
                    <td class="py-2"><strong>{{ $seguimiento->actividad->nombre ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Fecha Registro:</th>
                    <td class="py-2">{{ \Carbon\Carbon::parse($seguimiento->fecha_registro)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Responsable:</th>
                    <td class="py-2">{{ $seguimiento->responsable ?? 'No asignado' }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Estado Reportado:</th>
                    <td class="py-2"><span class="badge bg-secondary fs-6">{{ $seguimiento->estado }}</span></td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Porcentaje Avance:</th>
                    <td class="py-2">
                        <div class="d-flex align-items-center mt-1">
                            <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $seguimiento->porcentaje_avance }}%" aria-valuenow="{{ $seguimiento->porcentaje_avance }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="font-monospace fw-bold text-warning">{{ number_format($seguimiento->porcentaje_avance, 0) }}%</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="col-md-6">
            <h5 class="text-muted mb-2">Observaciones / Anotaciones:</h5>
            <div class="p-3 rounded" style="background-color: var(--church-surface-2); border: 1px solid var(--church-border);">
                {!! nl2br(e($seguimiento->observacion ?? 'Sin observaciones registradas.')) !!}
            </div>
        </div>
    </div>

    <div class="mt-4 border-top pt-3 d-flex gap-2">
        <a href="{{ route('seguimientos.edit', $seguimiento->id) }}" class="btn btn-church">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('seguimientos.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>
@endsection
