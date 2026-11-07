@extends('layouts.app')

@section('title', 'Detalle del Líder de Iglesia')

@section('content')
<div class="glass-panel" style="padding: 30px; max-width: 700px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-weight: 700; color: var(--text-primary);">Detalle del Líder de Iglesia</h2>
        <div style="display: flex; gap: 10px;">
            @if(auth()->user()->puede('lideres', 'editar'))
            <a href="{{ route('lideres.edit', $lider->id) }}" class="btn" style="width: auto; padding: 8px 16px; font-size: 0.9rem;">Editar</a>
            @endif
            <a href="{{ route('lideres.index') }}" class="btn" style="width: auto; padding: 8px 16px; font-size: 0.9rem; background: transparent; border: 1px solid var(--card-border); color: var(--text-secondary); text-decoration: none;">Volver</a>
        </div>
    </div>

    <div style="border: 1px solid var(--card-border); border-radius: 10px; overflow: hidden; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid var(--card-border);">
            <span style="color: var(--text-secondary);">Nombre</span>
            <span style="font-weight: 600; color: var(--text-primary);">{{ $lider->nombre }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid var(--card-border);">
            <span style="color: var(--text-secondary);">Cargo</span>
            <span style="font-weight: 600; color: var(--text-primary);">{{ $lider->cargo?->nombre ?? 'N/A' }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid var(--card-border);">
            <span style="color: var(--text-secondary);">Teléfono</span>
            <span style="font-weight: 600; color: var(--text-primary);">{{ $lider->telefono ?? 'N/A' }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid var(--card-border);">
            <span style="color: var(--text-secondary);">Fecha de Inicio</span>
            <span style="font-weight: 600; color: var(--text-primary);">{{ \Carbon\Carbon::parse($lider->fecha_inicio)->format('d/m/Y') }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 14px 18px;">
            <span style="color: var(--text-secondary);">Estado</span>
            <span style="font-weight: 600; color: var(--text-primary);">
                <span class="badge {{ $lider->activo ? 'badge-active' : 'badge-inactive' }}">{{ $lider->activo ? 'Activo' : 'Inactivo' }}</span>
            </span>
        </div>
    </div>
</div>
@endsection
