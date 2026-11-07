<section>
    <header>
        <h2 class="sidebar-title mb-1">Eliminar cuenta</h2>
        <p class="text-muted mb-0">
            Esta acci├│n es permanente. Para confirmar, ingresa tu contrase├▒a y elimina tu cuenta.
        </p>
    </header>

    <form method="post" action="{{ route('profile.destroy') }}" class="mt-4">
        @csrf
        @method('delete')

        <div class="mb-3">
            <label for="delete_password" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Contrase├▒a</label>
            <input id="delete_password" name="password" type="password"
                   class="form-control @if($errors->userDeletion->has('password')) is-invalid @endif"
                   placeholder="ÔÇóÔÇóÔÇóÔÇóÔÇóÔÇóÔÇóÔÇó">
            @if($errors->userDeletion->has('password'))
                <div class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</div>
            @endif
        </div>

        <button type="submit" class="btn btn-danger">
            Eliminar cuenta
        </button>
    </form>
</section>
