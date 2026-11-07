@extends('layouts.app')

@section('title', 'Registrar Pago')

@section('content')
<div class="glass-panel" style="padding: 30px; max-width: 600px; margin: 0 auto;">
    <h2 style="font-weight: 700; margin-bottom: 20px; color: var(--text-primary);">Registrar Pago de Inscripción</h2>

    <div style="background: rgba(37,99,235,.1); border: 1px solid rgba(37,99,235,.25); padding: 14px 18px; border-radius: 12px; margin-bottom: 20px;">
        <div style="font-size: 13px; color: var(--text-secondary);">Miembro / Curso</div>
        <div style="font-size: 18px; font-weight: 700; color: var(--text-primary);">{{ $inscripcion->miembro->nombre ?? 'N/A' }}</div>
        <div style="font-size: 14px; color: var(--text-secondary);">{{ $inscripcion->curso->nombre ?? 'N/A' }}</div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
        <div style="background: rgba(47,158,68,.12); border: 1px solid rgba(47,158,68,.25); padding: 14px 18px; border-radius: 12px;">
            <div style="font-size: 13px; color: var(--text-secondary);">Costo Inscripción</div>
            <div style="font-size: 22px; font-weight: 800; color: var(--text-primary);">Bs {{ number_format($inscripcion->curso->monto_inscripcion, 2) }}</div>
        </div>
        <div style="background: rgba(220,38,38,.12); border: 1px solid rgba(220,38,38,.25); padding: 14px 18px; border-radius: 12px;">
            <div style="font-size: 13px; color: var(--text-secondary);">Saldo Pendiente</div>
            <div style="font-size: 22px; font-weight: 800; color: var(--error);">Bs {{ number_format($inscripcion->saldo_pendiente, 2) }}</div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>
                
@endforeach
            </ul>
        </div>
    @endif

    @if(auth()->user()->puede('inscripciones', 'crear'))
    <form method="POST" action="{{ route('pagos.store', $inscripcion->id) }}">
        @csrf

        <div class="form-group">
            <label for="monto" class="form-label">Monto (Bs)</label>
            <input type="number" step="0.01" min="0.01" max="{{ $inscripcion->saldo_pendiente }}" name="monto" id="monto" class="form-control" value="{{ old('monto') }}" required autocomplete="off">
            <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">Máximo Bs {{ number_format($inscripcion->saldo_pendiente, 2) }}</div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="fecha_pago" class="form-label">Fecha de Pago</label>
                <input id="fecha_pago" type="date" name="fecha_pago" class="form-control" value="{{ old('fecha_pago', date('Y-m-d')) }}" required>
            </div>

            <div class="form-group">
                <label for="metodo_pago" class="form-label">Método de Pago</label>
                <select id="metodo_pago" name="metodo_pago" class="form-control" required>
                    <option value="Efectivo" {{ old('metodo_pago') == 'Efectivo' ? 'selected' : '' }}>Efectivo</option>
                    <option value="Transferencia" {{ old('metodo_pago') == 'Transferencia' ? 'selected' : '' }}>Transferencia</option>
                    <option value="Tarjeta" {{ old('metodo_pago') == 'Tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="comprobante" class="form-label">Comprobante / Recibo (Opcional)</label>
            <input id="comprobante" type="text" name="comprobante" class="form-control" value="{{ old('comprobante') }}">
        </div>

        <div style="display: flex; gap: 15px; margin-top: 30px;">
            <button type="submit" class="btn">Guardar Pago</button>
            <a href="{{ route('inscripciones.gestion', $inscripcion->curso_id) }}" class="btn" style="background: transparent; border: 1px solid var(--card-border); color: var(--text-secondary); text-decoration: none; text-align: center;">Cancelar</a>
        </div>
    </form>
    @endif
</div>
@endsection
