{{-- resources/views/categoriaactividad/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Ver Categoría')

@section('content')

{{-- HEADER --}}
<div class="mb-8">
    <div class="flex items-center gap-2 text-xs text-stone-400 mb-3">
        <a href="{{ route('categoriaactividad.index') }}" class="hover:text-gold-600 transition-colors">Categorías</a>
        <span>›</span>
        <span class="text-stone-600">{{ $categoria->nombre }}</span>
    </div>
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="font-display text-4xl font-semibold text-stone-800">
                {{ $categoria->nombre }}
            </h1>
            <p class="text-stone-500 text-sm mt-1">Detalle de la categoría</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('categoriaactividad.edit', $categoria) }}"
               class="inline-flex items-center gap-2 bg-gold-500 hover:bg-gold-600 text-white text-sm font-semibold px-4 py-2.5 rounded-lg shadow transition-colors">
                ✏️ Editar
            </a>
            <a href="{{ route('categoriaactividad.index') }}"
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

<div class="max-w-xl space-y-4">

    {{-- CARD PRINCIPAL --}}
    <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
        {{-- Franja decorativa --}}
        <div class="h-1.5 bg-gradient-to-r from-gold-300 via-gold-500 to-gold-300"></div>

        <div class="p-8 space-y-5">
            {{-- ID + Estado --}}
            <div class="flex items-center justify-between">
                <span class="text-xs text-stone-400 font-mono">ID #{{ $categoria->id }}</span>
                @if($categoria->estado)
                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold px-3 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full"></span> Activa
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 bg-stone-100 text-stone-500 border border-stone-200 text-xs font-semibold px-3 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 bg-stone-400 rounded-full"></span> Inactiva
                    </span>
                @endif
            </div>

            {{-- Nombre --}}
            <div>
                <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">Nombre</p>
                <p class="font-display text-2xl font-semibold text-stone-800">{{ $categoria->nombre }}</p>
            </div>

            {{-- Descripción --}}
            <div>
                <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">Descripción</p>
                @if($categoria->descripcion)
                    <p class="text-stone-600 text-sm leading-relaxed">{{ $categoria->descripcion }}</p>
                @else
                    <p class="text-stone-400 text-sm italic">Sin descripción registrada</p>
                @endif
            </div>

            {{-- Fechas --}}
            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-stone-100">
                <div>
                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">Creado</p>
                    <p class="text-sm text-stone-600">{{ $categoria->created_at->format('d/m/Y') }}</p>
                    <p class="text-xs text-stone-400">{{ $categoria->created_at->format('H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">Actualizado</p>
                    <p class="text-sm text-stone-600">{{ $categoria->updated_at->format('d/m/Y') }}</p>
                    <p class="text-xs text-stone-400">{{ $categoria->updated_at->format('H:i') }}</p>
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
                  action="{{ route('categoriaactividad.destroy', $categoria) }}"
                  onsubmit="return confirm('¿Seguro que deseas eliminar «{{ addslashes($categoria->nombre) }}»? Esta acción es permanente.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-red-50 hover:bg-red-500 text-red-600 hover:text-white border border-red-200 hover:border-red-500 text-xs font-semibold px-4 py-2 rounded-lg transition-all">
                    🗑 Eliminar categoría
                </button>
            </form>
        </div>
    </div>

</div>
@endsection