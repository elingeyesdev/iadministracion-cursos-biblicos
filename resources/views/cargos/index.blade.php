@extends('layouts.app')

@section('title', 'Listado de Cargos')

@section('breadcrumb')
    <li class="breadcrumb-item active text-muted" aria-current="page">Cargos</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="sidebar-title mb-0" style="color: var(--church-gold);"><i class="bi bi-briefcase me-2"></i>Cargos</h2>
        <a href="{{ route('cargos.create') }}" class="btn btn-church">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Cargo
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Nivel Jerárquico</th>
                    <th>Líderes Asignados</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cargos as $cargo)
                <tr>
                    <td><strong>{{ $cargo->nombre }}</strong></td>
                    <td>{{ $cargo->nivel_jerarquico }}</td>
                    <td><span class="badge bg-info text-dark">{{ $cargo->lideres_iglesia_count }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('cargos.show', $cargo->id) }}" class="btn btn-sm btn-outline-info me-1" title="Ver Detalle">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('cargos.edit', $cargo->id) }}" class="btn btn-sm btn-outline-warning me-1" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('cargos.destroy', $cargo->id) }}" method="POST" class="d-inline eliminar-form">
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
                    <td colspan="4" class="text-center text-muted py-4">No hay cargos registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $cargos->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.btn-eliminar').forEach(btn => {
    btn.addEventListener('click', function() {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
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