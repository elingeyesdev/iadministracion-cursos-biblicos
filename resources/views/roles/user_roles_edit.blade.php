@extends('layouts.app')

@section('title', 'Asignar Roles')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('asignar-roles.index') }}" class="text-gold-500 text-decoration-none">Roles Usuario</a></li>
    <li class="breadcrumb-item active text-white" aria-current="page">Asignar Roles</li>
@endsection

@section('content')
@php
    // Conserva la selección enviada si la validación devuelve al formulario.
    $seleccionados = array_map('intval', (array) old('roles', $user->roles->pluck('id')->all()));
@endphp

<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Asignar Roles</h1>
            <p class="text-muted mb-0">Selecciona los roles para <strong class="text-white">{{ $user->name }}</strong> ({{ $user->email }}).</p>
        </div>
        <a href="{{ route('asignar-roles.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 mb-4">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($user->roles->isNotEmpty())
        <div class="mb-4 d-flex flex-wrap align-items-center gap-2">
            <span class="text-muted small">Roles actuales:</span>
            @foreach($user->roles as $role)
                <span class="badge bg-info text-dark">{{ $role->nombre }}</span>
            @endforeach
        </div>
    @endif

    @if(auth()->user()->puede('roles', 'editar'))
        <div class="card border-0 shadow-sm" style="max-width: 860px;">
            <div class="card-body">
                <form method="POST" action="{{ route('asignar-roles.store') }}" class="js-assign-roles-form">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->id }}">

                    @if($roles->isEmpty())
                        <div class="alert alert-warning mb-0">
                            No hay roles registrados. Crea un rol antes de asignarlo a un usuario.
                        </div>
                    @else
                        <div class="roles-grid">
                            @foreach($roles as $role)
                                <label class="role-option">
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                           class="form-check-input" @checked(in_array($role->id, $seleccionados, true))>
                                    <span>
                                        <span class="role-option__name">{{ $role->nombre }}</span>
                                        @if($role->descripcion)
                                            <span class="role-option__desc">{{ $role->descripcion }}</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn btn-church px-4">Guardar Roles</button>
                        <a href="{{ route('asignar-roles.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('.js-assign-roles-form');

        if (!form) {
            return;
        }

        form.addEventListener('submit', function () {
            const boton = form.querySelector('button[type="submit"]');

            if (boton) {
                boton.disabled = true;
                boton.classList.add('disabled');
                boton.textContent = 'Guardando...';
            }
        });
    });
</script>
@endsection
