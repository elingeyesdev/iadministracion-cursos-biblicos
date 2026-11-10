@extends('layouts.app')

@section('title', 'Listado de Miembros')

@section('breadcrumb')
    <li class="breadcrumb-item active text-muted" aria-current="page">Miembros</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="sidebar-title mb-0" style="color: var(--church-gold);"><i class="bi bi-people me-2"></i>Miembros</h2>
        <a href="{{ route('miembros.create') }}" class="btn btn-church">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Miembro
        </a>
    </div>

    <div class="mb-3">
        <div class="input-group w-25">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="buscador" class="form-control" placeholder="Buscar...">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Ingreso</th>
                    <th>Sexo</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaBody">
                @forelse($miembros as $miembro)
                <tr>
                    <td><strong>{{ $miembro->nombre }}</strong></td>
                    <td>{{ $miembro->email }}</td>
                    <td>{{ $miembro->telefono ?? 'N/A' }}</td>
                    <td>
                        @if($miembro->estado == 'activo')
                            <span class="badge bg-success">Activo</span>
                        @elseif($miembro->estado == 'inactivo')
                            <span class="badge bg-danger">Inactivo</span>
                        @else
                            <span class="badge bg-warning text-dark">Visita</span>
                        @endif
                    </td>
                    <td>{{ $miembro->fecha_ingreso ? \Carbon\Carbon::parse($miembro->fecha_ingreso)->format('d/m/Y') : 'N/A' }}</td>
                    <td><span class="text-uppercase">{{ $miembro->sexo ?? 'Otro' }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('miembros.show', $miembro->id) }}" class="btn btn-sm btn-outline-info me-1" title="Ver Detalle">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('miembros.edit', $miembro->id) }}" class="btn btn-sm btn-outline-warning me-1" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('miembros.destroy', $miembro->id) }}" method="POST" class="d-inline eliminar-form">
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
                    <td colspan="7" class="text-center text-muted py-4">No hay miembros registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $miembros->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('buscador').addEventListener('keyup', function() {
    let filtro = this.value.toLowerCase();
    document.querySelectorAll('#tablaBody tr').forEach(function(fila) {
        fila.style.display = fila.textContent.toLowerCase().includes(filtro) ? '' : 'none';
    });
});

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
