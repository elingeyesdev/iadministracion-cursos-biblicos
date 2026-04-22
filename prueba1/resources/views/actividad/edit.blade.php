{{-- resources/views/actividad/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar Actividad')

@section('content')

{{-- HEADER --}}
<div class="mb-8">
    <div class="flex items-center gap-2 text-xs text-stone-400 mb-3">
        <a href="{{ route('actividad.index') }}" class="hover:text-gold-600 transition-colors">Actividades</a>
        <span>›</span>
        <a href="{{ route('actividad.show', $actividad) }}" class="hover:text-gold-600 transition-colors">{{ $actividad->nombre }}</a>
        <span>›</span>
        <span class="text-stone-600">Editar</span>
    </div>
    <h1 class="font-display text-4xl font-semibold text-stone-800">
        Editar <em class="text-gold-600 not-italic">Actividad</em>
    </h1>
    <p class="text-stone-500 text-sm mt-1">Modifica los datos de <strong class="text-stone-700">{{ $actividad->nombre }}</strong></p>
    <div class="mt-5 flex items-center gap-3">
        <div class="h-px flex-1 bg-gold-200"></div>
        <span class="text-gold-400 text-xs">✝</span>
        <div class="h-px flex-1 bg-gold-200"></div>
    </div>
</div>

<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-8">
        <form method="POST" action="{{ route('actividad.update', $actividad) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Nombre --}}
            <div>
                <label for="nombre" class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-2">
                    Nombre <span class="text-gold-500">*</span>
                </label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="{{ old('nombre', $actividad->nombre) }}"
                    autofocus
                    class="w-full px-4 py-2.5 bg-stone-50 border {{ $errors->has('nombre') ? 'border-red-400 bg-red-50' : 'border-stone-200' }} rounded-lg text-sm text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-gold-300 focus:border-gold-400 focus:bg-white transition"
                >
                @error('nombre')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><span>⚠</span> {{ $message }}</p>
                @enderror
            </div>

            {{-- Categoría y Responsable --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Categoría --}}
                <div>
                    <label for="id_categoria" class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-2">
                        Categoría <span class="text-gold-500">*</span>
                    </label>
                    <select
                        id="id_categoria"
                        name="id_categoria"
                        class="w-full px-4 py-2.5 bg-stone-50 border {{ $errors->has('id_categoria') ? 'border-red-400 bg-red-50' : 'border-stone-200' }} rounded-lg text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-gold-300 focus:border-gold-400 focus:bg-white transition"
                    >
                        <option value="">Selecciona una categoría</option>
                        @forelse($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ old('id_categoria', $actividad->id_categoria) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                        @empty
                            <option disabled>No hay categorías disponibles</option>
                        @endforelse
                    </select>
                    @error('id_categoria')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><span>⚠</span> {{ $message }}</p>
                    @enderror
                </div>

                {{-- Responsable --}}
                <div>
                    <label for="id_responsable" class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-2">
                        Responsable <span class="text-gold-500">*</span>
                    </label>
                    <select
                        id="id_responsable"
                        name="id_responsable"
                        class="w-full px-4 py-2.5 bg-stone-50 border {{ $errors->has('id_responsable') ? 'border-red-400 bg-red-50' : 'border-stone-200' }} rounded-lg text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-gold-300 focus:border-gold-400 focus:bg-white transition"
                    >
                        <option value="">Selecciona un responsable</option>
                        @forelse($miembros as $miem)
                            <option value="{{ $miem->id }}" {{ old('id_responsable', $actividad->id_responsable) == $miem->id ? 'selected' : '' }}>
                                {{ $miem->nombre }}
                            </option>
                        @empty
                            <option disabled>No hay miembros disponibles</option>
                        @endforelse
                    </select>
                    @error('id_responsable')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><span>⚠</span> {{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Fechas --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Fecha Inicio --}}
                <div>
                    <label for="fecha_inicio" class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-2">
                        Fecha de Inicio <span class="text-gold-500">*</span>
                    </label>
                    <input
                        type="date"
                        id="fecha_inicio"
                        name="fecha_inicio"
                        value="{{ old('fecha_inicio', $actividad->fecha_inicio->format('Y-m-d')) }}"
                        class="w-full px-4 py-2.5 bg-stone-50 border {{ $errors->has('fecha_inicio') ? 'border-red-400 bg-red-50' : 'border-stone-200' }} rounded-lg text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-gold-300 focus:border-gold-400 focus:bg-white transition"
                    >
                    @error('fecha_inicio')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><span>⚠</span> {{ $message }}</p>
                    @enderror
                </div>

                {{-- Fecha Fin --}}
                <div>
                    <label for="fecha_fin" class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-2">
                        Fecha de Fin (Opcional)
                    </label>
                    <input
                        type="date"
                        id="fecha_fin"
                        name="fecha_fin"
                        value="{{ old('fecha_fin', $actividad->fecha_fin ? $actividad->fecha_fin->format('Y-m-d') : '') }}"
                        class="w-full px-4 py-2.5 bg-stone-50 border {{ $errors->has('fecha_fin') ? 'border-red-400 bg-red-50' : 'border-stone-200' }} rounded-lg text-sm text-stone-800 focus:outline-none focus:ring-2 focus:ring-gold-300 focus:border-gold-400 focus:bg-white transition"
                    >
                    @error('fecha_fin')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><span>⚠</span> {{ $message }}</p>
                    @enderror
                </div>
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
                    placeholder="Describe brevemente esta actividad…"
                    class="w-full px-4 py-2.5 bg-stone-50 border {{ $errors->has('descripcion') ? 'border-red-400 bg-red-50' : 'border-stone-200' }} rounded-lg text-sm text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-gold-300 focus:border-gold-400 focus:bg-white transition resize-none"
                >{{ old('descripcion', $actividad->descripcion) }}</textarea>
                @error('descripcion')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><span>⚠</span> {{ $message }}</p>
                @enderror
            </div>

            {{-- Estado --}}
            <div>
                <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-3">
                    Estado <span class="text-gold-500">*</span>
                </label>
                @php $estadoActual = old('estado', $actividad->estado); @endphp
                <div class="grid grid-cols-3 gap-3">
                    @foreach(['pendiente' => 'Pendiente', 'en_proceso' => 'En Proceso', 'finalizada' => 'Finalizada'] as $value => $label)
                        <label class="flex items-center gap-3 px-4 py-3 rounded-lg border cursor-pointer transition
                            {{ $estadoActual == $value ? 'border-gold-400 bg-gold-50' : 'border-stone-200 bg-stone-50 hover:border-stone-300' }}">
                            <input type="radio" name="estado" value="{{ $value }}"
                                   {{ $estadoActual == $value ? 'checked' : '' }}
                                   class="accent-amber-500">
                            <span class="text-sm font-semibold text-stone-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('estado')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><span>⚠</span> {{ $message }}</p>
                @enderror
            </div>

            {{-- Botones --}}
            <div class="flex gap-3 pt-2 border-t border-stone-100">
                <button type="submit"
                    class="flex-1 sm:flex-none px-6 py-2.5 bg-gold-500 hover:bg-gold-600 text-white text-sm font-semibold rounded-lg shadow transition-colors">
                    Guardar cambios
                </button>
                <a href="{{ route('actividad.show', $actividad) }}"
                   class="flex-1 sm:flex-none px-6 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 text-sm font-medium rounded-lg text-center transition-colors">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
