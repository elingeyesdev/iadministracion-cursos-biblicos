@extends('layouts.app')

@section('title', 'Registrar Egreso')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('egresos.index') }}" class="text-gold-500 text-decoration-none">Egresos</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Registrar</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <h2 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-cash-stack me-2"></i>Registrar Egreso / Gasto</h2>

    <form action="{{ route('egresos.store') }}" method="POST">
        @csrf
        
        <div class="row g-3">
            <div class="col-md-6">
                <label for="tipo_egreso" class="form-label">Tipo de Egreso <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('tipo_egreso') is-invalid @enderror" id="tipo_egreso" name="tipo_egreso" value="{{ old('tipo_egreso') }}" required placeholder="Ej. Pago de Luz, Compra de Material Did├íctico">
                @error('tipo_egreso')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="monto" class="form-label">Monto del Egreso ($) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control @error('monto') is-invalid @enderror" id="monto" name="monto" value="{{ old('monto') }}" required placeholder="0.00">
                @error('monto')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="fecha" class="form-label">Fecha del Gasto <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('fecha') is-invalid @enderror" id="fecha" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required>
                @error('fecha')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="responsable" class="form-label">Responsable del Pago / Retiro</label>
                <input type="text" class="form-control @error('responsable') is-invalid @enderror" id="responsable" name="responsable" value="{{ old('responsable') }}" placeholder="Ej. Tesorero Juan">
                @error('responsable')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="comprobante" class="form-label">N├║mero de Comprobante / Boleta / Factura</label>
                <input type="text" class="form-control @error('comprobante') is-invalid @enderror" id="comprobante" name="comprobante" value="{{ old('comprobante') }}" placeholder="Ej. N┬░ Boleta 10243">
                @error('comprobante')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="id_ministerio" class="form-label">Asociar a Ministerio</label>
                <select class="form-select @error('id_ministerio') is-invalid @enderror" id="id_ministerio" name="id_ministerio">
                    <option value="" selected>Gasto General (Ninguno)</option>
                    @foreach($ministerios as $ministerio)
                        <option value="{{ $ministerio->id }}" {{ old('id_ministerio') == $ministerio->id ? 'selected' : '' }}>
                            {{ $ministerio->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('id_ministerio')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="id_contrato" class="form-label">Asociar a Contrato</label>
                <select class="form-select @error('id_contrato') is-invalid @enderror" id="id_contrato" name="id_contrato">
                    <option value="" selected>Ning├║n Contrato</option>
                    @foreach($contratos as $contrato)
                        <option value="{{ $contrato->id }}" {{ old('id_contrato') == $contrato->id ? 'selected' : '' }}>
                            Contrato #{{ $contrato->id }} - {{ $contrato->miembro->nombre ?? 'Miembro' }} ({{ Str::limit($contrato->descripcion, 30) }})
                        </option>
                    @endforeach
                </select>
                @error('id_contrato')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="descripcion" class="form-label">Descripci├│n Detallada del Gasto</label>
                <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="4" placeholder="Detalle los motivos y especificaciones del gasto...">{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-church">
                <i class="bi bi-save me-1"></i> Guardar
            </button>
            <a href="{{ route('egresos.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i> Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
