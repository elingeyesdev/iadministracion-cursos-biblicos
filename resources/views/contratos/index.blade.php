@extends('layouts.app')

@section('title', 'Listado de Contratos')

@section('breadcrumb')
    <li class="breadcrumb-item active text-muted" aria-current="page">Contratos</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="sidebar-title mb-0" style="color: var(--church-gold);"><i class="bi bi-file-earmark-text me-2"></i>Contratos</h2>
        <a href="{{ route('contratos.create') }}" class="btn btn-church">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Contrato
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
                    <th>Miembro</th>
                    <th>Descripción</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Monto</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaBody">
                @forelse($contratos as $contrato)
                <tr>
                    <td><strong>{{ $contrato->miembro->nombre ?? 'N/A' }}</strong></td>
                    <td>{{ Str::limit($contrato->descripcion, 40) ?? 'Sin descripción' }}</td>
                    <td>{{ \Carbon\Carbon::parse($contrato->fecha_inicio)->format('d/m/Y') }}</td>
                    <td>{{ $contrato->fecha_fin ? \Carbon\Carbon::parse($contrato->fecha_fin)->format('d/m/Y') : 'Vigente' }}</td>
                    <td class="text-warning fw-bold">${{ number_format($contrato->monto, 2) }}</td>
                    <td>
                        @if($contrato->estado == 'activo')
                            <span class="badge bg-success">Activo</span>
                        @elseif($contrato->estado == 'finalizado')
                            <span class="badge bg-secondary">Finalizado</span>
                        @else
                            <span class="badge bg-danger">Cancelado</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('contratos.show', $contrato->id) }}" class="btn btn-sm btn-outline-info me-1" title="Ver Detalle">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('contratos.edit', $contrato->id) }}" class="btn btn-sm btn-outline-warning me-1" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('contratos.destroy', $contrato->id) }}" method="POST" class="d-inline eliminar-form">
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
                    <td colspan="7" class="text-center text-muted py-4">No hay contratos registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $contratos->links() }}
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
