@extends('layouts.app')

@section('title', 'Detalle de Usuario')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}" class="text-gold-500 text-decoration-none">Usuarios</a></li>
    <li class="breadcrumb-item active text-white" aria-current="page">{{ $user->name }}</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Detalle de Usuario</h1>
            <p class="text-muted mb-0">Información de la cuenta y roles asignados.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Volver al listado
            </a>
            @if(!$user->trashed() && auth()->user()->puede('usuarios', 'editar'))
                <a href="{{ route('usuarios.edit', $user->id) }}" class="btn btn-church">
                    <i class="bi bi-pencil-fill me-1"></i> Editar Usuario
                </a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="user-avatar mx-auto mb-4">
                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                    </div>
                    <h4 class="mb-1 fw-bold">{{ $user->name }}</h4>
                    <p class="text-muted mb-4">{{ $user->email }}</p>

                    @if($user->trashed())
                        <span class="badge bg-danger">Cuenta eliminada</span>
                    @else
                        <span class="badge bg-success">Cuenta activa</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 bg-body-secondary py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-person-vcard-fill text-gold-500 me-2"></i>Información</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center gap-3 py-3 px-4">
                            <span class="text-muted">Nombre</span>
                            <span class="fw-semibold text-end">{{ $user->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center gap-3 py-3 px-4">
                            <span class="text-muted">Correo Electrónico</span>
                            <span class="fw-semibold text-end">{{ $user->email }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start gap-3 py-3 px-4">
                            <span class="text-muted">Roles</span>
                            <span class="text-end">
                                @forelse($user->roles as $role)
                                    @php
                                        $badgeClass = 'bg-secondary';
                                        if ($role->nombre === 'Administrador') $badgeClass = 'bg-danger';
                                        elseif ($role->nombre === 'Secretario') $badgeClass = 'bg-primary';
                                        elseif ($role->nombre === 'Tesorero') $badgeClass = 'bg-warning text-dark';
                                    @endphp
                                    <span class="badge {{ $badgeClass }} me-1 mb-1">{{ $role->nombre }}</span>
                                @empty
                                    <span class="text-muted">Sin roles</span>
                                @endforelse
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center gap-3 py-3 px-4">
                            <span class="text-muted">Fecha de Registro</span>
                            <span class="fw-semibold text-end">{{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y H:i') }}</span>
                        </li>
                        @if($user->deleted_at)
                            <li class="list-group-item d-flex justify-content-between align-items-center gap-3 py-3 px-4">
                                <span class="text-muted">Fecha de Eliminación</span>
                                <span class="fw-semibold text-end text-danger">{{ \Carbon\Carbon::parse($user->deleted_at)->format('d/m/Y H:i') }}</span>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
