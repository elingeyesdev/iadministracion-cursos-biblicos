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
                <h1 class="font-serif text-[2rem] font-normal text-[#F0EAD6] leading-tight">Agregar Miembro</h1>
                <a href="{{ route('miembro.index') }}"
                   class="text-xs uppercase tracking-widest text-[#F0EAD6]/40 hover:text-[#D4AF53] transition-colors">
                    ← Volver
                </a>
            </div>
            <div class="w-10 h-0.5 mb-10" style="background:linear-gradient(90deg,#D4AF53,transparent)"></div>

            <form method="POST" action="{{ route('miembro.store') }}"
                  class="bg-[#16161C] border border-white/[0.08] rounded-lg p-8 space-y-6">
                @csrf

                {{-- Nombre --}}
                <div>
                    <label class="block text-xs uppercase tracking-widest text-[#F0EAD6]/40 mb-2">Nombre</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}"
                           class="w-full bg-[#0E0E12] border border-white/[0.08] rounded-lg px-4 py-2.5 text-sm text-[#F0EAD6] focus:outline-none focus:border-[#D4AF53]/50 transition-colors @error('nombre') border-red-500/50 @enderror"/>
                    @error('nombre')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Apellido --}}
                <div>
                    <label class="block text-xs uppercase tracking-widest text-[#F0EAD6]/40 mb-2">Apellido</label>
                    <input type="text" name="apellido" value="{{ old('apellido') }}"
                           class="w-full bg-[#0E0E12] border border-white/[0.08] rounded-lg px-4 py-2.5 text-sm text-[#F0EAD6] focus:outline-none focus:border-[#D4AF53]/50 transition-colors @error('apellido') border-red-500/50 @enderror"/>
                    @error('apellido')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Correo --}}
                <div>
                    <label class="block text-xs uppercase tracking-widest text-[#F0EAD6]/40 mb-2">Correo</label>
                    <input type="email" name="correo" value="{{ old('correo') }}"
                           class="w-full bg-[#0E0E12] border border-white/[0.08] rounded-lg px-4 py-2.5 text-sm text-[#F0EAD6] focus:outline-none focus:border-[#D4AF53]/50 transition-colors @error('correo') border-red-500/50 @enderror"/>
                    @error('correo')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Teléfono --}}
                <div>
                    <label class="block text-xs uppercase tracking-widest text-[#F0EAD6]/40 mb-2">Teléfono <span class="text-[#F0EAD6]/20">(opcional)</span></label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                           class="w-full bg-[#0E0E12] border border-white/[0.08] rounded-lg px-4 py-2.5 text-sm text-[#F0EAD6] focus:outline-none focus:border-[#D4AF53]/50 transition-colors"/>
                </div>

                {{-- Rol --}}
                <div>
                    <label class="block text-xs uppercase tracking-widest text-[#F0EAD6]/40 mb-2">Rol</label>
                    <select name="rol"
                            class="w-full bg-[#0E0E12] border border-white/[0.08] rounded-lg px-4 py-2.5 text-sm text-[#F0EAD6] focus:outline-none focus:border-[#D4AF53]/50 transition-colors @error('rol') border-red-500/50 @enderror">
                        <option value="">Seleccionar...</option>
                        @foreach(['administrador','miembro','supervisor'] as $rol)
                            <option value="{{ $rol }}" {{ old('rol') === $rol ? 'selected' : '' }}>
                                {{ ucfirst($rol) }}
                            </option>
                        @endforeach
                    </select>
                    @error('rol')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Botones --}}
                <div class="flex gap-4 pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 rounded-lg text-sm text-black font-medium transition-all bg-gradient-to-br from-gold-400 to-gold-500 hover:from-gold-500 hover:to-gold-600">
                        Guardar
                    </button>
                    <a href="{{ route('miembro.index') }}"
                       class="px-6 py-2.5 rounded-lg text-sm text-gray-400 border border-gray-600 hover:border-gold-500 hover:text-gold-500 transition-all">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>