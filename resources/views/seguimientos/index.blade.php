@extends('layouts.app')

@section('title', 'Listado de Seguimientos')

@section('breadcrumb')
    <li class="breadcrumb-item active text-muted" aria-current="page">Seguimientos</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="sidebar-title mb-0" style="color: var(--church-gold);"><i class="bi bi-journal-check me-2"></i>Seguimientos</h2>
        <a href="{{ route('seguimientos.create') }}" class="btn btn-church">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Seguimiento
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
                    <th>Actividad</th>
                    <th>Fecha</th>
                    <th>Responsable</th>
                    <th>Estado</th>
                    <th>Avance</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaBody">
                @forelse($seguimientos as $seguimiento)
                <tr>
                    <td><strong>{{ $seguimiento->actividad->nombre ?? 'N/A' }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($seguimiento->fecha_registro)->format('d/m/Y') }}</td>
                    <td>{{ $seguimiento->responsable ?? 'N/A' }}</td>
                    <td><span class="badge bg-secondary">{{ $seguimiento->estado }}</span></td>
                    <td style="width: 20%;">
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $seguimiento->porcentaje_avance }}%" aria-valuenow="{{ $seguimiento->porcentaje_avance }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="small font-monospace fw-bold">{{ number_format($seguimiento->porcentaje_avance, 0) }}%</span>
                        </div>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('seguimientos.show', $seguimiento->id) }}" class="btn btn-sm btn-outline-info me-1" title="Ver Detalle">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('seguimientos.edit', $seguimiento->id) }}" class="btn btn-sm btn-outline-warning me-1" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('seguimientos.destroy', $seguimiento->id) }}" method="POST" class="d-inline eliminar-form">
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
                    <td colspan="6" class="text-center text-muted py-4">No hay seguimientos registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $seguimientos->links() }}
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
