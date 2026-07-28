@extends('layouts.app')

@section('title', 'Asignar Roles')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('asignar-roles.index') }}" class="text-gold-500 text-decoration-none">Asignar Roles</a></li>
    <li class="breadcrumb-item active text-white" aria-current="page">{{ $usuario->name }}</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Asignar Roles</h1>
            <p class="text-muted mb-0">Modifica la asignación de roles para el usuario <strong>{{ $usuario->name }}</strong>.</p>
        </div>
        <a href="{{ route('asignar-roles.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver al listado
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 mb-4 bg-danger text-white">
            <h5 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Por favor corrige los siguientes errores:</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-header border-0 bg-dark py-3">
                    <h5 class="card-title text-white mb-0"><i class="bi bi-shield-check text-gold-500 me-2"></i>Roles Disponibles</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4 p-3 rounded" style="background: rgba(255, 255, 255, 0.02); border: 1px dashed var(--church-border, #2b2b40);">
                        <h6 class="text-white mb-1">Información de Usuario:</h6>
                        <div class="text-muted small">
                            <span class="me-3"><strong>Nombre:</strong> {{ $usuario->name }}</span>
                            <span class="me-3"><strong>Email:</strong> {{ $usuario->email }}</span>
                            <span><strong>Username:</strong> {{ optional($usuario->profile)->username ?? '—' }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('asignar-roles.update', $usuario->id) }}">
                        @csrf
                        @method('PUT')

                        <p class="text-muted mb-3">Marca los roles que el usuario poseerá en el sistema:</p>

                        <div class="row">
                            @forelse($roles as $rol)
                                <div class="col-12 col-md-6 mb-3">
                                    <div class="p-3 rounded border border-secondary h-100" style="background: rgba(255,255,255,0.02);">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $rol->id }}" id="role_{{ $rol->id }}" {{ in_array($rol->id, $rolesAsignados) ? 'checked' : '' }}>
                                            <label class="form-check-label text-white fw-bold d-block" for="role_{{ $rol->id }}">
                                                {{ $rol->nombre }}
                                            </label>
                                        </div>
                                        <p class="text-muted small mb-0 mt-1" style="padding-left: 1.5rem;">
                                            {{ $rol->descripcion ?? 'Sin descripción disponible.' }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-warning border-0 bg-warning text-dark">
                                        No hay roles activos disponibles en el sistema.
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <div class="mt-4 d-flex gap-3">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save-fill me-1"></i> Guardar Roles
                            </button>
                            <a href="{{ route('asignar-roles.index') }}" class="btn btn-secondary px-4">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
