@extends('layouts.app')

@section('title', 'Registrar Ingreso')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('ingresos.index') }}" class="text-gold-500 text-decoration-none">Ingresos</a></li>
    <li class="breadcrumb-item active text-white" aria-current="page">Registrar Ingreso</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Registrar Ingreso</h1>
            <p class="text-muted mb-0">Registra un nuevo ingreso financiero asociándolo a un culto específico.</p>
        </div>
        <a href="{{ route('ingresos.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 mb-4 bg-danger text-white">
            <h5 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Por favor corrige los siguientes errores:</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important; max-width: 800px;">
        <div class="card-header border-0 bg-dark py-3">
            <h5 class="card-title text-white mb-0"><i class="bi bi-cash-coin text-gold-500 me-2"></i>Detalles del Ingreso</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('ingresos.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_culto" class="form-label text-white fw-semibold">Culto Asociado <span class="text-danger">*</span></label>
                        <select name="id_culto" id="id_culto" class="form-select bg-transparent text-white border-secondary @error('id_culto') is-invalid @enderror" required style="background-color: var(--church-surface, #1e1e2d) !important;">
                            <option value="" disabled {{ old('id_culto') === null ? 'selected' : '' }} style="background: #1e1e2d; color: #fff;">-- Selecciona un culto --</option>
                            @foreach($cultos as $culto)
                                <option value="{{ $culto->id_culto }}" {{ old('id_culto') == $culto->id_culto ? 'selected' : '' }} style="background: #1e1e2d; color: #fff;">
                                    {{ $culto->nombre }} — {{ ucfirst($culto->dia_semana) }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_culto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="monto_total" class="form-label text-white fw-semibold">Monto Total (Bs.) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="monto_total" id="monto_total" class="form-control bg-transparent text-white border-secondary @error('monto_total') is-invalid @enderror" value="{{ old('monto_total') }}" placeholder="0.00" required>
                        @error('monto_total') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha" class="form-label text-white fw-semibold">Fecha <span class="text-danger">*</span></label>
                        <input type="date" name="fecha" id="fecha" class="form-control bg-transparent text-white border-secondary @error('fecha') is-invalid @enderror" value="{{ old('fecha', date('Y-m-d')) }}" required>
                        @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="metodo_pago" class="form-label text-white fw-semibold">Método de Pago <span class="text-danger">*</span></label>
                        <select name="metodo_pago" id="metodo_pago" class="form-select bg-transparent text-white border-secondary @error('metodo_pago') is-invalid @enderror" required style="background-color: var(--church-surface, #1e1e2d) !important;">
                            <option value="efectivo" {{ old('metodo_pago') === 'efectivo' ? 'selected' : '' }} style="background: #1e1e2d; color: #fff;">Efectivo</option>
                            <option value="transferencia" {{ old('metodo_pago') === 'transferencia' ? 'selected' : '' }} style="background: #1e1e2d; color: #fff;">Transferencia</option>
                            <option value="otro" {{ old('metodo_pago', 'otro') === 'otro' ? 'selected' : '' }} style="background: #1e1e2d; color: #fff;">Otro</option>
                        </select>
                        @error('metodo_pago') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="tipo" class="form-label text-white fw-semibold">Tipo / Concepto de Ingreso</label>
                    <input type="text" name="tipo" id="tipo" class="form-control bg-transparent text-white border-secondary @error('tipo') is-invalid @enderror" value="{{ old('tipo') }}" placeholder="Ej. Diezmo, Ingreso Pro-Templo, Ingreso del Domingo...">
                    @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mt-4 d-flex gap-3">
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="bi bi-save-fill me-1"></i> Guardar Ingreso
                    </button>
                    <a href="{{ route('ingresos.index') }}" class="btn btn-secondary px-4 py-2">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
