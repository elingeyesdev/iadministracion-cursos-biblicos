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

        <div class="max-w-2xl mx-auto px-8 py-16">

            <div class="flex items-center justify-between mb-2">
                <h1 class="font-serif text-[2rem] font-normal text-[#F0EAD6] leading-tight">Detalle del Miembro</h1>
                <a href="{{ route('miembro.index') }}"
                   class="text-xs uppercase tracking-widest text-[#F0EAD6]/40 hover:text-[#D4AF53] transition-colors">
                    ← Volver
                </a>
            </div>
            <div class="w-10 h-0.5 mb-10" style="background:linear-gradient(90deg,#D4AF53,transparent)"></div>

            <div class="bg-[#16161C] border border-white/[0.08] rounded-lg p-8 space-y-6">

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-[#F0EAD6]/40 mb-1">Nombre</p>
                        <p class="text-sm text-[#F0EAD6]">{{ $miembro->nombre }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-widest text-[#F0EAD6]/40 mb-1">Apellido</p>
                        <p class="text-sm text-[#F0EAD6]">{{ $miembro->apellido }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-widest text-[#F0EAD6]/40 mb-1">Correo</p>
                        <p class="text-sm text-[#F0EAD6]">{{ $miembro->correo }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-widest text-[#F0EAD6]/40 mb-1">Teléfono</p>
                        <p class="text-sm text-[#F0EAD6]">{{ $miembro->telefono ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-widest text-[#F0EAD6]/40 mb-1">Rol</p>
                        <span class="px-2 py-1 rounded text-xs"
                              style="background:rgba(212,175,83,0.15); color:#D4AF53;">
                            {{ ucfirst($miembro->rol) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-widest text-[#F0EAD6]/40 mb-1">Registrado</p>
                        <p class="text-sm text-[#F0EAD6]/60">{{ $miembro->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>

                <div class="flex gap-4 pt-4 border-t border-white/[0.06]">
                    <a href="{{ route('miembro.edit', $miembro) }}"
                       class="px-6 py-2.5 rounded-lg text-sm text-[#0E0E12] font-medium transition-all"
                       style="background:linear-gradient(135deg,#D4AF53 0%,#B8952F 100%)">
                        Editar
                    </a>
                    <form method="POST" action="{{ route('miembro.destroy', $miembro) }}"
                          onsubmit="return confirm('¿Eliminar este miembro?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-6 py-2.5 rounded-lg text-sm text-red-400 border border-red-400/20 hover:border-red-400/50 transition-all">
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>