@extends('layouts.app')

@section('title', 'Detalle del Curso')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('cursos.index') }}" class="text-gold-500 text-decoration-none">Cursos</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Detalle</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-md-5">
        <div class="card p-4 shadow-sm h-100">
            <h3 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-book me-2"></i>Informaci├│n del Curso</h3>

            <table class="table table-borderless">
                <tr>
                    <th class="ps-0 py-2 text-muted" style="width: 35%;">Curso:</th>
                    <td class="py-2"><strong>{{ $curso->nombre }}</strong></td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Fecha Inicio:</th>
                    <td class="py-2">{{ \Carbon\Carbon::parse($curso->f_inicio)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Fecha Fin:</th>
                    <td class="py-2">{{ $curso->f_fin ? \Carbon\Carbon::parse($curso->f_fin)->format('d/m/Y') : 'Sin fecha fin registrada' }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Cupo M├íximo:</th>
                    <td class="py-2">{{ $curso->cupo_max ?? 'Ilimitado' }}</td>
                </tr>
                <tr>
                    <th class="ps-0 py-2 text-muted">Tipo:</th>
                    <td class="py-2">
                        @if($curso->tiene_pago)
                            <span class="badge bg-warning text-dark">Matriculado: ${{ number_format($curso->monto_inscripcion, 2) }}</span>
                        @else
                            <span class="badge bg-success">Gratuito</span>
                        @endif
                    </td>
                </tr>
            </table>

            <div class="mt-4 d-flex gap-2">
                <a href="{{ route('cursos.edit', $curso->id) }}" class="btn btn-church">
                    <i class="bi bi-pencil me-1"></i> Editar
                </a>
                <a href="{{ route('cursos.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card p-4 shadow-sm mb-4">
            <h4 class="sidebar-title mb-3" style="color: var(--church-gold);"><i class="bi bi-mortarboard me-2"></i>Docentes Asignados</h4>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Docente</th>
                            <th>Especialidad</th>
                            <th>T├¡tulo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($curso->docentes as $docente)
                        <tr>
                            <td><strong>{{ $docente->miembro->nombre ?? 'N/A' }}</strong></td>
                            <td>{{ $docente->especialidad ?? 'N/A' }}</td>
                            <td>{{ $docente->titulo ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">No hay docentes asignados a este curso.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-4 shadow-sm">
            <h4 class="sidebar-title mb-3" style="color: var(--church-gold);"><i class="bi bi-journal-text me-2"></i>Sesiones Planificadas</h4>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Tema</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th class="text-end">Acci├│n</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($curso->sesiones as $sesion)
                        <tr>
                            <td><strong>{{ $sesion->tema ?? 'Sin Tema' }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($sesion->fecha)->format('d/m/Y') }}</td>
                            <td>{{ $sesion->hora ? \Carbon\Carbon::parse($sesion->hora)->format('H:i') : 'Sin hora' }}</td>
                            <td class="text-end">
                                <a href="{{ route('sesiones.show', $sesion->id) }}" class="btn btn-sm btn-outline-info py-0 px-1">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">No hay sesiones planificadas para este curso.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
