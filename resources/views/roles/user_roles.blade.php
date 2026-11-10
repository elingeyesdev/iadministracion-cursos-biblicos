@extends('layouts.app')

@section('title', 'Asignar Rol')

@section('breadcrumb')
    <li class="breadcrumb-item active text-white" aria-current="page">Roles Usuario</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Roles Usuario</h1>
            <p class="text-muted mb-0">Gestiona los roles asignados a los usuarios del sistema.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" class="border-0">Usuario</th>
                        <th scope="col" class="border-0">Correo Electrónico</th>
                        <th scope="col" class="border-0">Roles Actuales</th>
                        <th scope="col" class="border-0 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->roles->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($user->roles as $role)
                                            <span class="badge bg-info text-dark">{{ $role->nombre }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">Sin roles</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    @if(auth()->user()->puede('roles', 'editar'))
                                        <a href="{{ route('asignar-roles.edit', $user->id) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Asignar roles" aria-label="Asignar roles a {{ $user->name }}">
                                            <i class="bi bi-shield-check"></i>
                                        </a>
                                        <form method="POST" action="{{ route('asignar-roles.destroy', $user->id) }}"
                                              onsubmit="return confirm('¿Quitar todos los roles asignados a este usuario?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="Limpiar roles" aria-label="Limpiar roles de {{ $user->name }}">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No hay usuarios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="card-footer border-0 bg-body-secondary">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
