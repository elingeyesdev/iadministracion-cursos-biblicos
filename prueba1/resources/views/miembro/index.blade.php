<x-app-layout>
    <div class="min-h-screen bg-[#0E0E12]">

        {{-- Header --}}
        <div class="bg-[#16161C] border-b border-white/[0.06] px-12 py-6 flex items-center justify-between">
            <span class="font-serif text-2xl tracking-wide text-[#D4AF53]">
                {{ config('app.name') }}
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs uppercase tracking-widest text-[#F0EAD6]/40 hover:text-[#D4AF53] transition-colors">
                    Cerrar sesión
                </button>
            </form>
        </div>

        <div class="max-w-5xl mx-auto px-8 py-16">

            {{-- Título --}}
            <div class="flex items-center justify-between mb-2">
                <h1 class="font-serif text-[2rem] font-normal text-[#F0EAD6] leading-tight">Miembros</h1>
                <a href="{{ route('dashboard') }}"
                   class="text-xs uppercase tracking-widest text-[#F0EAD6]/40 hover:text-[#D4AF53] transition-colors">
                    ← Volver
                </a>
            </div>
            <div class="w-10 h-0.5 mb-6" style="background:linear-gradient(90deg,#D4AF53,transparent)"></div>

            {{-- Alerta --}}
            @if(session('success'))
                <div class="mb-6 px-5 py-3 rounded-lg border border-[#D4AF53]/30 bg-[#D4AF53]/10 text-[#D4AF53] text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Búsqueda y botón agregar --}}
            <div class="flex flex-col sm:flex-row gap-4 mb-8">
                <form method="GET" action="{{ route('miembro.index') }}" class="flex-1 flex gap-3">
                    <input
                        type="text"
                        name="buscar"
                        value="{{ $buscar }}"
                        placeholder="Buscar por nombre, apellido o correo..."
                        class="flex-1 bg-[#16161C] border border-white/[0.08] rounded-lg px-4 py-2.5 text-sm text-[#F0EAD6] placeholder-[#F0EAD6]/30 focus:outline-none focus:border-[#D4AF53]/50 transition-colors"
                    />
                    <button type="submit"
                        class="px-5 py-2.5 rounded-lg text-sm text-black font-medium transition-all bg-gradient-to-br from-gold-400 to-gold-500 hover:from-gold-500 hover:to-gold-600">
                        Buscar
                    </button>
                </form>
                <a href="{{ route('miembro.create') }}"
                   class="px-6 py-3 rounded-lg text-base text-black font-medium transition-all bg-gradient-to-br from-gold-400 to-gold-500 hover:from-gold-500 hover:to-gold-600 border border-gold-600 flex items-center gap-2 shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Agregar Miembro
                </a>
            </div>

            {{-- Tabla --}}
            <div class="bg-[#16161C] border border-white/[0.08] rounded-lg overflow-hidden">
                <table class="w-full text-sm text-[#F0EAD6]">
                    <thead>
                        <tr class="border-b border-white/[0.06]">
                            <th class="text-left px-6 py-4 text-xs uppercase tracking-widest text-[#F0EAD6]/40 font-normal">Nombre</th>
                            <th class="text-left px-6 py-4 text-xs uppercase tracking-widest text-[#F0EAD6]/40 font-normal">Correo</th>
                            <th class="text-left px-6 py-4 text-xs uppercase tracking-widest text-[#F0EAD6]/40 font-normal">Teléfono</th>
                            <th class="text-left px-6 py-4 text-xs uppercase tracking-widest text-[#F0EAD6]/40 font-normal">Rol</th>
                            <th class="text-left px-6 py-4 text-xs uppercase tracking-widest text-[#F0EAD6]/40 font-normal">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($miembros as $miembro)
                            <tr class="border-b border-white/[0.04] hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-4">
                                    {{ $miembro->nombre }} {{ $miembro->apellido }}
                                </td>
                                <td class="px-6 py-4 text-[#F0EAD6]/60">{{ $miembro->correo }}</td>
                                <td class="px-6 py-4 text-[#F0EAD6]/60">{{ $miembro->telefono ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs"
                                          style="background:rgba(212,175,83,0.15); color:#D4AF53;">
                                        {{ ucfirst($miembro->rol) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('miembro.show', $miembro) }}"
                                           class="text-xs text-gray-400 hover:text-gold-500 transition-colors">Ver</a>
                                        <a href="{{ route('miembro.edit', $miembro) }}"
                                           class="text-xs text-gray-400 hover:text-gold-500 transition-colors">Editar</a>
                                        <form method="POST" action="{{ route('miembro.destroy', $miembro) }}"
                                              onsubmit="return confirm('¿Eliminar este miembro?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-xs text-gray-400 hover:text-red-400 transition-colors">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-[#F0EAD6]/30 text-sm">
                                    No se encontraron miembros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Paginación --}}
                @if($miembros->hasPages())
                    <div class="px-6 py-4 border-t border-white/[0.06]">
                        {{ $miembros->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>