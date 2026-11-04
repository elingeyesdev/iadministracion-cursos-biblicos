@extends('layouts.app')

@section('title', 'Editar Inscripción')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('inscripciones.index') }}" class="text-gold-500 text-decoration-none">Inscripciones</a></li>
    <li class="breadcrumb-item"><a href="{{ route('inscripciones.gestion', $inscripcion->curso_id) }}" class="text-gold-500 text-decoration-none">{{ $inscripcion->curso->nombre ?? 'Curso' }}</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Editar</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <h2 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-journal-richtext me-2"></i>Editar Inscripción</h2>

    <p><strong>Miembro:</strong> {{ $inscripcion->miembro->nombre ?? 'Miembro eliminado' }} &middot; <strong>Curso:</strong> {{ $inscripcion->curso->nombre ?? 'Curso eliminado' }}</p>

    <form action="{{ route('inscripciones.update', $inscripcion->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label for="fecha_inscripcion" class="form-label">Fecha de Inscripción <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('fecha_inscripcion') is-invalid @enderror" id="fecha_inscripcion" name="fecha_inscripcion" value="{{ old('fecha_inscripcion', $inscripcion->fecha_inscripcion) }}" required>
                @error('fecha_inscripcion')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                    @foreach(['Activo', 'Retirado', 'Completado'] as $estado)
                        <option value="{{ $estado }}" {{ old('estado', $inscripcion->estado) === $estado ? 'selected' : '' }}>{{ $estado }}</option>
                    @endforeach
                </select>
                @error('estado')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-church">
                <i class="bi bi-save me-1"></i> Actualizar
            </button>
            <a href="{{ route('inscripciones.gestion', $inscripcion->curso_id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i> Cancelar
            </a>
        </div>
    </form>
</div>
@endsection