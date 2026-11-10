@extends('layouts.app')

@section('title', 'Nuevo Curso')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('cursos.index') }}" class="text-gold-500 text-decoration-none">Cursos</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Nuevo</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <h2 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-book-half me-2"></i>Crear Nuevo Curso</h2>

    <form action="{{ route('cursos.store') }}" method="POST">
        @csrf
        
        <div class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre del Curso <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej. Panorama del Antiguo Testamento">
                @error('nombre')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="f_inicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('f_inicio') is-invalid @enderror" id="f_inicio" name="f_inicio" value="{{ old('f_inicio', date('Y-m-d')) }}" required>
                @error('f_inicio')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="f_fin" class="form-label">Fecha de Término</label>
                <input type="date" class="form-control @error('f_fin') is-invalid @enderror" id="f_fin" name="f_fin" value="{{ old('f_fin') }}">
                @error('f_fin')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="cupo_max" class="form-label">Cupo Máximo</label>
                <input type="number" class="form-control @error('cupo_max') is-invalid @enderror" id="cupo_max" name="cupo_max" value="{{ old('cupo_max') }}" placeholder="Ej. 30 (dejar vacío si es ilimitado)">
                @error('cupo_max')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 d-flex align-items-center">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" id="tiene_pago" name="tiene_pago" value="1" {{ old('tiene_pago') ? 'checked' : '' }}>
                    <label class="form-check-label" for="tiene_pago">
                        Curso requiere Pago / Matrícula
                    </label>
                </div>
            </div>

            <div class="col-md-4" id="monto_container" style="display: none;">
                <label for="monto_inscripcion" class="form-label">Monto Inscripción ($) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control @error('monto_inscripcion') is-invalid @enderror" id="monto_inscripcion" name="monto_inscripcion" value="{{ old('monto_inscripcion') }}" placeholder="0.00">
                @error('monto_inscripcion')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 mt-4">
                <label class="form-label d-block fw-bold" style="color: var(--church-gold);">Asignar Docentes</label>
                <div class="row g-2">
                    @forelse($docentes as $docente)
                        <div class="col-md-4 col-sm-6">
                            <div class="p-2 rounded border" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="docentes[]" value="{{ $docente->id }}" id="docente_{{ $docente->id }}" {{ is_array(old('docentes')) && in_array($docente->id, old('docentes')) ? 'checked' : '' }}>
                                    <label class="form-check-label text-truncate w-100" for="docente_{{ $docente->id }}">
                                        {{ $docente->miembro->nombre ?? 'Docente' }}
                                        <span class="d-block text-muted small">{{ $docente->especialidad ?? 'Sin especialidad' }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-muted small">No hay docentes registrados para asignar.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-church">
                <i class="bi bi-save me-1"></i> Guardar
            </button>
            <a href="{{ route('cursos.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i> Cancelar
            </a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tienePago = document.getElementById('tiene_pago');
    const montoContainer = document.getElementById('monto_container');
    const montoInput = document.getElementById('monto_inscripcion');

    function toggleMonto() {
        if (tienePago.checked) {
            montoContainer.style.display = 'block';
            montoInput.setAttribute('required', 'required');
        } else {
            montoContainer.style.display = 'none';
            montoInput.removeAttribute('required');
            montoInput.value = '';
        }
    }

    tienePago.addEventListener('change', toggleMonto);
    toggleMonto(); // Run on load
});
</script>
@endsection
