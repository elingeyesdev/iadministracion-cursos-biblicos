{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>@yield('title', 'Iglesia') — Sistema de Gestión</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body class="bg-cream min-h-screen bg-cross-pattern">

    {{-- NAVBAR --}}
    <nav class="bg-stone-900 border-b-2 border-gold-500 shadow-lg">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Brand --}}
                <div class="flex items-center gap-3">
                    <span class="text-gold-400 text-xl">✝</span>
                    <div>
                        <a href="/dashboard" class="font-display text-gold-300 text-xl font-semibold tracking-wide leading-none">
                            Mi Iglesia
                        </a>
                        <p class="text-stone-500 text-xs tracking-widest uppercase">Sistema de Gestión</p>
                    </div>
                </div>

                {{-- Nav links --}}
                <div class="hidden md:flex items-center gap-6">
                    <a href="{{ route('categoriaactividad.index') }}"
                       class="text-stone-300 hover:text-gold-300 text-sm font-medium tracking-wide transition-colors">
                        Categorías
                    </a>
                    <a href="/dashboard"
                       class="text-stone-300 hover:text-gold-300 text-sm font-medium tracking-wide transition-colors">
                        Dashboard
                    </a>
                    {{-- Logout --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="text-stone-500 hover:text-stone-300 text-sm transition-colors">
                            Salir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    {{-- BREADCRUMB + ALERTS --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        @if(session('success'))
            <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-3 mb-5 text-sm shadow-sm">
                <span class="text-emerald-500 text-base">✓</span>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 mb-5 text-sm shadow-sm">
                <span class="text-red-500 text-base">✗</span>
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>

    <footer class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-16 pb-8 text-center">
        <span class="text-gold-400 text-base">✝</span>
        <p class="text-stone-400 text-xs mt-1 tracking-widest uppercase">Sistema de Gestión Eclesiástica</p>
    </footer>
</body>
</html>