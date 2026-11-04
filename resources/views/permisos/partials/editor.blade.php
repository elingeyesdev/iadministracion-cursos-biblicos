@php
    $uid = $uid ?? 'x';
    $asignados = collect($asignados ?? [])->map(fn ($id) => (int) $id)->all();
    $seleccionados = collect(old('permisos', $asignados))->map(fn ($id) => (int) $id)->all();
    $indiceGrupo = 0;
@endphp

<div class="permisos-editor" data-editor="{{ $uid }}">
    <div class="d-flex flex-wrap gap-2 mb-3">
        <button type="button" class="btn btn-sm btn-outline-secondary js-permisos-all">
            <i class="bi bi-check2-square me-1"></i> Seleccionar todos
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary js-permisos-none">
            <i class="bi bi-x-square me-1"></i> Quitar todos
        </button>
        @error('permisos') <div class="text-danger small align-self-center">{{ $message }}</div> @enderror
        @error('permisos.*') <div class="text-danger small align-self-center">{{ $message }}</div> @enderror
    </div>

    @forelse($permisosPorCategoria as $claveCategoria => $grupo)
        <div class="permiso-categoria mb-4 p-3 rounded" style="border: 1px solid var(--church-border, #2b2b40); background: rgba(255,255,255,.02);">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-2"
                 style="border-bottom: 1px solid var(--church-border, #2b2b40);">
                <h6 class="text-white mb-0">
                    <i class="bi {{ $grupo['icono'] }} me-2 text-warning"></i>
                    {{ $grupo['nombre'] }}
                    <span class="badge bg-dark text-muted ms-1">{{ $grupo['permisos']->count() }}</span>
                </h6>
                <div class="form-check form-switch m-0">
                    <input class="form-check-input js-permiso-grupo" type="checkbox"
                           id="grupo-{{ $uid }}-{{ $indiceGrupo }}" data-grupo="{{ $indiceGrupo }}">
                    <label class="form-check-label text-muted small" for="grupo-{{ $uid }}-{{ $indiceGrupo }}">Todo el grupo</label>
                </div>
            </div>

            <div class="row g-2">
                @forelse($grupo['permisos'] as $permiso)
                    <div class="col-12 col-md-6">
                        <div class="form-check p-2 rounded" style="background: rgba(0,0,0,.12);">
                            <input class="form-check-input js-permiso-check"
                                   type="checkbox" name="permisos[]" value="{{ $permiso->id }}"
                                   data-grupo="{{ $indiceGrupo }}"
                                   id="permiso-{{ $uid }}-{{ $permiso->id }}"
                                   @checked(in_array($permiso->id, $seleccionados, true))>
                            <label class="form-check-label text-white" for="permiso-{{ $uid }}-{{ $permiso->id }}">
                                {{ $permiso->descripcion ?: $permiso->nombre }}
                                <span class="badge bg-dark text-muted ms-1" style="font-size: .65rem;">{{ $permiso->modulo }}</span>
                                <code class="d-block text-muted" style="font-size: .72rem;">{{ $permiso->nombre }}</code>
                            </label>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-muted small mb-0">Esta categoría no tiene permisos registrados.</p>
                    </div>
                @endforelse
            </div>
        </div>
        @php $indiceGrupo++; @endphp
    @empty
        <p class="text-muted mb-0">
            No hay permisos registrados. Ejecuta <code>php artisan db:seed --class=PermisoSeeder</code>.
        </p>
    @endforelse
</div>
