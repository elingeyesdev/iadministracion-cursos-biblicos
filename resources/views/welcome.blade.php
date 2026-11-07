<x-guest-layout>
    <div class="card shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div class="text-muted small mb-2" style="letter-spacing:.18em; text-transform:uppercase;">Inicio</div>
            <h1 class="sidebar-title mb-1" style="color: var(--church-text); font-size: 2rem;">{{ config('app.name', 'Sistema') }}</h1>
            <div class="mb-3" style="width: 44px; height: 2px; background: linear-gradient(90deg, var(--church-gold), transparent);"></div>
            <p class="text-muted mb-4">
                Herramienta para organizar miembros, actividades y seguimiento pastoral con el mismo cuidado que el resto del ministerio.
            </p>

            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-church w-100 py-2">
                    Ir al panel
                </a>
            @else
                <div class="d-grid gap-2">
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="btn btn-church py-2">
                            Iniciar sesi├│n
                        </a>
                    @endif
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-outline-church py-2">
                            Crear cuenta
                        </a>
                    @endif
                </div>
            @endauth

            <p class="text-center text-muted small mt-4 mb-0">
                Ô£Ø Sistema de gesti├│n eclesi├ística
            </p>
        </div>
    </div>
</x-guest-layout>
