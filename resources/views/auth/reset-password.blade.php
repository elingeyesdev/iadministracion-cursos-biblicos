<x-guest-layout>
    <div class="card shadow-sm">
        <div class="card-body p-4 p-md-5">
            <h1 class="sidebar-title mb-1" style="color: var(--church-text); font-size: 2rem;">Nueva contrase├▒a</h1>
            <div class="mb-3" style="width: 44px; height: 2px; background: linear-gradient(90deg, var(--church-gold), transparent);"></div>
            <p class="text-muted mb-4">Elige una contrase├▒a segura para proteger tu cuenta.</p>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="mb-3">
                    <label for="email" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Correo electr├│nico</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                        class="form-control @error('email') is-invalid @enderror" placeholder="tu@correo.com" />
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Nueva contrase├▒a</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                        class="form-control @error('password') is-invalid @enderror" placeholder="M├¡nimo 8 caracteres" />
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Confirmar contrase├▒a</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                        class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="Repite tu nueva contrase├▒a" />
                    @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-church w-100 py-2">
                    Restablecer contrase├▒a
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
