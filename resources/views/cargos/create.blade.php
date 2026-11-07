@extends('layouts.app')

@section('title', 'Nuevo Cargo')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('cargos.index') }}" class="text-gold-500 text-decoration-none">Cargos</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Nuevo</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <h2 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-briefcase me-2"></i>Crear Nuevo Cargo</h2>

    <form action="{{ route('cargos.store') }}" method="POST">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre del Cargo <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej. Pastor Principal">
                @error('nombre')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="nivel_jerarquico" class="form-label">Nivel Jer├írquico</label>
                <input type="number" min="1" class="form-control @error('nivel_jerarquico') is-invalid @enderror" id="nivel_jerarquico" name="nivel_jerarquico" value="{{ old('nivel_jerarquico', 1) }}">
                @error('nivel_jerarquico')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="descripcion" class="form-label">Descripci├│n</label>
                <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="3" placeholder="Responsabilidades del cargo...">{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-church">
                <i class="bi bi-save me-1"></i> Guardar
            </button>
            <a href="{{ route('cargos.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i> Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
