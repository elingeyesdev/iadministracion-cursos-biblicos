<x-app-layout>
    <div class="min-h-screen bg-[#0E0E12]">

        {{-- Header --}}
        <div class="bg-gray-900 border-b border-gray-700 px-12 py-6 flex items-center justify-between">
            <span class="font-serif text-2xl tracking-wide text-gold-500">
                {{ config('app.name') }}
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs uppercase tracking-widest text-gray-400 hover:text-gold-500 transition-colors">
                    Cerrar sesión
                </button>
            </form>
        </div>

        {{-- Contenido --}}
        <div class="max-w-5xl mx-auto px-8 py-16">

            {{-- Bienvenida --}}
            <h1 class="font-serif text-[2rem] font-normal text-gray-100 leading-tight mb-2">
                Bienvenido, {{ Auth::user()->name }}
            </h1>
            <div class="w-10 h-0.5 mb-6 bg-gradient-to-r from-gold-500 to-transparent"></div>
            <p class="text-sm text-gray-500 mb-12 leading-relaxed">Has iniciado sesión correctamente.</p>

            {{-- Tarjetas --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                {{-- Tarjeta Roles --}}
                @role('administrador')
                <a href="{{ route('roles.index') }}"
                    class="group block bg-gray-800 border border-gray-700 rounded-lg p-8 hover:border-gold-500/40 transition-all">
                    <div class="w-10 h-10 rounded-md flex items-center justify-center mb-5 bg-gradient-to-br from-gold-400 to-gold-500">
                        <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h2 class="font-serif text-lg text-gray-100 mb-1 group-hover:text-gold-500 transition-colors">Gestión de Roles</h2>
                    <p class="text-xs text-gray-400">Administra los roles del sistema.</p>
                </a>
                @endrole

                {{-- Tarjeta Perfil --}}
                <a href="{{ route('profile.edit') }}"
                    class="group block bg-gray-800 border border-gray-700 rounded-lg p-8 hover:border-gold-500/40 transition-all">
                    <div class="w-10 h-10 rounded-md flex items-center justify-center mb-5 bg-gradient-to-br from-gold-400 to-gold-500">
                        <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h2 class="font-serif text-lg text-gray-100 mb-1 group-hover:text-gold-500 transition-colors">Mi Perfil</h2>
                    <p class="text-xs text-gray-400">Edita tu información personal.</p>
                </a>

                {{-- Tarjeta Categoría Actividad --}}
                <a href="{{ route('categoriaactividad.index') }}"
                    class="group block bg-gray-800 border border-gray-700 rounded-lg p-8 hover:border-gold-500/40 transition-all">
                    <div class="w-10 h-10 rounded-md flex items-center justify-center mb-5 bg-gradient-to-br from-gold-400 to-gold-500">
                        <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h2 class="font-serif text-lg text-gray-100 mb-1 group-hover:text-gold-500 transition-colors">Categorías de Actividad</h2>
                    <p class="text-xs text-gray-400">Administra las categorías de actividades.</p>
                </a>

                {{-- Tarjeta Actividades --}}
                <a href="{{ route('actividad.index') }}"
                    class="group block bg-gray-800 border border-gray-700 rounded-lg p-8 hover:border-gold-500/40 transition-all">
                    <div class="w-10 h-10 rounded-md flex items-center justify-center mb-5 bg-gradient-to-br from-gold-400 to-gold-500">
                        <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="font-serif text-lg text-gray-100 mb-1 group-hover:text-gold-500 transition-colors">Actividades</h2>
                    <p class="text-xs text-gray-400">Gestiona todas las actividades.</p>
                </a>

                {{-- Tarjeta Miembros --}}
                <a href="{{ route('miembro.index') }}"
                    class="group block bg-gray-800 border border-gray-700 rounded-lg p-8 hover:border-gold-500/40 transition-all">
                    <div class="w-10 h-10 rounded-md flex items-center justify-center mb-5 bg-gradient-to-br from-gold-400 to-gold-500">
                        <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h2 class="font-serif text-lg text-gray-100 mb-1 group-hover:text-gold-500 transition-colors">Miembros</h2>
                    <p class="text-xs text-gray-400">Gestiona los miembros del sistema.</p>
                </a>

            </div>
        </div>
    </div>
</x-app-layout>