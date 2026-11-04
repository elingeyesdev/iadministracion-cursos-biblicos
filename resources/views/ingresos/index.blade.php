@extends('layouts.app')

@section('title', 'Listado de Ingresos')

@section('breadcrumb')
    <li class="breadcrumb-item active text-white" aria-current="page">Ingresos</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Ingresos</h1>
            <p class="text-muted mb-0">Gestiona el registro de ingresos y diezmos recaudados en los cultos.</p>
        </div>
        <a href="{{ route('ingresos.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle me-1"></i> Registrar Ingreso
        </a>
    </div>

    <!-- Buscador en tiempo real -->
    <div class="card mb-4 border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text bg-transparent text-muted border-secondary">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="realtimeSearch" class="form-control bg-transparent text-white border-secondary" placeholder="Buscar ingreso...">
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="ingresosTable" style="color: #c5c5d2;">
                <thead class="table-dark" style="background-color: #151521;">
                    <tr>
                        <th scope="col" class="border-0">Culto</th>
                        <th scope="col" class="border-0">Día</th>
                        <th scope="col" class="border-0">Fecha</th>
                        <th scope="col" class="border-0">Tipo / Concepto</th>
                        <th scope="col" class="border-0">Método de Pago</th>
                        <th scope="col" class="border-0 text-end">Monto Total</th>
                        <th scope="col" class="border-0 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                    @forelse($ingresos as $ingreso)
                        <tr class="ingreso-row">
                            <td class="fw-bold text-white">{{ $ingreso->culto->nombre ?? '-' }}</td>
                            <td class="text-capitalize">{{ optional($ingreso->culto)->dia_semana ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($ingreso->fecha)->format('d/m/Y') }}</td>
                            <td>{{ $ingreso->tipo ?? 'Ingreso General' }}</td>
                            <td class="text-capitalize">{{ $ingreso->metodo_pago }}</td>
                            <td class="text-end fw-bold text-success">
                                Bs. {{ number_format($ingreso->monto_total, 2) }}
                            </td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('ingresos.show', $ingreso->id) }}" class="btn btn-sm btn-outline-info" title="Ver Detalle">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="{{ route('ingresos.edit', $ingreso->id) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-ingreso" data-id="{{ $ingreso->id }}" title="Eliminar">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                    <form id="delete-form-{{ $ingreso->id }}" action="{{ route('ingresos.destroy', $ingreso->id) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay ingresos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ingresos->hasPages())
            <div class="card-footer border-0 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #151521;">
                <div class="text-muted small">
                    Mostrando {{ $ingresos->firstItem() }}–{{ $ingresos->lastItem() }} de {{ $ingresos->total() }} ingresos
                </div>
                <div>
                    {{ $ingresos->links('pagination::bootstrap-5') }}
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
        const rows = document.querySelectorAll('.ingreso-row');

        searchInput.addEventListener('input', function() {
            const query = searchInput.value.toLowerCase().trim();
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query)) {
                    row.style.setProperty('display', '', 'important');
                } else {
                    row.style.setProperty('display', 'none', 'important');
                }
            });
        });

        // SweetAlert2 confirmación de borrado
        const deleteButtons = document.querySelectorAll('.btn-delete-ingreso');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const ingresoId = this.getAttribute('data-id');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Vas a eliminar permanentemente este ingreso. Esta acción no se puede deshacer.",
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
                        document.getElementById(`delete-form-${ingresoId}`).submit();
                    }
                });
            });
        });
    });
</script>
@endsection
