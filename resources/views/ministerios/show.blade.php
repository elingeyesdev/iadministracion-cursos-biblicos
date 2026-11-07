@extends('layouts.app')

@section('title', 'Detalle del Ministerio')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('ministerios.index') }}" class="text-gold-500 text-decoration-none">Ministerios</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Detalle</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-md-5">
        <div class="card p-4 shadow-sm h-100">
            <h3 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-diagram-3 me-2"></i>Informaci├│n del Ministerio</h3>

            <table class="table table-borderless">
                <tr>
                    <th class="ps-0 py-2 text-muted" style="width: 35%;">Nombre:</th>
                    <td class="py-2"><strong>{{ $ministerio->nombre }}</strong></td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">L├¡der:</th>
                    <td class="py-2">{{ $ministerio->lider->nombre ?? 'Sin l├¡der asignado' }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Email L├¡der:</th>
                    <td class="py-2">{{ $ministerio->lider->email ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Descripci├│n:</th>
                    <td class="py-2">{{ $ministerio->descripcion ?? 'Sin descripci├│n' }}</td>
                </tr>
            </table>

            <div class="mt-4 d-flex gap-2">
                <a href="{{ route('ministerios.edit', $ministerio->id) }}" class="btn btn-church">
                    <i class="bi bi-pencil me-1"></i> Editar
                </a>
                <a href="{{ route('ministerios.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card p-4 shadow-sm mb-4">
            <h4 class="sidebar-title mb-3" style="color: var(--church-gold);"><i class="bi bi-calendar-event me-2"></i>Actividades</h4>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Actividad</th>
                            <th>Fecha</th>
                            <th>Lugar</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ministerio->actividades as $actividad)
                        <tr>
                            <td><strong>{{ $actividad->nombre }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($actividad->fecha)->format('d/m/Y') }}</td>
                            <td>{{ $actividad->lugar ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $actividad->estado == 'completada' ? 'success' : ($actividad->estado == 'cancelada' ? 'danger' : ($actividad->estado == 'en_curso' ? 'warning text-dark' : 'info')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $actividad->estado)) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">No registra actividades vinculadas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-4 shadow-sm">
            <h4 class="sidebar-title mb-3" style="color: var(--church-gold);"><i class="bi bi-cash-stack me-2"></i>Egresos del Ministerio</h4>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th>Monto</th>
                            <th>Responsable</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ministerio->egresos as $egreso)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($egreso->fecha)->format('d/m/Y') }}</td>
                            <td>{{ $egreso->tipo_egreso }}</td>
                            <td>${{ number_format($egreso->monto, 2) }}</td>
                            <td>{{ $egreso->responsable ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">No registra egresos para este ministerio.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
