@extends('layouts.app')

@section('title', 'Detalle de la Sesión')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sesiones.index') }}" class="text-gold-500 text-decoration-none">Sesiones</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Detalle</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-md-5">
        <div class="card p-4 shadow-sm h-100">
            <h3 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-journal-text me-2"></i>Detalle de la Sesión</h3>

            <table class="table table-borderless">
                <tr>
                    <th class="ps-0 py-2 text-muted" style="width: 35%;">Curso:</th>
                    <td class="py-2"><strong>{{ $sesion->curso->nombre ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Tema:</th>
                    <td class="py-2">{{ $sesion->tema ?? 'Sin Tema definido' }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Fecha:</th>
                    <td class="py-2">{{ \Carbon\Carbon::parse($sesion->fecha)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Hora:</th>
                    <td class="py-2">{{ $sesion->hora ? \Carbon\Carbon::parse($sesion->hora)->format('H:i') : 'Sin hora' }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Observaciones:</th>
                    <td class="py-2">{{ $sesion->observacion ?? 'Sin observaciones' }}</td>
                </tr>
            </table>

            <div class="mt-4 d-flex gap-2">
                <a href="{{ route('sesiones.edit', $sesion->id) }}" class="btn btn-church">
                    <i class="bi bi-pencil me-1"></i> Editar
                </a>
                <a href="{{ route('sesiones.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card p-4 shadow-sm h-100">
            <h4 class="sidebar-title mb-3" style="color: var(--church-gold);"><i class="bi bi-people me-2"></i>Miembros Registrados en esta Sesión</h4>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Estado Asistencia</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sesion->miembros as $miembro)
                        <tr>
                            <td><strong>{{ $miembro->nombre }}</strong></td>
                            <td>{{ $miembro->email }}</td>
                            <td>
                                <span class="badge bg-{{ $miembro->pivot->estado == 'presente' ? 'success' : ($miembro->pivot->estado == 'ausente' ? 'danger' : 'warning text-dark') }}">
                                    {{ ucfirst($miembro->pivot->estado) }}
                                </span>
                            </td>
                            <td>{{ $miembro->pivot->observacion ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">No hay miembros registrados para esta clase.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
