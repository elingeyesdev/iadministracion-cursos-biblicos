@extends('layouts.app')

@section('title', 'Administrar Asistencia')

@section('breadcrumb')
    <li class="breadcrumb-item active text-muted" aria-current="page">Asistencia</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="sidebar-title mb-0" style="color: var(--church-gold);"><i class="bi bi-clipboard-check me-2"></i>Registro de Asistencia</h2>
        <a href="{{ route('asistencia.registrar') }}" class="btn btn-church">
            <i class="bi bi-plus-circle me-1"></i> Registrar Asistencia
        </a>
    </div>

    <form method="GET" action="{{ route('asistencia.index') }}" class="row g-3 mb-3">
        <div class="col-md-4">
            <input type="text" name="q" class="form-control" placeholder="Buscar miembro..." value="{{ request('q') }}">
        </div>
        <div class="col-md-3">
            <select name="curso_id" class="form-select">
                <option value="">-- Curso --</option>
                @foreach($cursos as $curso)
                    <option value="{{ $curso->id }}" {{ request('curso_id') == $curso->id ? 'selected' : '' }}>{{ $curso->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="estado" class="form-select">
                <option value="">-- Estado --</option>
                @foreach(['Presente', 'Ausente', 'Tardanza', 'Justificado'] as $estado)
                    <option value="{{ $estado }}" {{ request('estado') === $estado ? 'selected' : '' }}>{{ $estado }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-church w-100"><i class="bi bi-funnel me-1"></i> Filtrar</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Miembro</th>
                    <th>Curso</th>
                    <th>Sesión</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($asistencias as $asistencia)
                <tr>
                    <td><strong>{{ $asistencia->miembro->nombre ?? 'N/A' }}</strong></td>
                    <td>{{ $asistencia->sesion->curso->nombre ?? 'N/A' }}</td>
                    <td>{{ $asistencia->sesion->tema ?? 'N/A' }}</td>
                    <td>{{ isset($asistencia->sesion->fecha) ? \Carbon\Carbon::parse($asistencia->sesion->fecha)->format('d/m/Y') : 'N/A' }}</td>
                    <td>
                        @php
                            $colores = ['Presente' => 'success', 'Ausente' => 'danger', 'Tardanza' => 'warning', 'Justificado' => 'info'];
                        @endphp
                        <span class="badge bg-{{ $colores[$asistencia->estado] ?? 'secondary' }}">{{ $asistencia->estado }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No hay registros de asistencia.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $asistencias->links() }}
    </div>
</div>
@endsection