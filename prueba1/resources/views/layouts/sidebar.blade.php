<div class="d-flex flex-column h-100 sidebar p-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <span class="fs-5" style="color: var(--church-gold);">✝</span>
            <div>
                <div class="sidebar-title fs-5 lh-1" style="color: var(--church-text);">
                    {{ config('app.name') }}
                </div>
                <div class="text-muted small" style="letter-spacing:.18em; text-transform:uppercase;">Sistema</div>
            </div>
        </div>
        <button class="btn btn-sm btn-outline-church d-lg-none" type="button" data-bs-dismiss="offcanvas" aria-label="Close">
            Cerrar
        </button>
    </div>

    <hr class="border opacity-25">

    <ul class="nav nav-pills flex-column gap-1">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                Dashboard
            </a>
        </li>

        <li class="nav-item mt-2 text-muted small" style="letter-spacing:.18em; text-transform:uppercase;">Gestión</li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('miembro.*') ? 'active' : '' }}" href="{{ route('miembro.index') }}">
                Miembros
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('actividad.*') ? 'active' : '' }}" href="{{ route('actividad.index') }}">
                Actividades
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('categoriaactividad.*') ? 'active' : '' }}" href="{{ route('categoriaactividad.index') }}">
                Categorías
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('asignacionactividad.*') ? 'active' : '' }}" href="{{ route('asignacionactividad.index') }}">
                Asignación de actividades
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('seguimiento.*') ? 'active' : '' }}" href="{{ route('seguimiento.index') }}">
                Seguimiento
            </a>
        </li>

        @if(auth()->user()?->role === 'administrador')
        <li class="nav-item mt-3 text-muted small" style="letter-spacing:.18em; text-transform:uppercase;">Seguridad</li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                Roles
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                Usuarios
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('asignar-roles.*') ? 'active' : '' }}" href="{{ route('asignar-roles.index') }}">
                Asignar roles
            </a>
        </li>
        @endif
    </ul>

    <div class="mt-auto pt-3">
        <div class="text-muted small mb-2">
            {{ auth()->user()->name ?? '' }}
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-church w-100">
                Salir
            </button>
        </form>
    </div>
</div>

