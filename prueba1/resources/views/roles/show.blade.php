@extends('layouts.app')

@section('title', 'Detalle del Rol')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}" class="text-gold-500 text-decoration-none">Roles</a></li>
    <li class="breadcrumb-item active text-white" aria-current="page">{{ $rol->nombre }}</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Detalles del Rol</h1>
            <p class="text-muted mb-0">Revisa la información del rol y el listado de usuarios asignados.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Volver al listado
            </a>
            <a href="{{ route('roles.edit', $rol->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil-fill me-1"></i> Editar Rol
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Columna Izquierda: Información del Rol -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-header border-0 bg-dark py-3">
                    <h5 class="card-title text-white mb-0"><i class="bi bi-shield-fill text-gold-500 me-2"></i>Información del Rol</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="text-muted small fw-semibold d-block mb-1">Nombre</label>
                        @php
                            $badgeClass = 'bg-secondary';
                            if ($rol->nombre === 'Administrador') $badgeClass = 'bg-danger';
                            elseif ($rol->nombre === 'Secretario') $badgeClass = 'bg-primary';
                            elseif ($rol->nombre === 'Tesorero') $badgeClass = 'bg-warning text-dark';
                        @endphp
                        <span class="badge {{ $badgeClass }} fs-6">{{ $rol->nombre }}</span>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small fw-semibold d-block mb-1">Descripción</label>
                        <p class="text-white mb-0" style="white-space: pre-line;">{{ $rol->descripcion ?? 'Sin descripción registrada.' }}</p>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small fw-semibold d-block mb-1">Estado del Rol</label>
                        @if($rol->estado === 'activo')
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-danger">Inactivo</span>
                        @endif
                    </div>

                    <div class="mb-0">
                        <label class="text-muted small fw-semibold d-block mb-1">Fecha Creación</label>
                        <span class="text-white">{{ $rol->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Usuarios Asignados -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm h-100" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-header border-0 bg-dark py-3">
                    <h5 class="card-title text-white mb-0"><i class="bi bi-people-fill text-gold-500 me-2"></i>Usuarios Asignados ({{ $rol->users->count() }})</h5>
                </div>
                <div class="card-body px-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="color: #c5c5d2;">
                            <thead class="table-dark" style="background-color: #151521;">
                                <tr>
                                    <th scope="col" class="border-0 px-4">Usuario</th>
                                    <th scope="col" class="border-0">Username</th>
                                    <th scope="col" class="border-0">Teléfono</th>
                                    <th scope="col" class="border-0">Fecha Asignación</th>
                                    <th scope="col" class="border-0 px-4">Estado Asignación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rol->users as $u)
                                    <tr>
                                        <td class="px-4">
                                            <div class="fw-bold text-white">{{ $u->name }}</div>
                                            <small class="text-muted">{{ $u->email }}</small>
                                        </td>
                                        <td>{{ optional($u->profile)->username ?? '—' }}</td>
                                        <td>{{ optional($u->profile)->telefono ?? '—' }}</td>
                                        <td>
                                            {{ $u->pivot->fecha_asignacion ? \Carbon\Carbon::parse($u->pivot->fecha_asignacion)->format('d/m/Y') : '—' }}
                                        </td>
                                        <td class="px-4">
                                            @if($u->pivot->estado === 'activo')
                                                <span class="badge bg-success">Activo</span>
                                            @else
                                                <span class="badge bg-danger">Inactivo</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4 px-4">Ningún usuario tiene asignado este rol actualmente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
