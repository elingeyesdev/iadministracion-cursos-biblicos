@extends('layouts.app')

@section('title', 'Detalle de Usuario')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}" class="text-gold-500 text-decoration-none">Usuarios</a></li>
    <li class="breadcrumb-item active text-white" aria-current="page">{{ $usuario->name }}</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Detalles del Usuario</h1>
            <p class="text-muted mb-0">Visualiza el perfil del usuario, informaci├│n de contacto y sus asignaciones de rol.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Volver al listado
            </a>
            <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil-fill me-1"></i> Editar Usuario
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Tarjeta Izquierda: Avatar e Informaci├│n General -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-body text-center py-5">
                    @php
                        $initial = strtoupper(substr($usuario->name, 0, 1));
                    @endphp
                    <div class="rounded-circle bg-gold-500 text-dark d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 100px; height: 100px; font-size: 3rem; font-weight: bold; background-color: #d4af37 !important;">
                        {{ $initial }}
                    </div>
                    <h4 class="text-white mb-1 fw-bold">{{ $usuario->name }}</h4>
                    <p class="text-muted mb-4">{{ $usuario->email }}</p>

                    <div class="text-start border-top border-secondary pt-4">
                        <div class="row mb-3">
                            <div class="col-5 text-muted fw-semibold">Nombre de usuario:</div>
                            <div class="col-7 text-white fw-bold">{{ optional($usuario->profile)->username ?? 'ÔÇö' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 text-muted fw-semibold">Tel├®fono:</div>
                            <div class="col-7 text-white">{{ optional($usuario->profile)->telefono ?? 'ÔÇö' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 text-muted fw-semibold">Estado de Cuenta:</div>
                            <div class="col-7">
                                @if(optional($usuario->profile)->estado === 'activo')
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 text-muted fw-semibold">Fecha de Registro:</div>
                            <div class="col-7 text-white">
                                {{ optional($usuario->profile)->fecha_registro ? \Carbon\Carbon::parse(optional($usuario->profile)->fecha_registro)->format('d/m/Y') : 'ÔÇö' }}
                            </div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-5 text-muted fw-semibold">Creado:</div>
                            <div class="col-7 text-white">{{ $usuario->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta Derecha: Tabla de Roles Asignados -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-header border-0 bg-dark py-3">
                    <h5 class="card-title text-white mb-0"><i class="bi bi-shield-check text-gold-500 me-2"></i>Roles Asignados</h5>
                </div>
                <div class="card-body px-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="color: #c5c5d2;">
                            <thead class="table-dark" style="background-color: #151521;">
                                <tr>
                                    <th scope="col" class="border-0 px-4">Rol</th>
                                    <th scope="col" class="border-0">Fecha Asignaci├│n</th>
                                    <th scope="col" class="border-0">Estado</th>
                                    <th scope="col" class="border-0 text-end px-4">Acci├│n</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($usuario->roles as $r)
                                    <tr>
                                        <td class="fw-bold text-white px-4">
                                            @php
                                                $badgeClass = 'bg-secondary';
                                                if ($r->nombre === 'Administrador') $badgeClass = 'bg-danger';
                                                elseif ($r->nombre === 'Secretario') $badgeClass = 'bg-primary';
                                                elseif ($r->nombre === 'Tesorero') $badgeClass = 'bg-warning text-dark';
                                            @endphp
                                            <span class="badge {{ $badgeClass }} me-2">{{ $r->nombre }}</span>
                                            <small class="text-muted d-block mt-1 font-normal">{{ $r->descripcion }}</small>
                                        </td>
                                        <td>
                                            {{ $r->pivot->fecha_asignacion ? \Carbon\Carbon::parse($r->pivot->fecha_asignacion)->format('d/m/Y') : 'ÔÇö' }}
                                        </td>
                                        <td>
                                            @if($r->pivot->estado === 'activo')
                                                <span class="badge bg-success">Activo</span>
                                            @else
                                                <span class="badge bg-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="text-end px-4">
                                            <form action="{{ route('usuarios.roles.estado', [$usuario->id, $r->id]) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                @if($r->pivot->estado === 'activo')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Desactivar asignaci├│n">
                                                        <i class="bi bi-x-circle-fill me-1"></i> Desactivar
                                                    </button>
                                                @else
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Activar asignaci├│n">
                                                        <i class="bi bi-check-circle-fill me-1"></i> Activar
                                                    </button>
                                                @endif
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4 px-4">Este usuario no tiene ning├║n rol asignado.</td>
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
