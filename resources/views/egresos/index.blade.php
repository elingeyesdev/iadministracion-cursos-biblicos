@extends('layouts.app')

@section('title', 'Listado de Egresos')

@section('breadcrumb')
    <li class="breadcrumb-item active text-muted" aria-current="page">Egresos</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="sidebar-title mb-0" style="color: var(--church-gold);"><i class="bi bi-cash-stack me-2"></i>Egresos</h2>
        <a href="{{ route('egresos.create') }}" class="btn btn-church">
            <i class="bi bi-plus-circle me-1"></i> Registrar Gasto
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
                    <th>Fecha</th>
                    <th>Tipo de Gasto</th>
                    <th>Monto</th>
                    <th>Responsable</th>
                    <th>Ministerio</th>
                    <th>Contrato</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaBody">
                @forelse($egresos as $egreso)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($egreso->fecha)->format('d/m/Y') }}</td>
                    <td><strong>{{ $egreso->tipo_egreso }}</strong></td>
                    <td class="text-danger fw-bold">${{ number_format($egreso->monto, 2) }}</td>
                    <td>{{ $egreso->responsable ?? 'N/A' }}</td>
                    <td>{{ $egreso->ministerio->nombre ?? 'General' }}</td>
                    <td>
                        @if($egreso->contrato)
                            Contrato #{{ $egreso->contrato->id }} ({{ $egreso->contrato->miembro->nombre ?? '' }})
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('egresos.show', $egreso->id) }}" class="btn btn-sm btn-outline-info me-1" title="Ver Detalle">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('egresos.edit', $egreso->id) }}" class="btn btn-sm btn-outline-warning me-1" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('egresos.destroy', $egreso->id) }}" method="POST" class="d-inline eliminar-form">
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
                    <td colspan="7" class="text-center text-muted py-4">No hay egresos registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $egresos->links() }}
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
