@extends('layouts.app')

@section('title', 'Usuarios')

@section('breadcrumb')
    <li class="breadcrumb-item active text-white" aria-current="page">Usuarios</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Usuarios</h1>
            <p class="text-muted mb-0">Administra las cuentas de usuario y sus roles de acceso.</p>
        </div>
        @if(auth()->user()->puede('usuarios', 'crear'))
            <a href="{{ route('usuarios.create') }}" class="btn btn-church">
                <i class="bi bi-person-plus-fill me-1"></i> Nuevo Usuario
            </a>
        @endif
    </div>

    {{-- Buscador en tiempo real --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text bg-transparent text-muted border-secondary">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="realtimeSearch" class="form-control border-secondary"
                       placeholder="Buscar usuario por nombre o correo...">
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="usersTable">
                <thead>
                    <tr>
                        <th scope="col" class="border-0">Nombre</th>
                        <th scope="col" class="border-0">Correo Electrónico</th>
                        <th scope="col" class="border-0">Roles</th>
                        <th scope="col" class="border-0">Estado</th>
                        <th scope="col" class="border-0 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="user-row"
                            data-name="{{ strtolower($user->name) }}"
                            data-email="{{ strtolower($user->email) }}">
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @forelse($user->roles as $role)
                                    @php
                                        $badgeClass = 'bg-secondary';
                                        if ($role->nombre === 'Administrador') $badgeClass = 'bg-danger';
                                        elseif ($role->nombre === 'Secretario') $badgeClass = 'bg-primary';
                                        elseif ($role->nombre === 'Tesorero') $badgeClass = 'bg-warning text-dark';
                                    @endphp
                                    <span class="badge {{ $badgeClass }} me-1">{{ $role->nombre }}</span>
                                @empty
                                    <span class="text-muted">Sin roles</span>
                                @endforelse
                            </td>
                            <td>
                                @if($user->trashed())
                                    <span class="badge bg-danger">Eliminado</span>
                                @else
                                    <span class="badge bg-success">Activo</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('usuarios.show', $user->id) }}"
                                       class="btn btn-sm btn-outline-secondary" title="Ver detalle">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    @if(auth()->user()->puede('usuarios', 'editar'))
                                        <a href="{{ route('usuarios.edit', $user->id) }}"
                                           class="btn btn-sm btn-outline-warning" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()->puede('usuarios', 'eliminar'))
                                        @if(auth()->id() !== $user->id)
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger btn-delete-user"
                                                    data-id="{{ $user->id }}"
                                                    data-name="{{ $user->name }}"
                                                    title="Eliminar">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                            <form id="delete-form-{{ $user->id }}"
                                                  action="{{ route('usuarios.destroy', $user->id) }}"
                                                  method="POST" class="d-none">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-danger" disabled
                                                    title="No puedes eliminarte a ti mismo">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No hay usuarios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="card-footer border-0 d-flex align-items-center justify-content-between flex-wrap gap-2 bg-body-secondary">
                <div class="text-muted small">
                    Mostrando {{ $users->firstItem() }}–{{ $users->lastItem() }} de {{ $users->total() }} usuarios
                </div>
                <div>
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('realtimeSearch');

        if (searchInput) {
            const rows = document.querySelectorAll('.user-row');

            searchInput.addEventListener('input', function () {
                const query = searchInput.value.toLowerCase().trim();

                rows.forEach(function (row) {
                    const name = row.getAttribute('data-name') || '';
                    const email = row.getAttribute('data-email') || '';
                    row.style.display = (name.includes(query) || email.includes(query)) ? '' : 'none';
                });
            });
        }

        const dark = document.documentElement.classList.contains('church-dark');

        document.querySelectorAll('.btn-delete-user').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const userId = this.getAttribute('data-id');
                const userName = this.getAttribute('data-name');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `Vas a eliminar al usuario "${userName}". Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    background: dark ? '#16161c' : '#ffffff',
                    color: dark ? '#f0ead6' : '#221e19'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        document.getElementById(`delete-form-${userId}`).submit();
                    }
                });
            });
        });
    });
</script>
@endsection
