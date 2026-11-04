@extends('layouts.app')

@section('title', 'Registrar Asistencia')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('asistencia.index') }}" class="text-gold-500 text-decoration-none">Asistencia</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Registrar</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <h2 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-clipboard-check me-2"></i>Registrar Asistencia</h2>

    <form method="GET" action="{{ route('asistencia.registrar') }}" class="row g-3 mb-4">
        <div class="col-md-6">
            <select name="sesion_id" class="form-select" onchange="this.form.submit()">
                <option value="">-- Seleccionar Sesión --</option>
                @foreach($cursos as $curso)
                    <optgroup label="{{ $curso->nombre }}">
                        @foreach($curso->sesiones as $sesion)
                            <option value="{{ $sesion->id }}" {{ request('sesion_id') == $sesion->id ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::parse($sesion->fecha)->format('d/m/Y') }} - {{ $sesion->tema ?? 'Sin tema' }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
    </form>

    @if($sesion)
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="p-3 rounded border text-center" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                    <div class="text-muted small text-uppercase">Curso</div>
                    <div class="fw-bold">{{ $sesion->curso->nombre ?? 'N/A' }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded border text-center" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                    <div class="text-muted small text-uppercase">Sesión</div>
                    <div class="fw-bold">{{ \Carbon\Carbon::parse($sesion->fecha)->format('d/m/Y') }} - {{ $sesion->hora ? substr($sesion->hora, 0, 5) : '--:--' }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded border text-center" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                    <div class="text-muted small text-uppercase">Resumen</div>
                    <div class="fw-bold">
                        <span class="badge bg-success">P: {{ $resumen['Presente'] }}</span>
                        <span class="badge bg-danger">A: {{ $resumen['Ausente'] }}</span>
                        <span class="badge bg-warning text-dark">T: {{ $resumen['Tardanza'] }}</span>
                        <span class="badge bg-info text-dark">J: {{ $resumen['Justificado'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('asistencia.store', $sesion->id) }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Miembro</th>
                            <th style="width: 200px;">Estado</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inscritos as $inscripcion)
                        @php $asist = $asistencias->get($inscripcion->miembro_id); @endphp
                        <tr>
                            <td><strong>{{ $inscripcion->miembro->nombre ?? 'N/A' }}</strong></td>
                            <td>
                                <select name="asistencia[{{ $inscripcion->miembro_id }}][estado]" class="form-select form-select-sm">
                                    @foreach(['Presente', 'Ausente', 'Tardanza', 'Justificado'] as $estado)
                                        <option value="{{ $estado }}" {{ $asist?->estado === $estado ? 'selected' : '' }}>{{ $estado }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="asistencia[{{ $inscripcion->miembro_id }}][observacion]" class="form-control form-control-sm" value="{{ $asist?->observacion }}" placeholder="Opcional">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">No hay miembros inscritos en este curso para registrar asistencia.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($inscritos->isNotEmpty())
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-church">
                    <i class="bi bi-save me-1"></i> Guardar Asistencia
                </button>
            </div>
            @endif
        </form>
    @else
        <div class="alert alert-info">Selecciona una sesión para registrar la asistencia de los miembros inscritos.</div>
    @endif
</div>
@endsection