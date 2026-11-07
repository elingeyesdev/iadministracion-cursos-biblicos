@extends('layouts.app')

@section('title', 'Detalle del Egreso')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('egresos.index') }}" class="text-gold-500 text-decoration-none">Egresos</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Detalle</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <h3 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-cash-stack me-2"></i>Detalle de Egreso o Gasto</h3>

    <div class="row g-4">
        <div class="col-md-6">
            <table class="table table-borderless">
                <tr>
                    <th class="ps-0 py-2 text-muted" style="width: 35%;">Monto:</th>
                    <td class="py-2 text-danger fw-bold fs-5">${{ number_format($egreso->monto, 2) }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Fecha del Gasto:</th>
                    <td class="py-2">{{ \Carbon\Carbon::parse($egreso->fecha)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Concepto/Tipo:</th>
                    <td class="py-2"><strong>{{ $egreso->tipo_egreso }}</strong></td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Responsable:</th>
                    <td class="py-2">{{ $egreso->responsable ?? 'No registrado' }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">N┬░ Comprobante:</th>
                    <td class="py-2">{{ $egreso->comprobante ?? 'Sin comprobante' }}</td>
                </tr>
            </table>
        </div>

        <div class="col-md-6">
            <table class="table table-borderless">
                <tr>
                    <th class="ps-0 py-2 text-muted" style="width: 35%;">Ministerio:</th>
                    <td class="py-2">{{ $egreso->ministerio->nombre ?? 'Gasto General (Ninguno)' }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Contrato Vinculado:</th>
                    <td class="py-2">
                        @if($egreso->contrato)
                            <a href="{{ route('contratos.show', $egreso->contrato->id) }}" class="text-gold-500 text-decoration-none">
                                Contrato #{{ $egreso->contrato->id }} ({{ $egreso->contrato->miembro->nombre ?? '' }})
                            </a>
                        @else
                            Ninguno
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Descripci├│n:</th>
                    <td class="py-2">{{ $egreso->descripcion ?? 'Sin descripci├│n adicional' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="mt-4 border-top pt-3 d-flex gap-2">
        <a href="{{ route('egresos.edit', $egreso->id) }}" class="btn btn-church">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <a href="{{ route('egresos.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>
@endsection
