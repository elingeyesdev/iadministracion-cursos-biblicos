@extends('layouts.app')

@section('title', 'Gesti├│n de Cultos')

@section('breadcrumb')
    <li class="breadcrumb-item active text-white" aria-current="page">Cultos</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Cultos</h1>
            <p class="text-muted mb-0">Administra las reuniones y horarios de culto de la congregaci├│n.</p>
        </div>
        <a href="{{ route('cultos.create') }}" class="btn btn-success">
            <i class="bi bi-calendar-heart me-1"></i> Nuevo Culto
        </a>
    </div>

    <!-- Buscador en tiempo real -->
    <div class="card mb-4 border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text bg-transparent text-muted border-secondary">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="realtimeSearch" class="form-control bg-transparent text-white border-secondary" placeholder="Buscar culto por nombre...">
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="cultosTable" style="color: #c5c5d2;">
                <thead class="table-dark" style="background-color: #151521;">
                    <tr>
                        <th scope="col" class="border-0">Nombre</th>
                        <th scope="col" class="border-0">D├¡a de la semana</th>
                        <th scope="col" class="border-0">Hora</th>
                        <th scope="col" class="border-0">Descripci├│n</th>
                        <th scope="col" class="border-0 text-end">Total Recaudado</th>
                        <th scope="col" class="border-0 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cultos as $c)
                        <tr class="culto-row" data-nombre="{{ strtolower($c->nombre) }}">
                            <td class="fw-bold text-white">{{ $c->nombre }}</td>
                            <td class="text-capitalize">{{ $c->dia_semana }}</td>
                            <td>{{ \Carbon\Carbon::parse($c->hora)->format('H:i') }} hs</td>
                            <td>{{ $c->descripcion ?? 'Sin descripci├│n' }}</td>
                            <td class="text-end fw-bold text-success">
                                Bs. {{ number_format($c->ingresos_sum_monto_total ?? 0, 2) }}
                            </td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('cultos.show', $c->id_culto) }}" class="btn btn-sm btn-outline-info" title="Ver Detalle">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="{{ route('cultos.edit', $c->id_culto) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-culto" data-id="{{ $c->id_culto }}" data-nombre="{{ $c->nombre }}" title="Eliminar">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                    <form id="delete-form-{{ $c->id_culto }}" action="{{ route('cultos.destroy', $c->id_culto) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No hay cultos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cultos->hasPages())
            <div class="card-footer border-0 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #151521;">
                <div class="text-muted small">
                    Mostrando {{ $cultos->firstItem() }}ÔÇô{{ $cultos->lastItem() }} de {{ $cultos->total() }} cultos
                </div>
                <div>
                    {{ $cultos->links('pagination::bootstrap-5') }}
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
        const rows = document.querySelectorAll('.culto-row');

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
        const deleteButtons = document.querySelectorAll('.btn-delete-culto');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const cultoId = this.getAttribute('data-id');
                const cultoNombre = this.getAttribute('data-nombre');

                Swal.fire({
                    title: '┬┐Est├ís seguro?',
                    text: `Vas a eliminar el culto "${cultoNombre}". Esta acci├│n no se puede deshacer y fallar├í si tiene ingresos registrados.`,
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
                        document.getElementById(`delete-form-${cultoId}`).submit();
                    }
                });
            });
        });
    });
</script>
@endsection
