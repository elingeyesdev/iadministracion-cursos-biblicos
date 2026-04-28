<x-guest-layout>
    <div class="card shadow-sm">
        <div class="card-body p-4 p-md-5">
            <h1 class="sidebar-title mb-1" style="color: var(--church-text); font-size: 2rem;">Verifica tu correo</h1>
            <div class="mb-3" style="width: 44px; height: 2px; background: linear-gradient(90deg, var(--church-gold), transparent);"></div>
            <p class="text-muted mb-4">
                Hemos enviado un enlace de verificación a tu correo. Revisa tu bandeja de entrada y haz clic en el enlace para activar tu cuenta.
            </p>

            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success" role="alert">
                    Se ha enviado un nuevo enlace de verificación a tu correo.
                </div>
            @endif

            <div class="d-flex gap-2">
                <form method="POST" action="{{ route('verification.send') }}" class="flex-grow-1">
                    @csrf
                    <button type="submit" class="btn btn-church w-100 py-2">
                        Reenviar correo
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-church py-2">
                        Salir
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>