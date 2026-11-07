@extends('layouts.app')

@section('title', 'Detalle del Docente')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('docentes.index') }}" class="text-gold-500 text-decoration-none">Docentes</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Detalle</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-md-5">
        <div class="card p-4 shadow-sm h-100">
            <h3 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-mortarboard me-2"></i>Informaci├│n del Docente</h3>

            <table class="table table-borderless">
                <tr>
                    <th class="ps-0 py-2 text-muted" style="width: 35%;">Nombre:</th>
                    <td class="py-2"><strong>{{ $docente->miembro->nombre ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Especialidad:</th>
                    <td class="py-2">{{ $docente->especialidad ?? 'Sin especialidad registrada' }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">T├¡tulo:</th>
                    <td class="py-2">{{ $docente->titulo ?? 'Sin t├¡tulo registrado' }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Estado:</th>
                    <td class="py-2">
                        @if($docente->activo)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-danger">Inactivo</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Miembro desde:</th>
                    <td class="py-2">{{ $docente->miembro->fecha_ingreso ? \Carbon\Carbon::parse($docente->miembro->fecha_ingreso)->format('d/m/Y') : 'N/A' }}</td>
                </tr>
            </table>

            <div class="mt-4 d-flex gap-2">
                <a href="{{ route('docentes.edit', $docente->id) }}" class="btn btn-church">
                    <i class="bi bi-pencil me-1"></i> Editar
                </a>
                <a href="{{ route('docentes.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card p-4 shadow-sm h-100">
            <h4 class="sidebar-title mb-3" style="color: var(--church-gold);"><i class="bi bi-book me-2"></i>Cursos que Dicta</h4>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Cupo M├íx.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($docente->cursos as $curso)
                        <tr>
                            <td><strong>{{ $curso->nombre }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($curso->f_inicio)->format('d/m/Y') }}</td>
                            <td>{{ $curso->f_fin ? \Carbon\Carbon::parse($curso->f_fin)->format('d/m/Y') : 'Sin fecha fin' }}</td>
                            <td>{{ $curso->cupo_max ?? 'Ilimitado' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">No dicta ning├║n curso en este momento.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
