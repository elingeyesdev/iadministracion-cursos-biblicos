@extends('layouts.app')

@section('title', 'Detalle de Culto')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('cultos.index') }}" class="text-gold-500 text-decoration-none">Cultos</a></li>
    <li class="breadcrumb-item active text-white" aria-current="page">{{ $culto->nombre }}</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Detalle del Culto</h1>
            <p class="text-muted mb-0">Informaci├│n del culto y registro hist├│rico de recaudaciones.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('cultos.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
            <a href="{{ route('cultos.edit', $culto->id_culto) }}" class="btn btn-warning">
                <i class="bi bi-pencil-fill me-1"></i> Editar Culto
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Columna Izquierda: Informaci├│n General -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-header border-0 bg-dark py-3">
                    <h5 class="card-title text-white mb-0"><i class="bi bi-calendar-heart text-gold-500 me-2"></i>Datos del Culto</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="text-muted small fw-semibold d-block mb-1">Nombre</label>
                        <h4 class="text-white fw-bold mb-0">{{ $culto->nombre }}</h4>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small fw-semibold d-block mb-1">D├¡a de la semana</label>
                        <span class="text-white text-capitalize fs-5">{{ $culto->dia_semana }}</span>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small fw-semibold d-block mb-1">Hora</label>
                        <span class="text-white fs-5">{{ \Carbon\Carbon::parse($culto->hora)->format('H:i') }} hs</span>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small fw-semibold d-block mb-1">Descripci├│n</label>
                        <p class="text-white mb-0" style="white-space: pre-line;">{{ $culto->descripcion ?? 'Sin descripci├│n registrada.' }}</p>
                    </div>

                    <hr class="border-secondary">

                    <div class="mb-0 bg-success bg-opacity-10 p-3 rounded border border-success border-opacity-20 text-center">
                        <label class="text-success small fw-bold d-block mb-1 text-uppercase">Total Recaudado Hist├│rico</label>
                        <span class="text-success fw-bold fs-3">Bs. {{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Listado de Ingresos de este Culto -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm h-100" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-header border-0 bg-dark py-3">
                    <h5 class="card-title text-white mb-0"><i class="bi bi-cash-coin text-gold-500 me-2"></i>Historial de Ingresos ({{ $culto->ingresos->count() }})</h5>
                </div>
                <div class="card-body px-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="color: #c5c5d2;">
                            <thead class="table-dark" style="background-color: #151521;">
                                <tr>
                                    <th scope="col" class="border-0 px-4">Fecha</th>
                                    <th scope="col" class="border-0">Tipo</th>
                                    <th scope="col" class="border-0">M├®todo de Pago</th>
                                    <th scope="col" class="border-0 text-end px-4">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($culto->ingresos as $ingreso)
                                    <tr>
                                        <td class="px-4 text-white fw-bold">
                                            {{ \Carbon\Carbon::parse($ingreso->fecha)->format('d/m/Y') }}
                                        </td>
                                        <td>{{ $ingreso->tipo ?? 'General' }}</td>
                                        <td class="text-capitalize">{{ $ingreso->metodo_pago ?? 'Otro' }}</td>
                                        <td class="text-end fw-bold text-success px-4">
                                            Bs. {{ number_format($ingreso->monto_total, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4 px-4">No se han registrado ingresos para este culto todav├¡a.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($culto->ingresos->count() > 0)
                                <tfoot>
                                    <tr style="background-color: #151521;">
                                        <td colspan="3" class="px-4 text-white fw-bold text-end">Total Recaudado:</td>
                                        <td class="px-4 text-success fw-bold text-end fs-5">Bs. {{ number_format($total, 2) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
