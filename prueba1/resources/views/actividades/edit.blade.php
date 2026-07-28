@extends('layouts.app')

@section('title', 'Editar Actividad')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('actividades.index') }}" class="text-gold-500 text-decoration-none">Actividades</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Editar</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <h2 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-pencil-square me-2"></i>Editar Actividad: {{ $actividad->nombre }}</h2>

    <form action="{{ route('actividades.update', $actividad->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row g-3">
            <div class="col-md-6">
                <label for="id_ministerio" class="form-label">Ministerio Organizador <span class="text-danger">*</span></label>
                <select class="form-select @error('id_ministerio') is-invalid @enderror" id="id_ministerio" name="id_ministerio" required>
                    <option value="" disabled>Seleccione un ministerio...</option>
                    @foreach($ministerios as $ministerio)
                        <option value="{{ $ministerio->id }}" {{ old('id_ministerio', $actividad->id_ministerio) == $ministerio->id ? 'selected' : '' }}>
                            {{ $ministerio->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('id_ministerio')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre de la Actividad <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre', $actividad->nombre) }}" required placeholder="Ej. Campamento de Jóvenes">
                @error('nombre')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="fecha" class="form-label">Fecha de Realización <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('fecha') is-invalid @enderror" id="fecha" name="fecha" value="{{ old('fecha', $actividad->fecha) }}" required>
                @error('fecha')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="lugar" class="form-label">Lugar / Ubicación</label>
                <input type="text" class="form-control @error('lugar') is-invalid @enderror" id="lugar" name="lugar" value="{{ old('lugar', $actividad->lugar) }}" placeholder="Ej. Templo Central, Auditorio B">
                @error('lugar')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                    <option value="planificada" {{ old('estado', $actividad->estado) == 'planificada' ? 'selected' : '' }}>Planificada</option>
                    <option value="en_curso" {{ old('estado', $actividad->estado) == 'en_curso' ? 'selected' : '' }}>En Curso</option>
                    <option value="completada" {{ old('estado', $actividad->estado) == 'completada' ? 'selected' : '' }}>Completada</option>
                    <option value="cancelada" {{ old('estado', $actividad->estado) == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                </select>
                @error('estado')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="descripcion" class="form-label">Descripción / Detalles</label>
                <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="4" placeholder="Describa el objetivo y programa de la actividad...">{{ old('descripcion', $actividad->descripcion) }}</textarea>
                @error('descripcion')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-church">
                <i class="bi bi-save me-1"></i> Actualizar
            </button>
            <a href="{{ route('actividades.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i> Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
