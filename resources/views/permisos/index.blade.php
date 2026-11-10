@extends('layouts.app')

@section('title', 'Administrar Permisos')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}" class="text-gold-500 text-decoration-none">Acceso y Seguridad</a></li>
    <li class="breadcrumb-item active text-white" aria-current="page">Permisos</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Administrar Permisos</h1>
            <p class="text-muted mb-0">Selecciona un rol y edita sus permisos aquí mismo.</p>
        </div>
        <a href="{{ route('roles.index') }}" class="btn btn-secondary">
            <i class="bi bi-shield-check me-1"></i> Administrar Roles
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-0 mb-4 bg-danger text-white">
            <h5 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Por favor corrige los siguientes errores:</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
        <div class="card-header border-0 bg-dark py-3 d-flex align-items-center justify-content-between">
            <h5 class="card-title text-white mb-0">
                <i class="bi bi-shield-check text-gold-500 me-2"></i>Roles y sus permisos
            </h5>
            <span class="badge bg-secondary">{{ $roles->count() }} roles</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" class="border-0">Rol</th>
                        <th scope="col" class="border-0">Descripción</th>
                        <th scope="col" class="border-0">Estado</th>
                        <th scope="col" class="border-0">Permisos</th>
                        <th scope="col" class="border-0 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody x-data="{ abierto: {{ request('rol') ? (int) request('rol') : 'null' }} }">
                    @forelse($roles as $rol)
                        <tr>
                            <td class="fw-bold text-white">
                                @php
                                    $badgeClass = 'bg-secondary';
                                    if ($rol->nombre === 'Administrador') $badgeClass = 'bg-danger';
                                    elseif ($rol->nombre === 'Secretario') $badgeClass = 'bg-primary';
                                    elseif ($rol->nombre === 'Tesorero') $badgeClass = 'bg-warning text-dark';
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $rol->nombre }}</span>
                            </td>
                            <td>{{ $rol->descripcion ?? 'Sin descripción' }}</td>
                            <td>
                                @if(strcasecmp((string) $rol->estado, 'activo') === 0)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info text-dark fw-bold">{{ $rol->permisos_count }}</span>
                                <span class="text-muted small">de {{ $totalPermisos }}</span>
                            </td>
                            <td class="text-end">
                                @if(auth()->user()->tienePermiso('permisos.gestionar'))
                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                            @click="abierto = (abierto === {{ $rol->id }} ? null : {{ $rol->id }})">
                                        <i class="bi bi-sliders me-1"></i>
                                        <span x-text="abierto === {{ $rol->id }} ? 'Cerrar' : 'Editar permisos'">Editar permisos</span>
                                    </button>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-eye me-1"></i> Solo lectura
                                    </span>
                                @endif
                            </td>
                        </tr>

                        @if(auth()->user()->tienePermiso('permisos.gestionar'))
                            <tr x-show="abierto === {{ $rol->id }}" x-cloak>
                                <td colspan="5" class="p-0 border-0">
                                    <div class="p-4" style="background: var(--church-surface-2, #151521); border-top: 1px solid var(--church-border, #2b2b40);">
                                        @if($rol->nombre === 'Administrador')
                                            <div class="alert alert-info border-0 mb-3">
                                                <i class="bi bi-info-circle-fill me-2"></i>
                                                El rol <strong>Administrador</strong> tiene acceso total por definición, independientemente de los permisos marcados aquí.
                                            </div>
                                        @endif

                                        <form method="POST" action="{{ route('permisos.roles.update', $rol->id) }}">
                                            @csrf
                                            @method('PUT')

                                            @include('permisos.partials.editor', [
                                                'uid' => $rol->id,
                                                'permisosPorCategoria' => $permisosPorCategoria,
                                                'asignados' => $rol->permisos->pluck('id')->all(),
                                            ])

                                            <div class="d-flex gap-3 mt-4">
                                                <button type="submit" class="btn btn-primary px-4 py-2">
                                                    <i class="bi bi-save-fill me-1"></i> Guardar permisos
                                                </button>
                                                <button type="button" class="btn btn-secondary px-4 py-2"
                                                        @click="abierto = null">
                                                    Cancelar
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No hay roles registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editorDe = (el) => el.closest('.permisos-editor');

        function sincronizarGrupo(editor, indice) {
            const delGrupo = editor.querySelectorAll('.js-permiso-check[data-grupo="' + indice + '"]');
            const toggle = editor.querySelector('.js-permiso-grupo[data-grupo="' + indice + '"]');

            if (! toggle || delGrupo.length === 0) return;

            const marcados = Array.from(delGrupo).filter(c => c.checked).length;
            toggle.checked = marcados === delGrupo.length;
            toggle.indeterminate = marcados > 0 && marcados < delGrupo.length;
        }

        function sincronizarTodo(editor) {
            editor.querySelectorAll('.js-permiso-grupo').forEach(toggle => {
                sincronizarGrupo(editor, toggle.dataset.grupo);
            });
        }

        // Estado inicial de los interruptores por categoría (también en paneles ocultos).
        document.querySelectorAll('.permisos-editor').forEach(sincronizarTodo);

        document.addEventListener('change', function (evento) {
            const check = evento.target;

            if (check.classList.contains('js-permiso-check')) {
                const editor = editorDe(check);
                if (editor) sincronizarGrupo(editor, check.dataset.grupo);
                return;
            }

            if (check.classList.contains('js-permiso-grupo')) {
                const editor = editorDe(check);
                if (! editor) return;

                editor.querySelectorAll('.js-permiso-check[data-grupo="' + check.dataset.grupo + '"]')
                    .forEach(item => { item.checked = check.checked; });
                check.indeterminate = false;
            }
        });

        document.addEventListener('click', function (evento) {
            const todos = evento.target.closest('.js-permisos-all');
            const ninguno = evento.target.closest('.js-permisos-none');

            if (! todos && ! ninguno) return;

            const editor = editorDe(todos || ninguno);
            if (! editor) return;

            const valor = Boolean(todos);
            editor.querySelectorAll('.js-permiso-check').forEach(check => { check.checked = valor; });
            sincronizarTodo(editor);
        });
    });
</script>
@endsection
