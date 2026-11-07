<x-guest-layout>
    <div class="card shadow-sm">
        <div class="card-body p-4 p-md-5">
            <h1 class="sidebar-title mb-1" style="color: var(--church-text); font-size: 2rem;">Verifica tu correo</h1>
            <div class="mb-3" style="width: 44px; height: 2px; background: linear-gradient(90deg, var(--church-gold), transparent);"></div>
            <p class="text-muted mb-4">
                Hemos enviado un enlace de verificaci├│n a <strong>{{ auth()->user()->email }}</strong>. Revisa tu bandeja de entrada y haz clic en el enlace para activar tu cuenta.
            </p>

            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success" role="alert">
                    Se ha enviado un nuevo enlace de verificaci├│n a tu correo.
                </div>
            @endif

            @if (session('status') == 'verification-mail-failed')
                <div class="alert alert-warning" role="alert">
                    No se pudo enviar el correo de verificaci├│n en este momento. Int├®ntalo de nuevo m├ís tarde.
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-church w-100 py-2">
                    Reenviar correo
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="text-center mt-3 mb-0">
                @csrf
                <input type="hidden" name="redirect" value="login">
                <button type="submit" class="btn btn-link p-0 text-decoration-none small" style="color: var(--church-gold);">
                    <i class="bi bi-arrow-left me-1"></i> Volver a inicio
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
