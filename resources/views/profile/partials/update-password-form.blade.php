<section>
    <header>
        <h2 class="sidebar-title mb-1">Cambiar contrase├▒a</h2>
        <p class="text-muted mb-0">Usa una contrase├▒a larga y segura.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-4">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Contrase├▒a actual</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                   class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif" />
            @if($errors->updatePassword->has('current_password'))
                <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
            @endif
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Nueva contrase├▒a</label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                   class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif" />
            @if($errors->updatePassword->has('password'))
                <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
            @endif
        </div>

        <div class="mb-4">
            <label for="update_password_password_confirmation" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Confirmar contrase├▒a</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                   class="form-control @if($errors->updatePassword->has('password_confirmation')) is-invalid @endif" />
            @if($errors->updatePassword->has('password_confirmation'))
                <div class="invalid-feedback">{{ $errors->updatePassword->first('password_confirmation') }}</div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-church">Guardar</button>
            @if (session('status') === 'password-updated')
                <span class="text-muted small">Guardado.</span>
            @endif
        </div>
    </form>
</section>
