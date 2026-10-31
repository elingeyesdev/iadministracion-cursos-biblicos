@php
    $uid = $uid ?? 'x';
    $asignados = collect($asignados ?? [])->map(fn ($id) => (int) $id)->all();
    $seleccionados = collect(old('permisos', $asignados))->map(fn ($id) => (int) $id)->all();
    $indiceModulo = 0;
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

    @forelse($permisosPorModulo as $modulo => $permisos)
        <div class="permiso-modulo mb-3 p-3 rounded" style="border: 1px solid var(--church-border, #2b2b40); background: rgba(255,255,255,.02);">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <h6 class="text-white mb-0">
                    <i class="bi {{ config('permisos.modulos.'.$modulo.'.icono', 'bi-key') }} me-2 text-warning"></i>
                    {{ $modulo }}
                    <span class="badge bg-dark text-muted ms-1">{{ $permisos->count() }}</span>
                </h6>
                <div class="form-check form-switch m-0">
                    <input class="form-check-input js-permiso-modulo" type="checkbox"
                           id="modulo-{{ $uid }}-{{ $indiceModulo }}" data-modulo="{{ $indiceModulo }}">
                    <label class="form-check-label text-muted small" for="modulo-{{ $uid }}-{{ $indiceModulo }}">Todo el módulo</label>
                </div>
            </div>

            <div class="row g-2">
                @foreach($permisos as $permiso)
                    <div class="col-12 col-md-6">
                        <div class="form-check">
                            <input class="form-check-input js-permiso-check"
                                   type="checkbox" name="permisos[]" value="{{ $permiso->id }}"
                                   data-modulo="{{ $indiceModulo }}"
                                   id="permiso-{{ $uid }}-{{ $permiso->id }}"
                                   @checked(in_array($permiso->id, $seleccionados, true))>
                            <label class="form-check-label text-white" for="permiso-{{ $uid }}-{{ $permiso->id }}">
                                {{ $permiso->descripcion ?: $permiso->nombre }}
                                <code class="d-block text-muted" style="font-size: .72rem;">{{ $permiso->nombre }}</code>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @php $indiceModulo++; @endphp
    @empty
        <p class="text-muted mb-0">
            No hay permisos registrados. Ejecuta <code>php artisan db:seed --class=PermisoSeeder</code>.
        </p>
    @endforelse
</div>
