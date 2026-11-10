@extends('layouts.app')

@section('title', 'Gestionar Inscripciones - ' . $curso->nombre)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('inscripciones.index') }}" class="text-gold-500 text-decoration-none">Inscripciones</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">{{ $curso->nombre }}</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="sidebar-title mb-0" style="color: var(--church-gold);"><i class="bi bi-journal-richtext me-2"></i>{{ $curso->nombre }}</h2>
        <span class="text-muted">
            Inscritos: <span class="badge bg-info text-dark">{{ $inscripciones->whereNull('deleted_at')->count() }}</span>
            @if($curso->cupo_max)
                / {{ $curso->cupo_max }}
            @endif
        </span>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="p-3 rounded border h-100" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                <h5 class="fw-bold mb-3"><i class="bi bi-person-plus me-1"></i> Inscribir Miembro</h5>
                <form action="{{ route('inscripciones.store', $curso->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="miembro_id" class="form-label">Miembro <span class="text-danger">*</span></label>
                        <select class="form-select" id="miembro_id" name="miembro_id" required>
                            <option value="">-- Seleccionar Miembro --</option>
                            @foreach($miembrosDisponibles as $miembro)
                                <option value="{{ $miembro->id }}">{{ $miembro->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_inscripcion" class="form-label">Fecha de Inscripción <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fecha_inscripcion" name="fecha_inscripcion" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <button type="submit" class="btn btn-church w-100">
                        <i class="bi bi-person-plus me-1"></i> Inscribir
                    </button>
                </form>
                @if($miembrosDisponibles->isEmpty())
                    <div class="alert alert-warning mt-3 mb-0 small">No hay miembros disponibles para inscribir en este curso.</div>
                @endif
            </div>
        </div>

        <div class="col-lg-7">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Miembro</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inscripciones as $inscripcion)
                        <tr @if($inscripcion->trashed()) class="table-secondary" @endif>
                            <td>
                                {{ $inscripcion->miembro->nombre ?? 'Miembro eliminado' }}
                                @if($inscripcion->trashed())
                                    <span class="badge bg-secondary ms-1">Eliminado</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($inscripcion->fecha_inscripcion)->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $color = $inscripcion->estado === 'Activo' ? 'success' : ($inscripcion->estado === 'Completado' ? 'info' : 'warning');
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ $inscripcion->estado }}</span>
                            </td>
                            <td class="text-end">
                                @if($inscripcion->trashed())
                                    <form action="{{ route('inscripciones.restore', $inscripcion->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Restaurar">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('inscripciones.edit', $inscripcion->id) }}" class="btn btn-sm btn-outline-warning me-1" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('inscripciones.destroy', $inscripcion->id) }}" method="POST" class="d-inline eliminar-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Aún no hay inscripciones para este curso.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('inscripciones.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
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
