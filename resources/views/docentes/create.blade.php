@extends('layouts.app')

@section('title', 'Nuevo Docente')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('docentes.index') }}" class="text-gold-500 text-decoration-none">Docentes</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Nuevo</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <h2 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-mortarboard me-2"></i>Registrar Nuevo Docente</h2>

    <form action="{{ route('docentes.store') }}" method="POST">
        @csrf
        
        <div class="row g-3">
            <div class="col-md-6">
                <label for="id_miembro" class="form-label">Miembro <span class="text-danger">*</span></label>
                <select class="form-select @error('id_miembro') is-invalid @enderror" id="id_miembro" name="id_miembro" required>
                    <option value="" disabled selected>Seleccione un miembro...</option>
                    @foreach($miembros as $miembro)
                        <option value="{{ $miembro->id }}" {{ old('id_miembro') == $miembro->id ? 'selected' : '' }}>
                            {{ $miembro->nombre }} ({{ $miembro->email }})
                        </option>
                    @endforeach
                </select>
                @error('id_miembro')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="especialidad" class="form-label">Especialidad</label>
                <input type="text" class="form-control @error('especialidad') is-invalid @enderror" id="especialidad" name="especialidad" value="{{ old('especialidad') }}" placeholder="Ej. Teología, Consejería Familiar">
                @error('especialidad')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="titulo" class="form-label">Título Profesional / Académico</label>
                <input type="text" class="form-control @error('titulo') is-invalid @enderror" id="titulo" name="titulo" value="{{ old('titulo') }}" placeholder="Ej. Bachiller en Teología">
                @error('titulo')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 d-flex align-items-center">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" {{ old('activo', '1') ? 'checked' : '' }}>
                    <label class="form-check-label" for="activo">
                        Docente Activo
                    </label>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-church">
                <i class="bi bi-save me-1"></i> Guardar
            </button>
            <a href="{{ route('docentes.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i> Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
