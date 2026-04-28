@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="mb-4">
    <h1 class="sidebar-title" style="font-size:2rem;">Dashboard</h1>
    <p class="text-muted mb-0">Bienvenido, {{ Auth::user()->name }}.</p>
</div>

<div class="row g-3">
    <div class="col-12 col-md-6">
        <a href="{{ route('profile.edit') }}" class="text-decoration-none">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small" style="letter-spacing:.18em; text-transform:uppercase;">Cuenta</div>
                    <div class="sidebar-title mt-1" style="font-size:1.25rem;">Mi Perfil</div>
                    <div class="text-muted mt-2">Edita tu información personal.</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-md-6">
        <a href="{{ route('miembro.index') }}" class="text-decoration-none">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small" style="letter-spacing:.18em; text-transform:uppercase;">Gestión</div>
                    <div class="sidebar-title mt-1" style="font-size:1.25rem;">Miembros</div>
                    <div class="text-muted mt-2">Gestiona los miembros del sistema.</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-md-6">
        <a href="{{ route('actividad.index') }}" class="text-decoration-none">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small" style="letter-spacing:.18em; text-transform:uppercase;">Gestión</div>
                    <div class="sidebar-title mt-1" style="font-size:1.25rem;">Actividades</div>
                    <div class="text-muted mt-2">Gestiona todas las actividades.</div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-md-6">
        <a href="{{ route('categoriaactividad.index') }}" class="text-decoration-none">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small" style="letter-spacing:.18em; text-transform:uppercase;">Gestión</div>
                    <div class="sidebar-title mt-1" style="font-size:1.25rem;">Categorías</div>
                    <div class="text-muted mt-2">Administra las categorías de actividades.</div>
                </div>
            </div>
        </a>
    </div>

    @if(auth()->user()?->role === 'administrador')
    <div class="col-12 col-md-6">
        <a href="{{ route('usuarios.index') }}" class="text-decoration-none">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small" style="letter-spacing:.18em; text-transform:uppercase;">Seguridad</div>
                    <div class="sidebar-title mt-1" style="font-size:1.25rem;">Usuarios</div>
                    <div class="text-muted mt-2">Gestiona cuentas y datos de acceso.</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-6">
        <a href="{{ route('asignar-roles.index') }}" class="text-decoration-none">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small" style="letter-spacing:.18em; text-transform:uppercase;">Seguridad</div>
                    <div class="sidebar-title mt-1" style="font-size:1.25rem;">Asignar roles</div>
                    <div class="text-muted mt-2">Define qué roles tiene cada usuario.</div>
                </div>
            </div>
        </a>
    </div>
    @endif
</div>

@endsection