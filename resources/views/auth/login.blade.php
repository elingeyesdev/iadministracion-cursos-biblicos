<x-guest-layout>
    <div class="card shadow-sm">
        <div class="card-body p-4 p-md-5">
            <h1 class="sidebar-title mb-1" style="color: var(--church-text); font-size: 2rem;">Iniciar sesión</h1>
            <div class="mb-3" style="width: 44px; height: 2px; background: linear-gradient(90deg, var(--church-gold), transparent);"></div>
            <p class="text-muted mb-4">Accede con tu correo y contraseña para continuar.</p>

            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Correo electrónico</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                        class="form-control @error('email') is-invalid @enderror" placeholder="tu@correo.com" />
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Contraseña</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                        class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" />
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                        <label class="form-check-label text-muted" for="remember_me">Recordarme</label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-decoration-none" style="color: var(--church-gold);">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn btn-church w-100 py-2">
                    Iniciar sesión
                </button>
            </form>

            @if (Route::has('register'))
                <p class="text-center mt-3 mb-0 text-muted small">
                    ¿No tienes cuenta? <a href="{{ route('register') }}" class="text-decoration-none" style="color: var(--church-gold);">Regístrate</a>
                </p>
            @endif
        </div>
    </div>
</x-guest-layout>
