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

        {{-- Contenido --}}
        <div class="max-w-5xl mx-auto px-8 py-16">

            {{-- Bienvenida --}}
            <h1 class="font-serif text-[2rem] font-normal text-[#F0EAD6] leading-tight mb-2">
                Bienvenido, {{ Auth::user()->name }}
            </h1>
            <div class="w-10 h-0.5 mb-6" style="background:linear-gradient(90deg,#D4AF53,transparent)"></div>
            <p class="text-sm text-[#F0EAD6]/45 mb-12 leading-relaxed">Has iniciado sesión correctamente.</p>

            {{-- Tarjetas --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                {{-- Tarjeta Roles --}}
                @role('administrador')
                <a href="{{ route('roles.index') }}"
                    class="group block bg-[#16161C] border border-white/[0.08] rounded-lg p-8 hover:border-[#D4AF53]/40 transition-all">
                    <div class="w-10 h-10 rounded-md flex items-center justify-center mb-5"
                         style="background:linear-gradient(135deg,#D4AF53 0%,#B8952F 100%)">
                        <svg class="w-5 h-5 text-[#0E0E12]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h2 class="font-serif text-lg text-[#F0EAD6] mb-1 group-hover:text-[#D4AF53] transition-colors">Gestión de Roles</h2>
                    <p class="text-xs text-[#F0EAD6]/40">Administra los roles del sistema.</p>
                </a>
                @endrole

                {{-- Tarjeta Perfil --}}
                <a href="{{ route('profile.edit') }}"
                    class="group block bg-[#16161C] border border-white/[0.08] rounded-lg p-8 hover:border-[#D4AF53]/40 transition-all">
                    <div class="w-10 h-10 rounded-md flex items-center justify-center mb-5"
                         style="background:linear-gradient(135deg,#D4AF53 0%,#B8952F 100%)">
                        <svg class="w-5 h-5 text-[#0E0E12]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h2 class="font-serif text-lg text-[#F0EAD6] mb-1 group-hover:text-[#D4AF53] transition-colors">Mi Perfil</h2>
                    <p class="text-xs text-[#F0EAD6]/40">Edita tu información personal.</p>
                </a>

            </div>
        </div>
    </div>
</x-app-layout>