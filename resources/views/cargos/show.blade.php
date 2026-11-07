@extends('layouts.app')

@section('title', 'Detalle del Cargo')

@section('content')
<div class="glass-panel" style="padding: 30px; max-width: 700px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-weight: 700; color: var(--text-primary);">Detalle del Cargo</h2>
        @if($cargo->trashed())
            <span class="badge badge-inactive">Eliminado</span>
        @endif
    </div>

    <div style="border: 1px solid var(--card-border); border-radius: 10px; overflow: hidden; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid var(--card-border);">
            <span style="color: var(--text-secondary);">Nombre</span>
            <span style="font-weight: 600; color: var(--text-primary);">{{ $cargo->nombre }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid var(--card-border);">
            <span style="color: var(--text-secondary);">Nivel Jerárquico</span>
            <span style="font-weight: 600; color: var(--text-primary);">{{ $cargo->nivel_jerarquico }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 14px 18px;">
            <span style="color: var(--text-secondary);">Descripción</span>
            <span style="font-weight: 600; color: var(--text-primary);">{{ $cargo->descripcion ?? 'N/A' }}</span>
        </div>
    </div>

    <div style="display: flex; gap: 15px; margin-top: 30px;">
        @if(auth()->user()->puede('cargos', 'editar'))
        <a href="{{ route('cargos.edit', $cargo->id) }}" class="btn" style="text-decoration: none; text-align: center;">Editar</a>
        @endif
        <a href="{{ route('cargos.index') }}" class="btn" style="background: transparent; border: 1px solid var(--card-border); color: var(--text-secondary); text-decoration: none; text-align: center;">Volver</a>
    </div>
</div>
@endsection
