@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('breadcrumb')
    <li class="breadcrumb-item active text-white" aria-current="page">Usuarios</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Usuarios</h1>
            <p class="text-muted mb-0">Administra las cuentas de usuario, perfiles y asignación de roles.</p>
        </div>
        <a href="{{ route('usuarios.create') }}" class="btn btn-success">
            <i class="bi bi-person-plus-fill me-1"></i> Nuevo Usuario
        </a>
    </div>

    <!-- Buscador en tiempo real -->
    <div class="card mb-4 border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text bg-transparent text-muted border-secondary">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="realtimeSearch" class="form-control bg-transparent text-white border-secondary" placeholder="Buscar usuario por nombre, email o username...">
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="usuariosTable" style="color: #c5c5d2;">
                <thead class="table-dark" style="background-color: #151521;">
                    <tr>
                        <th scope="col" class="border-0">Nombre</th>
                        <th scope="col" class="border-0">Email</th>
                        <th scope="col" class="border-0">Username</th>
                        <th scope="col" class="border-0">Teléfono</th>
                        <th scope="col" class="border-0">Estado</th>
                        <th scope="col" class="border-0">Roles</th>
                        <th scope="col" class="border-0 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $u)
                        <tr class="usuario-row" data-name="{{ strtolower($u->name) }}" data-email="{{ strtolower($u->email) }}" data-username="{{ strtolower(optional($u->profile)->username) }}">
                            <td class="fw-bold text-white">{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ optional($u->profile)->username ?? '-' }}</td>
                            <td>{{ optional($u->profile)->telefono ?? '-' }}</td>
                            <td>
                                @if(optional($u->profile)->estado === 'activo')
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                @forelse($u->roles as $r)
                                    @php
                                        $badgeClass = 'bg-secondary';
                                        if ($r->nombre === 'Administrador') $badgeClass = 'bg-danger';
                                        elseif ($r->nombre === 'Secretario') $badgeClass = 'bg-primary';
                                        elseif ($r->nombre === 'Tesorero') $badgeClass = 'bg-warning text-dark';
                                    @endphp
                                    <span class="badge {{ $badgeClass }} me-1">{{ $r->nombre }}</span>
                                @empty
                                    <span class="badge bg-dark text-muted">Sin Roles</span>
                                @endforelse
                            </td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('usuarios.show', $u->id) }}" class="btn btn-sm btn-outline-info" title="Ver Detalle">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="{{ route('usuarios.edit', $u->id) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    @if(auth()->id() !== $u->id)
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-usuario" data-id="{{ $u->id }}" data-name="{{ $u->name }}" title="Eliminar">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                        <form id="delete-form-{{ $u->id }}" action="{{ route('usuarios.destroy', $u->id) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-danger disabled" title="No puedes eliminarte a ti mismo" disabled>
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay usuarios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($usuarios->hasPages())
            <div class="card-footer border-0 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #151521;">
                <div class="text-muted small">
                    Mostrando {{ $usuarios->firstItem() }}–{{ $usuarios->lastItem() }} de {{ $usuarios->total() }} usuarios
                </div>
                <div>
                    {{ $usuarios->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Buscador en tiempo real (Client-side filtering)
        const searchInput = document.getElementById('realtimeSearch');
        const rows = document.querySelectorAll('.usuario-row');

        searchInput.addEventListener('input', function() {
            const query = searchInput.value.toLowerCase().trim();
            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const email = row.getAttribute('data-email') || '';
                const username = row.getAttribute('data-username') || '';
                
                if (name.includes(query) || email.includes(query) || username.includes(query)) {
                    row.style.setProperty('display', '', 'important');
                } else {
                    row.style.setProperty('display', 'none', 'important');
                }
            });
        });

        // SweetAlert2 confirmación de borrado
        const deleteButtons = document.querySelectorAll('.btn-delete-usuario');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const userName = this.getAttribute('data-name');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `Vas a eliminar permanentemente al usuario "${userName}". Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    background: '#1e1e2d',
                    color: '#fff'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(`delete-form-${userId}`).submit();
                    }
                });
            });
        });
    });
</script>
@endsection
