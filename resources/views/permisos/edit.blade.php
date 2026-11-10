@extends('layouts.app')

@section('title', 'Permisos del Rol')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('permisos.index') }}" class="text-gold-500 text-decoration-none">Permisos</a></li>
    <li class="breadcrumb-item active text-white" aria-current="page">{{ $role->nombre }}</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Permisos del rol: {{ $role->nombre }}</h1>
            <p class="text-muted mb-0">Marca las acciones que este rol puede realizar en cada módulo.</p>
        </div>
        <a href="{{ route('permisos.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    @if($role->nombre === 'Administrador')
        <div class="alert alert-success border-0 mb-4">
            El rol <strong>Administrador</strong> siempre tiene acceso total al sistema; estos permisos se muestran solo como referencia.
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 mb-4">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('permisos.update', $role->id) }}">
        @csrf
        @method('PUT')

        @foreach($categorias as $categoria => $modulos)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-0 bg-body-secondary py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-key-fill text-gold-500 me-2"></i>{{ $categoria }}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="min-width: 640px;">
                        <thead>
                            <tr>
                                <th scope="col" class="border-0 text-start">Módulo</th>
                                @foreach($acciones as $accionKey => $accionLabel)
                                    <th scope="col" class="border-0 text-center">{{ $accionLabel }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modulos as $modulo => $meta)
                                @php $permisoActual = $actuales->get($modulo); @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $meta['label'] ?? $modulo }}</td>
                                    @foreach($acciones as $accionKey => $accionLabel)
                                        <td class="text-center">
                                            @if(in_array($accionKey, $meta['acciones'] ?? [], true))
                                                @php
                                                    $marcado = old(
                                                        "permisos.{$modulo}.{$accionKey}",
                                                        $permisoActual ? (bool) $permisoActual->{$accionKey} : false
                                                    );
                                                @endphp
                                                <input type="checkbox"
                                                       class="form-check-input"
                                                       name="permisos[{{ $modulo }}][{{ $accionKey }}]"
                                                       value="1"
                                                       aria-label="{{ $meta['label'] ?? $modulo }}: {{ $accionLabel }}"
                                                       @checked($marcado)>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        <div class="d-flex gap-2 mt-2">
            <button type="submit" class="btn btn-church px-4">
                <i class="bi bi-save-fill me-1"></i> Guardar Permisos
            </button>
            <a href="{{ route('permisos.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
        </div>
    </form>
</div>
@endsection
