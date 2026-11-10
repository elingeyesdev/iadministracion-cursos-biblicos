@extends('layouts.app')

@section('title', 'Nuevo Líder de Iglesia')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('lideres.index') }}" class="text-gold-500 text-decoration-none">Líderes</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Nuevo</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <h2 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-person-badge me-2"></i>Registrar Líder de Iglesia</h2>

    <form action="{{ route('lideres.store') }}" method="POST">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                @error('nombre')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="id_cargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                <select class="form-select @error('id_cargo') is-invalid @enderror" id="id_cargo" name="id_cargo" required>
                    <option value="">-- Seleccionar Cargo --</option>
                    @foreach($cargos as $cargo)
                        <option value="{{ $cargo->id }}" {{ old('id_cargo') == $cargo->id ? 'selected' : '' }}>{{ $cargo->nombre }}</option>
                    @endforeach
                </select>
                @error('id_cargo')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control @error('telefono') is-invalid @enderror" id="telefono" name="telefono" value="{{ old('telefono') }}">
                @error('telefono')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="fecha_inicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', date('Y-m-d')) }}" required>
                @error('fecha_inicio')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4 d-flex align-items-center">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" {{ old('activo') ? 'checked' : 'checked' }}>
                    <label class="form-check-label" for="activo">Activo</label>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-church">
                <i class="bi bi-save me-1"></i> Guardar
            </button>
            <a href="{{ route('lideres.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i> Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
