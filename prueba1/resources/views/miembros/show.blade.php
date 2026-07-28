@extends('layouts.app')

@section('title', 'Detalle del Miembro')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('miembros.index') }}" class="text-gold-500 text-decoration-none">Miembros</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Detalle</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-md-5">
        <div class="card p-4 shadow-sm h-100">
            <h3 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-person-badge me-2"></i>Información del Miembro</h3>

            <table class="table table-borderless">
                <tr>
                    <th class="ps-0 py-2 text-muted" style="width: 35%;">Nombre:</th>
                    <td class="py-2"><strong>{{ $miembro->nombre }}</strong></td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Email:</th>
                    <td class="py-2">{{ $miembro->email }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Teléfono:</th>
                    <td class="py-2">{{ $miembro->telefono ?? 'No registrado' }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Nacimiento:</th>
                    <td class="py-2">{{ $miembro->f_nacimiento ? \Carbon\Carbon::parse($miembro->f_nacimiento)->format('d/m/Y') : 'No registrado' }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Sexo:</th>
                    <td class="py-2"><span class="text-uppercase">{{ $miembro->sexo ?? 'Otro' }}</span></td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Estado:</th>
                    <td class="py-2">
                        @if($miembro->estado == 'activo')
                            <span class="badge bg-success">Activo</span>
                        @elseif($miembro->estado == 'inactivo')
                            <span class="badge bg-danger">Inactivo</span>
                        @else
                            <span class="badge bg-warning text-dark">Visita</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Ingreso:</th>
                    <td class="py-2">{{ $miembro->fecha_ingreso ? \Carbon\Carbon::parse($miembro->fecha_ingreso)->format('d/m/Y') : 'No registrado' }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Dirección:</th>
                    <td class="py-2">{{ $miembro->direccion ?? 'No registrada' }}</td>
                </tr>
            </table>

            <div class="mt-4 d-flex gap-2">
                <a href="{{ route('miembros.edit', $miembro->id) }}" class="btn btn-church">
                    <i class="bi bi-pencil me-1"></i> Editar
                </a>
                <a href="{{ route('miembros.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card p-4 shadow-sm mb-4">
            <h4 class="sidebar-title mb-3" style="color: var(--church-gold);"><i class="bi bi-file-earmark-text me-2"></i>Contratos Asociados</h4>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Descripción</th>
                            <th>Monto</th>
                            <th>Vigencia</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($miembro->contratos as $contrato)
                        <tr>
                            <td>{{ Str::limit($contrato->descripcion, 30) }}</td>
                            <td>${{ number_format($contrato->monto, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($contrato->fecha_inicio)->format('d/m/Y') }} @if($contrato->fecha_fin) al {{ \Carbon\Carbon::parse($contrato->fecha_fin)->format('d/m/Y') }} @endif</td>
                            <td>
                                <span class="badge bg-{{ $contrato->estado == 'activo' ? 'success' : ($contrato->estado == 'finalizado' ? 'secondary' : 'danger') }}">
                                    {{ ucfirst($contrato->estado) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">No registra contratos.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-4 shadow-sm">
            <h4 class="sidebar-title mb-3" style="color: var(--church-gold);"><i class="bi bi-journal-text me-2"></i>Sesiones Inscritas</h4>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Sesión / Tema</th>
                            <th>Fecha</th>
                            <th>Estado Asistencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($miembro->sesiones as $sesion)
                        <tr>
                            <td>{{ $sesion->tema ?? 'Sin Tema' }}</td>
                            <td>{{ \Carbon\Carbon::parse($sesion->fecha)->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $sesion->pivot->estado == 'presente' ? 'success' : ($sesion->pivot->estado == 'ausente' ? 'danger' : 'warning text-dark') }}">
                                    {{ ucfirst($sesion->pivot->estado) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">No registra asistencia a sesiones.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
