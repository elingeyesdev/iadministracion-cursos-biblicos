<section>
    <header>
        <h2 class="sidebar-title mb-1">Información del perfil</h2>
        <p class="text-muted mb-0">Actualiza tu nombre y correo electrónico.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Nombre</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Correo</label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $user->email) }}" required autocomplete="username" />
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <div class="text-muted small">
                        Tu correo aún no está verificado.
                        <button form="send-verification" class="btn btn-link p-0 align-baseline text-decoration-none" style="color: var(--church-gold);">
                            Reenviar verificación
                        </button>
                    </div>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success mt-3 mb-0" role="alert">
                            Se ha enviado un nuevo enlace de verificación a tu correo.
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-church">
                Guardar
            </button>
            @if (session('status') === 'profile-updated')
                <span class="text-muted small">Guardado.</span>
            @endif
        </div>
    </form>
</section>
