@extends('layouts.app')

@section('title', 'Editar Seguimiento')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('seguimientos.index') }}" class="text-gold-500 text-decoration-none">Seguimientos</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Editar</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <h2 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-pencil-square me-2"></i>Editar Seguimiento</h2>

    <form action="{{ route('seguimientos.update', $seguimiento->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row g-3">
            <div class="col-md-6">
                <label for="id_actividad" class="form-label">Actividad Relacionada <span class="text-danger">*</span></label>
                <select class="form-select @error('id_actividad') is-invalid @enderror" id="id_actividad" name="id_actividad" required>
                    <option value="" disabled>Seleccione la actividad...</option>
                    @foreach($actividades as $actividad)
                        <option value="{{ $actividad->id }}" {{ old('id_actividad', $seguimiento->id_actividad) == $actividad->id ? 'selected' : '' }}>
                            {{ $actividad->nombre }} ({{ \Carbon\Carbon::parse($actividad->fecha)->format('d/m/Y') }})
                        </option>
                    @endforeach
                </select>
                @error('id_actividad')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="responsable" class="form-label">Responsable del Reporte</label>
                <input type="text" class="form-control @error('responsable') is-invalid @enderror" id="responsable" name="responsable" value="{{ old('responsable', $seguimiento->responsable) }}" placeholder="Ej. Pedro Picapiedra">
                @error('responsable')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="fecha_registro" class="form-label">Fecha de Registro <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('fecha_registro') is-invalid @enderror" id="fecha_registro" name="fecha_registro" value="{{ old('fecha_registro', $seguimiento->fecha_registro) }}" required>
                @error('fecha_registro')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="estado" class="form-label">Estado de la Tarea/Hito <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('estado') is-invalid @enderror" id="estado" name="estado" value="{{ old('estado', $seguimiento->estado) }}" required placeholder="Ej. En planificaci├│n, Compras listas, Coordinado con log├¡stica">
                @error('estado')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="porcentaje_avance" class="form-label">Porcentaje de Avance: <span id="rangoValor" class="fw-bold text-warning">{{ $seguimiento->porcentaje_avance }}%</span></label>
                <input type="range" class="form-range @error('porcentaje_avance') is-invalid @enderror" min="0" max="100" step="5" id="porcentaje_avance" name="porcentaje_avance" value="{{ old('porcentaje_avance', $seguimiento->porcentaje_avance) }}">
                @error('porcentaje_avance')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="observacion" class="form-label">Observaciones / Detalles</label>
                <textarea class="form-control @error('observacion') is-invalid @enderror" id="observacion" name="observacion" rows="4" placeholder="Escriba comentarios u observaciones sobre el estado actual...">{{ old('observacion', $seguimiento->observacion) }}</textarea>
                @error('observacion')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-church">
                <i class="bi bi-save me-1"></i> Actualizar
            </button>
            <a href="{{ route('seguimientos.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i> Cancelar
            </a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
const range = document.getElementById('porcentaje_avance');
const label = document.getElementById('rangoValor');
range.addEventListener('input', function() {
    label.textContent = this.value + '%';
});
// Set initial value
label.textContent = range.value + '%';
</script>
@endsection
