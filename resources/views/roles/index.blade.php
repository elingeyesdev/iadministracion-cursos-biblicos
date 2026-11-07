@extends('layouts.app')

@section('title', 'Gesti├│n de Roles')

@section('breadcrumb')
    <li class="breadcrumb-item active text-white" aria-current="page">Roles</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Roles</h1>
            <p class="text-muted mb-0">Administra los roles del sistema que controlan los permisos de acceso.</p>
        </div>
        <div class="d-flex gap-2">
            @permiso('permisos.ver')
                <a href="{{ route('permisos.index') }}" class="btn btn-outline-light">
                    <i class="bi bi-key me-1"></i> Permisos
                </a>
            @endpermiso
            @permiso('roles.gestionar')
                <a href="{{ route('roles.create') }}" class="btn btn-success">
                    <i class="bi bi-shield-plus me-1"></i> Nuevo Rol
                </a>
            @endpermiso
        </div>
    </div>

    <!-- Buscador en tiempo real -->
    <div class="card mb-4 border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text bg-transparent text-muted border-secondary">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="realtimeSearch" class="form-control bg-transparent text-white border-secondary" placeholder="Buscar rol por nombre...">
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="rolesTable" style="color: #c5c5d2;">
                <thead class="table-dark" style="background-color: #151521;">
                    <tr>
                        <th scope="col" class="border-0">Nombre</th>
                        <th scope="col" class="border-0">Descripci├│n</th>
                        <th scope="col" class="border-0">Estado</th>
                        <th scope="col" class="border-0">Permisos</th>
                        <th scope="col" class="border-0">Usuarios Asignados</th>
                        <th scope="col" class="border-0 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $rol)
                        <tr class="rol-row" data-nombre="{{ strtolower($rol->nombre) }}">
                            <td class="fw-bold text-white">
                                @php
                                    $badgeClass = 'bg-secondary';
                                    if ($rol->nombre === 'Administrador') $badgeClass = 'bg-danger';
                                    elseif ($rol->nombre === 'Secretario') $badgeClass = 'bg-primary';
                                    elseif ($rol->nombre === 'Tesorero') $badgeClass = 'bg-warning text-dark';
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $rol->nombre }}</span>
                            </td>
                            <td>{{ $rol->descripcion ?? 'Sin descripci├│n' }}</td>
                            <td>
                                @if($rol->estado === 'activo')
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info text-dark fw-bold" title="Permisos asignados">{{ $rol->permisos_count ?? 0 }}</span>
                            </td>
                            <td>
                                <span class="badge bg-dark text-white fw-bold">{{ $rol->users_count ?? 0 }}</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('roles.show', $rol->id) }}" class="btn btn-sm btn-outline-info" title="Ver Detalle">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    @permiso('roles.gestionar')
                                        <a href="{{ route('roles.edit', $rol->id) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-rol" data-id="{{ $rol->id }}" data-nombre="{{ $rol->nombre }}" title="Eliminar">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                        <form id="delete-form-{{ $rol->id }}" action="{{ route('roles.destroy', $rol->id) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endpermiso
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No hay roles registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($roles->hasPages())
            <div class="card-footer border-0 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #151521;">
                <div class="text-muted small">
                    Mostrando {{ $roles->firstItem() }}ÔÇô{{ $roles->lastItem() }} de {{ $roles->total() }} roles
                </div>
                <div>
                    {{ $roles->links('pagination::bootstrap-5') }}
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
        const rows = document.querySelectorAll('.rol-row');

        searchInput.addEventListener('input', function() {
            const query = searchInput.value.toLowerCase().trim();
            rows.forEach(row => {
                const nombre = row.getAttribute('data-nombre') || '';
                
                if (nombre.includes(query)) {
                    row.style.setProperty('display', '', 'important');
                } else {
                    row.style.setProperty('display', 'none', 'important');
                }
            });
        });

        // SweetAlert2 confirmaci├│n de borrado
        const deleteButtons = document.querySelectorAll('.btn-delete-rol');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const rolId = this.getAttribute('data-id');
                const rolNombre = this.getAttribute('data-nombre');

                Swal.fire({
                    title: '┬┐Est├ís seguro?',
                    text: `Vas a eliminar permanentemente el rol "${rolNombre}". Esta acci├│n no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'S├¡, eliminar',
                    cancelButtonText: 'Cancelar',
                    background: '#1e1e2d',
                    color: '#fff'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(`delete-form-${rolId}`).submit();
                    }
                });
            });
        });
    });
</script>
@endsection
