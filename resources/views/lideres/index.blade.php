@extends('layouts.app')

@section('title', 'Listado de L├¡deres de Iglesia')

@section('breadcrumb')
    <li class="breadcrumb-item active text-muted" aria-current="page">L├¡deres de Iglesia</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="sidebar-title mb-0" style="color: var(--church-gold);"><i class="bi bi-person-badge me-2"></i>L├¡deres de Iglesia</h2>
        <a href="{{ route('lideres.create') }}" class="btn btn-church">
            <i class="bi bi-plus-circle me-1"></i> Nuevo L├¡der
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Cargo</th>
                    <th>Tel├®fono</th>
                    <th>Fecha de Inicio</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lideres as $lider)
                <tr>
                    <td><strong>{{ $lider->nombre }}</strong></td>
                    <td>{{ $lider->cargo->nombre ?? 'N/A' }}</td>
                    <td>{{ $lider->telefono ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($lider->fecha_inicio)->format('d/m/Y') }}</td>
                    <td>
                        @if($lider->activo)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-secondary">Inactivo</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('lideres.show', $lider->id) }}" class="btn btn-sm btn-outline-info me-1" title="Ver Detalle">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('lideres.edit', $lider->id) }}" class="btn btn-sm btn-outline-warning me-1" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('lideres.destroy', $lider->id) }}" method="POST" class="d-inline eliminar-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No hay l├¡deres de iglesia registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $lideres->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.btn-eliminar').forEach(btn => {
    btn.addEventListener('click', function() {
        Swal.fire({
            title: '┬┐Est├ís seguro?',
            text: 'Esta acci├│n no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'S├¡, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                this.closest('form').submit();
            }
        });
    });
});
</script>
@endsection
