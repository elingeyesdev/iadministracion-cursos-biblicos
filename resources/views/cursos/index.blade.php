@extends('layouts.app')

@section('title', 'Listado de Cursos')

@section('breadcrumb')
    <li class="breadcrumb-item active text-muted" aria-current="page">Cursos</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="sidebar-title mb-0" style="color: var(--church-gold);"><i class="bi bi-book me-2"></i>Cursos Acad├®micos</h2>
        <a href="{{ route('cursos.create') }}" class="btn btn-church">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Curso
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
                    <th>Inicio</th>
                    <th>T├®rmino</th>
                    <th>Cupo M├íximo</th>
                    <th>Costo Inscripci├│n</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaBody">
                @forelse($cursos as $curso)
                <tr>
                    <td><strong>{{ $curso->nombre }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($curso->f_inicio)->format('d/m/Y') }}</td>
                    <td>{{ $curso->f_fin ? \Carbon\Carbon::parse($curso->f_fin)->format('d/m/Y') : 'N/A' }}</td>
                    <td>{{ $curso->cupo_max ?? 'Sin l├¡mite' }}</td>
                    <td>
                        @if($curso->tiene_pago)
                            <span class="badge bg-warning text-dark">${{ number_format($curso->monto_inscripcion, 2) }}</span>
                        @else
                            <span class="badge bg-success">Gratuito</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('cursos.show', $curso->id) }}" class="btn btn-sm btn-outline-info me-1" title="Ver Detalle">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('cursos.edit', $curso->id) }}" class="btn btn-sm btn-outline-warning me-1" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('cursos.destroy', $curso->id) }}" method="POST" class="d-inline eliminar-form">
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
                    <td colspan="6" class="text-center text-muted py-4">No hay cursos registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $cursos->links() }}
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
