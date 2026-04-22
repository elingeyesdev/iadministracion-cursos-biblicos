{{-- resources/views/categoriaactividad/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Categorías de Actividad')

@section('content')

{{-- PAGE HEADER --}}
<div class="mb-8">
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <p class="text-gold-600 text-xs tracking-widest uppercase font-medium mb-1">Gestión</p>
            <h1 class="font-display text-4xl font-semibold text-stone-800 leading-tight">
                Categorías de <em class="text-gold-600 not-italic">Actividad</em>
            </h1>
            <p class="text-stone-500 text-sm mt-1">Organiza las actividades de la comunidad por categoría</p>
        </div>
        <a href="{{ route('categoriaactividad.create') }}"
           class="inline-flex items-center gap-2 bg-gold-500 hover:bg-gold-600 text-white font-semibold text-sm px-5 py-2.5 rounded-lg shadow transition-colors whitespace-nowrap">
            <span class="text-base leading-none">+</span> Nueva Categoría
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
<form method="GET" action="{{ route('categoriaactividad.index') }}" class="mb-6">
    <div class="flex gap-2 max-w-md">
        <div class="relative flex-1">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400 text-sm">🔍</span>
            <input
                type="text"
                name="busqueda"
                value="{{ $busqueda ?? '' }}"
                placeholder="Buscar categoría…"
                class="w-full pl-9 pr-4 py-2.5 bg-white border border-stone-200 rounded-lg text-sm text-stone-700 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-gold-300 focus:border-gold-400 shadow-sm transition"
            >
        </div>
        <button type="submit"
            class="px-4 py-2.5 bg-stone-800 hover:bg-stone-900 text-white text-sm font-medium rounded-lg shadow transition-colors">
            Buscar
        </button>
        @if($busqueda)
            <a href="{{ route('categoriaactividad.index') }}"
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
    @if($categorias->isEmpty())
        <div class="py-16 text-center">
            <span class="text-gold-300 text-4xl block mb-3">✝</span>
            <p class="text-stone-500 text-sm">
                @if($busqueda)
                    No se encontraron categorías para <strong>"{{ $busqueda }}"</strong>
                @else
                    Aún no hay categorías registradas.
                @endif
            </p>
            @if(!$busqueda)
            <a href="{{ route('categoriaactividad.create') }}"
               class="inline-block mt-4 text-gold-600 hover:text-gold-700 text-sm font-medium underline underline-offset-4">
                Crear la primera categoría
            </a>
            @endif
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-stone-50 border-b border-stone-200">
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-stone-500 uppercase tracking-wider w-12">#</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-stone-500 uppercase tracking-wider">Nombre</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-stone-500 uppercase tracking-wider hidden md:table-cell">Descripción</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-stone-500 uppercase tracking-wider">Estado</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-stone-500 uppercase tracking-wider hidden lg:table-cell">Creado</th>
                    <th class="px-5 py-3.5 text-xs font-semibold text-stone-500 uppercase tracking-wider text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach($categorias as $cat)
                <tr class="hover:bg-gold-50 transition-colors">
                    <td class="px-5 py-4 text-stone-400 text-xs">{{ $cat->id }}</td>
                    <td class="px-5 py-4">
                        <span class="font-semibold text-stone-800">{{ $cat->nombre }}</span>
                    </td>
                    <td class="px-5 py-4 text-stone-500 hidden md:table-cell max-w-xs">
                        {{ $cat->descripcion ? Str::limit($cat->descripcion, 65) : '—' }}
                    </td>
                    <td class="px-5 py-4">
                        @if($cat->estado)
                            <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full"></span> Activa
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 bg-stone-100 text-stone-500 border border-stone-200 text-xs font-semibold px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 bg-stone-400 rounded-full"></span> Inactiva
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-stone-400 text-xs hidden lg:table-cell">
                        {{ $cat->created_at->format('d/m/Y') }}
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('categoriaactividad.show', $cat) }}"
                               class="p-1.5 text-stone-400 hover:text-stone-600 hover:bg-stone-100 rounded-lg transition-colors"
                               title="Ver detalle">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('categoriaactividad.edit', $cat) }}"
                               class="px-3 py-1.5 text-xs font-medium text-stone-700 bg-stone-100 hover:bg-gold-100 hover:text-gold-700 rounded-lg transition-colors border border-stone-200">
                                Editar
                            </a>
                            <form method="POST"
                                  action="{{ route('categoriaactividad.destroy', $cat) }}"
                                  onsubmit="return confirm('¿Eliminar la categoría «{{ addslashes($cat->nombre) }}»?')">
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
        @if($categorias->hasPages())
        <div class="px-5 py-4 border-t border-stone-100 flex items-center justify-between gap-4 flex-wrap">
            <p class="text-xs text-stone-400">
                Mostrando {{ $categorias->firstItem() }}–{{ $categorias->lastItem() }} de {{ $categorias->total() }} categorías
            </p>
            <div class="flex items-center gap-1">
                @if($categorias->onFirstPage())
                    <span class="px-3 py-1.5 text-xs text-stone-300 bg-stone-50 rounded-lg border border-stone-100 cursor-not-allowed">‹</span>
                @else
                    <a href="{{ $categorias->previousPageUrl() }}"
                       class="px-3 py-1.5 text-xs text-stone-600 bg-stone-50 hover:bg-gold-50 hover:text-gold-700 rounded-lg border border-stone-200 transition-colors">‹</a>
                @endif

                @foreach($categorias->getUrlRange(1, $categorias->lastPage()) as $page => $url)
                    @if($page == $categorias->currentPage())
                        <span class="px-3 py-1.5 text-xs font-bold text-white bg-gold-500 rounded-lg">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                           class="px-3 py-1.5 text-xs text-stone-600 bg-stone-50 hover:bg-gold-50 hover:text-gold-700 rounded-lg border border-stone-200 transition-colors">{{ $page }}</a>
                    @endif
                @endforeach

                @if($categorias->hasMorePages())
                    <a href="{{ $categorias->nextPageUrl() }}"
                       class="px-3 py-1.5 text-xs text-stone-600 bg-stone-50 hover:bg-gold-50 hover:text-gold-700 rounded-lg border border-stone-200 transition-colors">›</a>
                @else
                    <span class="px-3 py-1.5 text-xs text-stone-300 bg-stone-50 rounded-lg border border-stone-100 cursor-not-allowed">›</span>
                @endif
            </div>
        </div>
        @endif
    @endif
</div>
@endsection