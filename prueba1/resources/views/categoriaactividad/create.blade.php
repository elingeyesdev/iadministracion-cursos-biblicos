{{-- resources/views/categoriaactividad/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nueva Categoría')

@section('content')

{{-- HEADER --}}
<div class="mb-8">
    <div class="flex items-center gap-2 text-xs text-stone-400 mb-3">
        <a href="{{ route('categoriaactividad.index') }}" class="hover:text-gold-600 transition-colors">Categorías</a>
        <span>›</span>
        <span class="text-stone-600">Nueva</span>
    </div>
    <h1 class="font-display text-4xl font-semibold text-stone-800">
        Nueva <em class="text-gold-600 not-italic">Categoría</em>
    </h1>
    <p class="text-stone-500 text-sm mt-1">Agrega una nueva categoría para organizar las actividades</p>
    <div class="mt-5 flex items-center gap-3">
        <div class="h-px flex-1 bg-gold-200"></div>
        <span class="text-gold-400 text-xs">✝</span>
        <div class="h-px flex-1 bg-gold-200"></div>
    </div>
</div>

<div class="max-w-xl">
    <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-8">
        <form method="POST" action="{{ route('categoriaactividad.store') }}" class="space-y-6">
            @csrf

            {{-- Nombre --}}
            <div>
                <label for="nombre" class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-2">
                    Nombre <span class="text-gold-500">*</span>
                </label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="{{ old('nombre') }}"
                    placeholder="Ej: Grupos de Oración, Jóvenes, Coro…"
                    autofocus
                    class="w-full px-4 py-2.5 bg-stone-50 border {{ $errors->has('nombre') ? 'border-red-400 bg-red-50' : 'border-stone-200' }} rounded-lg text-sm text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-gold-300 focus:border-gold-400 focus:bg-white transition"
                >
                @error('nombre')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <span>⚠</span> {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Descripción --}}
            <div>
                <label for="descripcion" class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-2">
                    Descripción
                </label>
                <textarea
                    id="descripcion"
                    name="descripcion"
                    rows="4"
                    placeholder="Describe brevemente esta categoría de actividad…"
                    class="w-full px-4 py-2.5 bg-stone-50 border {{ $errors->has('descripcion') ? 'border-red-400 bg-red-50' : 'border-stone-200' }} rounded-lg text-sm text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-gold-300 focus:border-gold-400 focus:bg-white transition resize-none"
                >{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <span>⚠</span> {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Estado --}}
            <div>
                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-3">
                    Estado <span class="text-gold-500">*</span>
                </label>
                <div class="flex gap-3">
                    <label class="flex-1 flex items-center gap-3 px-4 py-3 rounded-lg border cursor-pointer transition
                        {{ old('estado', '1') == '1' ? 'border-gold-400 bg-gold-50' : 'border-stone-200 bg-stone-50 hover:border-stone-300' }}">
                        <input type="radio" name="estado" value="1"
                               {{ old('estado', '1') == '1' ? 'checked' : '' }}
                               class="accent-amber-500">
                        <div>
                            <span class="text-sm font-semibold text-stone-700">Activa</span>
                            <p class="text-xs text-stone-400">Visible y en uso</p>
                        </div>
                    </label>
                    <label class="flex-1 flex items-center gap-3 px-4 py-3 rounded-lg border cursor-pointer transition
                        {{ old('estado') == '0' ? 'border-gold-400 bg-gold-50' : 'border-stone-200 bg-stone-50 hover:border-stone-300' }}">
                        <input type="radio" name="estado" value="0"
                               {{ old('estado') == '0' ? 'checked' : '' }}
                               class="accent-amber-500">
                        <div>
                            <span class="text-sm font-semibold text-stone-700">Inactiva</span>
                            <p class="text-xs text-stone-400">Deshabilitada</p>
                        </div>
                    </label>
                </div>
                @error('estado')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <span>⚠</span> {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Botones --}}
            <div class="flex gap-3 pt-2 border-t border-stone-100">
                <button type="submit"
                    class="flex-1 sm:flex-none px-6 py-2.5 bg-gold-500 hover:bg-gold-600 text-white text-sm font-semibold rounded-lg shadow transition-colors">
                    Guardar categoría
                </button>
                <a href="{{ route('categoriaactividad.index') }}"
                   class="flex-1 sm:flex-none px-6 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 text-sm font-medium rounded-lg text-center transition-colors">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection