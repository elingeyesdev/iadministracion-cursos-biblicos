@extends('layouts.app')

@section('title', 'Detalle del Contrato')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('contratos.index') }}" class="text-gold-500 text-decoration-none">Contratos</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Detalle</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-md-5">
        <div class="card p-4 shadow-sm h-100">
            <h3 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-file-earmark-text me-2"></i>Informaci├│n del Contrato</h3>

            <table class="table table-borderless">
                <tr>
                    <th class="ps-0 py-2 text-muted" style="width: 35%;">Miembro:</th>
                    <td class="py-2"><strong>{{ $contrato->miembro->nombre ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Email Miembro:</th>
                    <td class="py-2">{{ $contrato->miembro->email ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Monto total:</th>
                    <td class="py-2 text-warning fw-bold fs-5">${{ number_format($contrato->monto, 2) }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Fecha Inicio:</th>
                    <td class="py-2">{{ \Carbon\Carbon::parse($contrato->fecha_inicio)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Fecha Fin:</th>
                    <td class="py-2">{{ $contrato->fecha_fin ? \Carbon\Carbon::parse($contrato->fecha_fin)->format('d/m/Y') : 'Vigente' }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Estado:</th>
                    <td class="py-2">
                        @if($contrato->estado == 'activo')
                            <span class="badge bg-success">Activo / Vigente</span>
                        @elseif($contrato->estado == 'finalizado')
                            <span class="badge bg-secondary">Finalizado</span>
                        @else
                            <span class="badge bg-danger">Cancelado</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Descripci├│n:</th>
                    <td class="py-2">{{ $contrato->descripcion ?? 'Sin descripci├│n' }}</td>
                </tr>
            </table>

            <div class="mt-4 d-flex gap-2">
                <a href="{{ route('contratos.edit', $contrato->id) }}" class="btn btn-church">
                    <i class="bi bi-pencil me-1"></i> Editar
                </a>
                <a href="{{ route('contratos.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card p-4 shadow-sm h-100">
            <h4 class="sidebar-title mb-3" style="color: var(--church-gold);"><i class="bi bi-cash-stack me-2"></i>Egresos Relacionados</h4>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo Egreso</th>
                            <th>Monto</th>
                            <th>Responsable</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contrato->egresos as $egreso)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($egreso->fecha)->format('d/m/Y') }}</td>
                            <td><strong>{{ $egreso->tipo_egreso }}</strong></td>
                            <td class="text-danger fw-bold">${{ number_format($egreso->monto, 2) }}</td>
                            <td>{{ $egreso->responsable ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">No hay egresos asociados a este contrato.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
