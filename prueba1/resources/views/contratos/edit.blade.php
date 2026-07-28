@extends('layouts.app')

@section('title', 'Editar Contrato')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('contratos.index') }}" class="text-gold-500 text-decoration-none">Contratos</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Editar</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <h2 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-pencil-square me-2"></i>Editar Contrato</h2>

    <form action="{{ route('contratos.update', $contrato->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row g-3">
            <div class="col-md-6">
                <label for="id_miembro" class="form-label">Miembro / Contratista <span class="text-danger">*</span></label>
                <select class="form-select @error('id_miembro') is-invalid @enderror" id="id_miembro" name="id_miembro" required>
                    <option value="" disabled>Seleccione un miembro...</option>
                    @foreach($miembros as $miembro)
                        <option value="{{ $miembro->id }}" {{ old('id_miembro', $contrato->id_miembro) == $miembro->id ? 'selected' : '' }}>
                            {{ $miembro->nombre }} ({{ $miembro->email }})
                        </option>
                    @endforeach
                </select>
                @error('id_miembro')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="monto" class="form-label">Monto del Contrato ($)</label>
                <input type="number" step="0.01" class="form-control @error('monto') is-invalid @enderror" id="monto" name="monto" value="{{ old('monto', $contrato->monto) }}" placeholder="0.00">
                @error('monto')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="fecha_inicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', $contrato->fecha_inicio) }}" required>
                @error('fecha_inicio')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="fecha_fin" class="form-label">Fecha de Término</label>
                <input type="date" class="form-control @error('fecha_fin') is-invalid @enderror" id="fecha_fin" name="fecha_fin" value="{{ old('fecha_fin', $contrato->fecha_fin) }}">
                @error('fecha_fin')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="estado" class="form-label">Estado del Contrato <span class="text-danger">*</span></label>
                <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                    <option value="activo" {{ old('estado', $contrato->estado) == 'activo' ? 'selected' : '' }}>Activo / Vigente</option>
                    <option value="finalizado" {{ old('estado', $contrato->estado) == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                    <option value="cancelado" {{ old('estado', $contrato->estado) == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                </select>
                @error('estado')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="descripcion" class="form-label">Descripción / Detalles del Acuerdo</label>
                <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="4" placeholder="Escriba los términos del contrato, servicios prestados, etc...">{{ old('descripcion', $contrato->descripcion) }}</textarea>
                @error('descripcion')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-church">
                <i class="bi bi-save me-1"></i> Actualizar
            </button>
            <a href="{{ route('contratos.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i> Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
