@extends('layouts.app')

@section('title', 'Editar Ministerio')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('ministerios.index') }}" class="text-gold-500 text-decoration-none">Ministerios</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Editar</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <h2 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-pencil-square me-2"></i>Editar Ministerio: {{ $ministerio->nombre }}</h2>

    <form action="{{ route('ministerios.update', $ministerio->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre del Ministerio <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre', $ministerio->nombre) }}" required placeholder="Ej. Ministerio de Alabanza">
                @error('nombre')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="id_lider" class="form-label">Líder / Encargado</label>
                <select class="form-select @error('id_lider') is-invalid @enderror" id="id_lider" name="id_lider">
                    <option value="">Seleccione un líder...</option>
                    @foreach($miembros as $miembro)
                        <option value="{{ $miembro->id }}" {{ old('id_lider', $ministerio->id_lider) == $miembro->id ? 'selected' : '' }}>
                            {{ $miembro->nombre }} ({{ $miembro->email }})
                        </option>
                    @endforeach
                </select>
                @error('id_lider')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="4" placeholder="Describa el propósito y actividades de este ministerio...">{{ old('descripcion', $ministerio->descripcion) }}</textarea>
                @error('descripcion')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-church">
                <i class="bi bi-save me-1"></i> Actualizar
            </button>
            <a href="{{ route('ministerios.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i> Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
