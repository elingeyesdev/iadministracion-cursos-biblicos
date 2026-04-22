{{-- resources/views/actividad/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Actividades')

@section('content')

{{-- PAGE HEADER --}}
<div class="mb-8">
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <p class="text-gold-600 text-xs tracking-widest uppercase font-medium mb-1">Gestión</p>
            <h1 class="font-display text-4xl font-semibold text-stone-800 leading-tight">
                <em class="text-gold-600 not-italic">Actividades</em>
            </h1>
            <p class="text-stone-500 text-sm mt-1">Organiza y administra todas las actividades</p>
        </div>
        <a href="{{ route('actividad.create') }}"
           class="inline-flex items-center gap-2 bg-gold-500 hover:bg-gold-600 text-white font-semibold text-sm px-5 py-2.5 rounded-lg shadow transition-colors whitespace-nowrap">
            <span class="text-base leading-none">+</span> Nueva Actividad
        </a>
    </div>

    {{-- Separador --}}
    <div class="mt-5 flex items-center gap-3">
        <div class="h-px flex-1 bg-gold-200"></div>
        <span class="text-gold-400 text-xs">✝</span>
        <div class="h-px flex-1 bg-gold-200"></div>
    </div>
</div>

{{-- BUSCADOR --}}
<form method="GET" action="{{ route('actividad.index') }}" class="mb-6">
    <div class="flex gap-2 max-w-md">
        <div class="relative flex-1">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 text-sm">🔍</span>
            <input
                type="text"
                name="busqueda"
                value="{{ $busqueda ?? '' }}"
                placeholder="Buscar actividad…"
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-stone-200 rounded-lg text-sm text-stone-700 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-gold-300 focus:border-gold-400 shadow-sm transition"
            >
        </div>
        <button type="submit"
            class="px-4 py-2.5 bg-stone-800 hover:bg-stone-900 text-white text-sm font-medium rounded-lg shadow transition-colors">
            Buscar
        </button>
        @if($busqueda)
            <a href="{{ route('actividad.index') }}"
               class="px-3 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-600 text-sm rounded-lg transition-colors border border-stone-200">
                ✕
            </a>
        @endif
    </div>
    @if($busqueda)
        <p class="text-stone-500 text-xs mt-2">
            Resultados para: <strong class="text-stone-700">"{{ $busqueda }}"</strong>
        </p>
    @endif
</form>

{{-- TABLA --}}
<div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
    @if($actividades->isEmpty())
        <div class="py-16 text-center">
            <span class="text-gold-300 text-4xl block mb-3">✝</span>
            <p class="text-stone-500 text-sm">
                @if($busqueda)
                    No se encontraron actividades para <strong>"{{ $busqueda }}"</strong>
                @else
                    Aún no hay actividades registradas.
                @endif
            </p>
            @if(!$busqueda)
            <a href="{{ route('actividad.create') }}"
               class="inline-block mt-4 text-gold-600 hover:text-gold-700 text-sm font-medium underline underline-offset-4">
                Crear la primera actividad
            </a>
            @endif
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-stone-50 border-b border-stone-200">
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-stone-500 uppercase tracking-wider w-12">#</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-stone-500 uppercase tracking-wider">Nombre</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-stone-500 uppercase tracking-wider hidden md:table-cell">Categoría</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-stone-500 uppercase tracking-wider hidden md:table-cell">Responsable</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-stone-500 uppercase tracking-wider hidden lg:table-cell">Fechas</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-stone-500 uppercase tracking-wider">Estado</th>
                    <th class="px-5 py-3.5 text-xs font-semibold text-stone-500 uppercase tracking-wider text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach($actividades as $act)
                <tr class="hover:bg-gold-50 transition-colors">
                    <td class="px-5 py-4 text-stone-400 text-xs">{{ $act->id }}</td>
                    <td class="px-5 py-4">
                        <span class="font-semibold text-stone-800">{{ $act->nombre }}</span>
                    </td>
                    <td class="px-5 py-4 text-stone-500 hidden md:table-cell">
                        <span class="inline-flex items-center bg-gold-50 text-gold-700 border border-gold-200 text-xs font-medium px-2.5 py-1 rounded">
                            {{ $act->categoria->nombre ?? '—' }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-stone-500 hidden md:table-cell">
                        {{ $act->responsable->nombre ?? '—' }}
                    </td>
                    <td class="px-5 py-4 text-stone-500 hidden lg:table-cell text-xs">
                        <div class="space-y-0.5">
                            <p><strong>Inicio:</strong> {{ $act->fecha_inicio->format('d/m/Y') }}</p>
                            @if($act->fecha_fin)
                                <p><strong>Fin:</strong> {{ $act->fecha_fin->format('d/m/Y') }}</p>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        @php
                            $estadoClass = match($act->estado) {
                                'pendiente' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                'en_proceso' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'finalizada' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            };
                            $estadoLabel = match($act->estado) {
                                'pendiente' => 'Pendiente',
                                'en_proceso' => 'En Proceso',
                                'finalizada' => 'Finalizada',
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1 {{ $estadoClass }} border text-xs font-semibold px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full" style="background-color: currentColor;"></span> {{ $estadoLabel }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('actividad.show', $act) }}"
                               class="p-1.5 text-stone-400 hover:text-stone-600 hover:bg-stone-100 rounded-lg transition-colors"
                               title="Ver detalle">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('actividad.edit', $act) }}"
                               class="px-3 py-1.5 text-xs font-medium text-stone-700 bg-stone-100 hover:bg-gold-100 hover:text-gold-700 rounded-lg transition-colors border border-stone-200">
                                Editar
                            </a>
                            <form method="POST"
                                  action="{{ route('actividad.destroy', $act) }}"
                                  onsubmit="return confirm('¿Eliminar la actividad «{{ addslashes($act->nombre) }}»?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="p-1.5 text-stone-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Eliminar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- PAGINACIÓN --}}
        @if($actividades->hasPages())
        <div class="px-5 py-4 border-t border-stone-100 flex items-center justify-between gap-4 flex-wrap">
            <p class="text-xs text-stone-400">
                Mostrando <strong>{{ $actividades->count() }}</strong> de <strong>{{ $actividades->total() }}</strong> actividades
            </p>
            <div class="flex gap-1">
                {{ $actividades->links('pagination::simple-bootstrap-5') }}
            </div>
        </div>
        @endif
    @endif
</div>

@endsection
