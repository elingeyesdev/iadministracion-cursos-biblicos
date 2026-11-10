<x-guest-layout>
    <div class="card shadow-sm">
        <div class="card-body p-4 p-md-5">
            <h1 class="sidebar-title mb-1" style="color: var(--church-text); font-size: 2rem;">Área segura</h1>
            <div class="mb-3" style="width: 44px; height: 2px; background: linear-gradient(90deg, var(--church-gold), transparent);"></div>
            <p class="text-muted mb-4">
                Esta sección requiere verificación adicional. Confirma tu contraseña antes de continuar.
            </p>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <div class="mb-4">
                    <label for="password" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Contraseña</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                        class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" />
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-church w-100 py-2">
                    Confirmar acceso
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
