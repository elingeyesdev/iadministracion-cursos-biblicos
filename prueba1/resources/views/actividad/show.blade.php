{{-- resources/views/actividad/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Ver Actividad')

@section('content')

{{-- HEADER --}}
<div class="mb-8">
    <div class="flex items-center gap-2 text-xs text-stone-400 mb-3">
        <a href="{{ route('actividad.index') }}" class="hover:text-gold-600 transition-colors">Actividades</a>
        <span>›</span>
        <span class="text-stone-600">{{ $actividad->nombre }}</span>
    </div>
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="font-display text-4xl font-semibold text-stone-800">
                {{ $actividad->nombre }}
            </h1>
            <p class="text-stone-500 text-sm mt-1">Detalle de la actividad</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('actividad.edit', $actividad) }}"
               class="inline-flex items-center gap-2 bg-gold-500 hover:bg-gold-600 text-white text-sm font-semibold px-4 py-2.5 rounded-lg shadow transition-colors">
                ✏️ Editar
            </a>
            <a href="{{ route('actividad.index') }}"
               class="inline-flex items-center gap-2 bg-stone-100 hover:bg-stone-200 text-stone-700 text-sm font-medium px-4 py-2.5 rounded-lg transition-colors border border-stone-200">
                ← Volver
            </a>
        </div>
    </div>
    <div class="mt-5 flex items-center gap-3">
        <div class="h-px flex-1 bg-gold-200"></div>
        <span class="text-gold-400 text-xs">✝</span>
        <div class="h-px flex-1 bg-gold-200"></div>
    </div>
</div>

<div class="max-w-2xl space-y-4">

    {{-- CARD PRINCIPAL --}}
    <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
        {{-- Franja decorativa --}}
        <div class="h-1.5 bg-gradient-to-r from-gold-300 via-gold-500 to-gold-300"></div>

        <div class="p-8 space-y-5">
            {{-- ID + Estado --}}
            <div class="flex items-center justify-between">
                <span class="text-xs text-stone-400 font-mono">ID #{{ $actividad->id }}</span>
                @php
                    $estadoClass = match($actividad->estado) {
                        'pendiente' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                        'en_proceso' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'finalizada' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    };
                    $estadoLabel = match($actividad->estado) {
                        'pendiente' => 'Pendiente',
                        'en_proceso' => 'En Proceso',
                        'finalizada' => 'Finalizada',
                    };
                @endphp
                <span class="inline-flex items-center gap-1.5 {{ $estadoClass }} border text-xs font-semibold px-3 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full" style="background-color: currentColor;"></span> {{ $estadoLabel }}
                </span>
            </div>

            {{-- Nombre --}}
            <div>
                <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">Nombre</p>
                <p class="font-display text-2xl font-semibold text-stone-800">{{ $actividad->nombre }}</p>
            </div>

            {{-- Categoría y Responsable --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">Categoría</p>
                    @if($actividad->categoria)
                        <span class="inline-flex items-center bg-gold-50 text-gold-700 border border-gold-200 text-sm font-semibold px-3 py-1.5 rounded-lg">
                            {{ $actividad->categoria->nombre }}
                        </span>
                    @else
                        <p class="text-stone-400 text-sm italic">Sin categoría</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">Responsable</p>
                    @if($actividad->responsable)
                        <p class="text-stone-700 text-sm font-semibold">{{ $actividad->responsable->nombre }}</p>
                    @else
                        <p class="text-stone-400 text-sm italic">Sin asignar</p>
                    @endif
                </div>
            </div>

            {{-- Fechas --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">Fecha de Inicio</p>
                    <p class="text-stone-700 text-sm font-semibold">{{ $actividad->fecha_inicio->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">Fecha de Fin</p>
                    @if($actividad->fecha_fin)
                        <p class="text-stone-700 text-sm font-semibold">{{ $actividad->fecha_fin->format('d/m/Y') }}</p>
                    @else
                        <p class="text-stone-400 text-sm italic">No especificada</p>
                    @endif
                </div>
            </div>

            {{-- Descripción --}}
            <div>
                <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">Descripción</p>
                @if($actividad->descripcion)
                    <p class="text-stone-600 text-sm leading-relaxed">{{ $actividad->descripcion }}</p>
                @else
                    <p class="text-stone-400 text-sm italic">Sin descripción registrada</p>
                @endif
            </div>

            {{-- Fechas de creación --}}
            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-stone-100">
                <div>
                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">Creado</p>
                    <p class="text-sm text-stone-600">{{ $actividad->created_at->format('d/m/Y') }}</p>
                    <p class="text-xs text-stone-400">{{ $actividad->created_at->format('H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">Actualizado</p>
                    <p class="text-sm text-stone-600">{{ $actividad->updated_at->format('d/m/Y') }}</p>
                    <p class="text-xs text-stone-400">{{ $actividad->updated_at->format('H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ZONA DE PELIGRO --}}
    <div class="bg-white rounded-xl shadow-sm border border-red-100 p-5">
        <p class="text-xs font-semibold text-red-400 uppercase tracking-wider mb-3">Zona de peligro</p>
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <p class="text-sm text-stone-500">Una vez eliminada, esta acción no se puede deshacer.</p>
            <form method="POST"
                  action="{{ route('actividad.destroy', $actividad) }}"
                  onsubmit="return confirm('¿Seguro que deseas eliminar «{{ addslashes($actividad->nombre) }}»? Esta acción es permanente.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-red-50 hover:bg-red-500 text-red-600 hover:text-white border border-red-200 hover:border-red-500 text-xs font-semibold px-4 py-2 rounded-lg transition-all">
                    🗑 Eliminar actividad
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
