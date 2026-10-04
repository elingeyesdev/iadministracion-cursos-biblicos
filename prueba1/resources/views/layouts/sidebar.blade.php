<div class="d-flex flex-column h-100 sidebar p-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <span class="fs-4 text-warning">✝</span>
            <div>
                <div class="sidebar-title fs-5 fw-bold lh-1 text-white">
                    Sistema Iglesia
                </div>
                <div class="text-muted small" style="letter-spacing:.1em; text-transform:uppercase; font-size: 0.75rem;">Administración</div>
            </div>
        </div>
        <button class="btn btn-sm btn-outline-light d-lg-none" type="button" data-bs-dismiss="offcanvas" aria-label="Close">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <hr class="border opacity-25 text-white">

    <div class="flex-grow-1 overflow-auto">
        <ul class="nav nav-pills flex-column gap-1">
            <!-- PRINCIPAL -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2 me-2 text-warning"></i> Principal
                </a>
            </li>

            <!-- ACCESO Y SEGURIDAD COLLAPSIBLE MENU -->
            @if(auth()->user()->esAdmin())
            <li class="nav-item mt-3" x-data="{ openSec: {{ request()->is('usuarios*', 'roles*', 'asignar-roles*') ? 'true' : 'false' }} }">
                <a class="nav-link d-flex align-items-center justify-content-between text-white" 
                   @click.prevent="openSec = !openSec"
                   href="#" 
                   style="background: rgba(255,255,255,0.03); cursor: pointer;">
                    <span><i class="bi bi-shield-lock me-2 text-warning"></i> Acceso y Seguridad</span>
                    <i class="bi" :class="openSec ? 'bi-chevron-up' : 'bi-chevron-down'" style="font-size: 0.8rem;"></i>
                </a>
                
                <div x-show="openSec" class="mt-1 ps-2">
                    <ul class="nav nav-pills flex-column gap-1 mt-1 border-start border-secondary ps-2">
                        <li class="nav-item">
                            <a class="nav-link py-1 {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                                <i class="bi bi-person-gear me-2"></i> Administrar Usuarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-1 {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                                <i class="bi bi-shield-check me-2"></i> Administrar Roles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-1 {{ request()->routeIs('asignar-roles.*') ? 'active' : '' }}" href="{{ route('asignar-roles.index') }}">
                                <i class="bi bi-shield-fill-check me-2"></i> Roles Usuario
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif

            <!-- PARAMETRIZACIÓN COLLAPSIBLE MENU -->
            <li class="nav-item mt-3" x-data="{ openParam: {{ request()->is('miembros*', 'cultos*', 'actividades*', 'cursos*', 'cargos*', 'lideres*', 'ministerios*', 'docentes*', 'seguimientos*', 'contratos*') ? 'true' : 'false' }} }">
                <a class="nav-link d-flex align-items-center justify-content-between text-white" 
                   @click.prevent="openParam = !openParam"
                   href="#" 
                   style="background: rgba(255,255,255,0.03); cursor: pointer;">
                    <span><i class="bi bi-sliders me-2 text-warning"></i> Parametrización</span>
                    <i class="bi" :class="openParam ? 'bi-chevron-up' : 'bi-chevron-down'" style="font-size: 0.8rem;"></i>
                </a>
                
                <div x-show="openParam" class="mt-1 ps-2">
                    <ul class="nav nav-pills flex-column gap-1 mt-1 border-start border-secondary ps-2">
                        <!-- PERSONAS -->
                        <li class="nav-item text-muted text-uppercase fw-bold mt-2 mb-1" style="font-size: 0.7rem; letter-spacing: .05em;">Personas</li>
                        
                        @if(auth()->user()->tieneRol('Administrador') || auth()->user()->tieneRol('Secretario'))
                        <li class="nav-item">
                            <a class="nav-link py-1 {{ request()->routeIs('miembros.*') ? 'active' : '' }}" href="{{ route('miembros.index') }}">
                                <i class="bi bi-people me-2"></i> Miembros
                            </a>
                        </li>
                        @endif

                        <li class="nav-item">
                            <a class="nav-link py-1 {{ request()->routeIs('lideres.*') ? 'active' : '' }}" href="{{ route('lideres.index') }}">
                                <i class="bi bi-person-badge me-2"></i> Líderes Iglesia
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link py-1 {{ request()->routeIs('docentes.*') ? 'active' : '' }}" href="{{ route('docentes.index') }}">
                                <i class="bi bi-mortarboard me-2"></i> Docentes
                            </a>
                        </li>

                        <!-- ORGANIZACIÓN -->
                        <li class="nav-item text-muted text-uppercase fw-bold mt-3 mb-1" style="font-size: 0.7rem; letter-spacing: .05em;">Organización</li>
                        <li class="nav-item">
                            <a class="nav-link py-1 {{ request()->routeIs('ministerios.*') ? 'active' : '' }}" href="{{ route('ministerios.index') }}">
                                <i class="bi bi-diagram-3 me-2"></i> Ministerios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-1 {{ request()->routeIs('cargos.*') ? 'active' : '' }}" href="{{ route('cargos.index') }}">
                                <i class="bi bi-briefcase me-2"></i> Cargos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-1 {{ request()->routeIs('actividades.*') ? 'active' : '' }}" href="{{ route('actividades.index') }}">
                                <i class="bi bi-calendar-event me-2"></i> Actividades
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-1 {{ request()->routeIs('seguimientos.*') ? 'active' : '' }}" href="{{ route('seguimientos.index') }}">
                                <i class="bi bi-journal-check me-2"></i> Seguimientos
                            </a>
                        </li>

                        <!-- FINANZAS PARAM -->
                        @if(auth()->user()->tieneRol('Administrador') || auth()->user()->tieneRol('Tesorero'))
                        <li class="nav-item text-muted text-uppercase fw-bold mt-3 mb-1" style="font-size: 0.7rem; letter-spacing: .05em;">Finanzas</li>
                        <li class="nav-item">
                            <a class="nav-link py-1 {{ request()->routeIs('cultos.*') ? 'active' : '' }}" href="{{ route('cultos.index') }}">
                                <i class="bi bi-calendar-heart me-2"></i> Cultos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-1 {{ request()->routeIs('contratos.*') ? 'active' : '' }}" href="{{ route('contratos.index') }}">
                                <i class="bi bi-file-earmark-text me-2"></i> Contratos
                            </a>
                        </li>
                        @endif

                        <!-- ACADÉMICO -->
                        <li class="nav-item text-muted text-uppercase fw-bold mt-3 mb-1" style="font-size: 0.7rem; letter-spacing: .05em;">Académico</li>
                        <li class="nav-item">
                            <a class="nav-link py-1 {{ request()->routeIs('cursos.*') ? 'active' : '' }}" href="{{ route('cursos.index') }}">
                                <i class="bi bi-book me-2"></i> Cursos
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- TRANSACCIONAL COLLAPSIBLE MENU -->
            <li class="nav-item mt-3" x-data="{ openTran: {{ request()->is('sesiones*', 'inscripciones*', 'asistencia*', 'ingresos*', 'egresos*') ? 'true' : 'false' }} }">
                <a class="nav-link d-flex align-items-center justify-content-between text-white" 
                   @click.prevent="openTran = !openTran"
                   href="#" 
                   style="background: rgba(255,255,255,0.03); cursor: pointer;">
                    <span><i class="bi bi-arrow-left-right me-2 text-warning"></i> Transaccional</span>
                    <i class="bi" :class="openTran ? 'bi-chevron-up' : 'bi-chevron-down'" style="font-size: 0.8rem;"></i>
                </a>
                
                <div x-show="openTran" class="mt-1 ps-2">
                    <ul class="nav nav-pills flex-column gap-1 mt-1 border-start border-secondary ps-2">
                        <li class="nav-item">
                            <a class="nav-link py-1 {{ request()->routeIs('sesiones.*') ? 'active' : '' }}" href="{{ route('sesiones.index') }}">
                                <i class="bi bi-journal-text me-2"></i> Sesiones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-1 {{ request()->routeIs('inscripciones.*') ? 'active' : '' }}" href="{{ route('inscripciones.index') }}">
                                <i class="bi bi-journal-richtext me-2"></i> Inscripciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-1 {{ request()->routeIs('asistencia.*') ? 'active' : '' }}" href="{{ route('asistencia.index') }}">
                                <i class="bi bi-clipboard-check me-2"></i> Asistencia
                            </a>
                        </li>

                        <!-- FINANZAS -->
                        @if(auth()->user()->tieneRol('Administrador') || auth()->user()->tieneRol('Tesorero'))
                        <li class="nav-item text-muted text-uppercase fw-bold mt-3 mb-1" style="font-size: 0.7rem; letter-spacing: .05em;">Finanzas</li>
                        <li class="nav-item">
                            <a class="nav-link py-1 {{ request()->routeIs('ingresos.*') ? 'active' : '' }}" href="{{ route('ingresos.index') }}">
                                <i class="bi bi-cash-coin me-2"></i> Ingresos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-1 {{ request()->routeIs('egresos.*') ? 'active' : '' }}" href="{{ route('egresos.index') }}">
                                <i class="bi bi-cash-stack me-2"></i> Egresos
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>

            <!-- REPORTE COLLAPSIBLE MENU -->
            <li class="nav-item mt-3" x-data="{ openRep: {{ request()->is('reportes*') ? 'true' : 'false' }} }">
                <a class="nav-link d-flex align-items-center justify-content-between text-white" 
                   @click.prevent="openRep = !openRep"
                   href="#" 
                   style="background: rgba(255,255,255,0.03); cursor: pointer;">
                    <span><i class="bi bi-graph-up me-2 text-warning"></i> Reporte</span>
                    <i class="bi" :class="openRep ? 'bi-chevron-up' : 'bi-chevron-down'" style="font-size: 0.8rem;"></i>
                </a>
                
                <div x-show="openRep" class="mt-1 ps-2">
                    <ul class="nav nav-pills flex-column gap-1 mt-1 border-start border-secondary ps-2">
                        <li class="nav-item">
                            <a class="nav-link py-1 {{ request()->routeIs('reportes.*') ? 'active' : '' }}" href="{{ route('reportes.index') }}">
                                <i class="bi bi-graph-up me-2"></i> Reportes
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>

    <div class="mt-auto pt-3 border-top border-secondary">
        <div class="text-muted small mb-2">
            <i class="bi bi-person-circle me-1"></i> {{ auth()->user()->name ?? 'Usuario' }}
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i> Salir
            </button>
        </form>
    </div>
</div>