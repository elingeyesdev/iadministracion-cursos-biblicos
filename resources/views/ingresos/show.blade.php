@extends('layouts.app')

@section('title', 'Detalle del Ingreso')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('ingresos.index') }}" class="text-gold-500 text-decoration-none">Ingresos</a></li>
    <li class="breadcrumb-item active text-white" aria-current="page">Detalle</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Detalle del Ingreso</h1>
            <p class="text-muted mb-0">Información detallada del ingreso o diezmo registrado.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('ingresos.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Volver al listado
            </a>
            <a href="{{ route('ingresos.edit', $ingreso->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil-fill me-1"></i> Editar Ingreso
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-header border-0 bg-dark py-3">
                    <h5 class="card-title text-white mb-0"><i class="bi bi-cash-coin text-gold-500 me-2"></i>Información de Transacción</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted fw-semibold">Culto:</div>
                        <div class="col-md-8 text-white fw-bold">
                            @if($ingreso->culto)
                                <a href="{{ route('cultos.show', $ingreso->id_culto) }}" class="text-gold-500 text-decoration-none">
                                    {{ $ingreso->culto->nombre }} ({{ ucfirst($ingreso->culto->dia_semana) }})
                                </a>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted fw-semibold">Monto Total:</div>
                        <div class="col-md-8 text-success fw-bold fs-5">Bs. {{ number_format($ingreso->monto_total, 2) }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 text-muted fw-semibold">Fecha de Recaudación:</div>
                        <div class="col-md-8 text-white">{{ \Carbon\Carbon::parse($ingreso->fecha)->format('d/m/Y') }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 text-muted fw-semibold">Concepto / Tipo:</div>
                        <div class="col-md-8 text-white">{{ $ingreso->tipo ?? 'Ingreso General' }}</div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-4 text-muted fw-semibold">Método de Pago:</div>
                        <div class="col-md-8 text-capitalize">
                            <span class="badge bg-secondary">{{ $ingreso->metodo_pago }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
