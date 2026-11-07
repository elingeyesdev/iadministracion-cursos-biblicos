@extends('layouts.app')

@section('title', 'Asignar Roles')

@section('breadcrumb')
    <li class="breadcrumb-item active text-white" aria-current="page">Asignar Roles</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Asignar Roles</h1>
            <p class="text-muted mb-0">Selecciona un usuario para administrar, activar o desactivar sus roles en el sistema.</p>
        </div>
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
                        <th scope="col" class="border-0">Roles Asignados</th>
                        <th scope="col" class="border-0 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $u)
                        <tr class="usuario-row" data-name="{{ strtolower($u->name) }}" data-email="{{ strtolower($u->email) }}" data-username="{{ strtolower(optional($u->profile)->username) }}">
                            <td class="fw-bold text-white">{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ optional($u->profile)->username ?? '-' }}</td>
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
                                <a href="{{ route('asignar-roles.edit', $u->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-shield-check me-1"></i> Asignar Roles
                                </a>
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
        
        @if($usuarios->hasPages())
            <div class="card-footer border-0 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #151521;">
                <div class="text-muted small">
                    Mostrando {{ $usuarios->firstItem() }}ÔÇô{{ $usuarios->lastItem() }} de {{ $usuarios->total() }} usuarios
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
        // Buscador en tiempo real
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
    });
</script>
@endsection
