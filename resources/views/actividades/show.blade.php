@extends('layouts.app')

@section('title', 'Detalle de la Actividad')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('actividades.index') }}" class="text-gold-500 text-decoration-none">Actividades</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Detalle</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-md-5">
        <div class="card p-4 shadow-sm h-100">
            <h3 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-calendar-event me-2"></i>Informaci├│n de la Actividad</h3>

            <table class="table table-borderless">
                <tr>
                    <th class="ps-0 py-2 text-muted" style="width: 35%;">Nombre:</th>
                    <td class="py-2"><strong>{{ $actividad->nombre }}</strong></td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Ministerio:</th>
                    <td class="py-2">{{ $actividad->ministerio->nombre ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Fecha:</th>
                    <td class="py-2">{{ \Carbon\Carbon::parse($actividad->fecha)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Lugar:</th>
                    <td class="py-2">{{ $actividad->lugar ?? 'Sin asignar' }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Estado:</th>
                    <td class="py-2">
                        @if($actividad->estado == 'completada')
                            <span class="badge bg-success">Completada</span>
                        @elseif($actividad->estado == 'en_curso')
                            <span class="badge bg-warning text-dark">En Curso</span>
                        @elseif($actividad->estado == 'cancelada')
                            <span class="badge bg-danger">Cancelada</span>
                        @else
                            <span class="badge bg-info text-dark">Planificada</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Descripci├│n:</th>
                    <td class="py-2">{{ $actividad->descripcion ?? 'Sin descripci├│n' }}</td>
                </tr>
            </table>

            <div class="mt-4 d-flex gap-2">
                <a href="{{ route('actividades.edit', $actividad->id) }}" class="btn btn-church">
                    <i class="bi bi-pencil me-1"></i> Editar
                </a>
                <a href="{{ route('actividades.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card p-4 shadow-sm mb-4">
            <h4 class="sidebar-title mb-3" style="color: var(--church-gold);"><i class="bi bi-journal-check me-2"></i>Seguimientos de Avance</h4>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Fecha Registro</th>
                            <th>Responsable</th>
                            <th>Estado</th>
                            <th>Avance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($actividad->seguimientos as $seguimiento)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($seguimiento->fecha_registro)->format('d/m/Y') }}</td>
                            <td>{{ $seguimiento->responsable ?? 'N/A' }}</td>
                            <td>{{ $seguimiento->estado }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $seguimiento->porcentaje_avance }}%" aria-valuenow="{{ $seguimiento->porcentaje_avance }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="small font-monospace fw-bold">{{ number_format($seguimiento->porcentaje_avance, 0) }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">No registra seguimientos de avance.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        </div>
    </div>
</div>
@endsection
