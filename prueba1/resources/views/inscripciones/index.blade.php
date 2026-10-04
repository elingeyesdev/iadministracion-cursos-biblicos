@extends('layouts.app')

@section('title', 'Inscripciones a Cursos')

@section('breadcrumb')
    <li class="breadcrumb-item active text-muted" aria-current="page">Inscripciones</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="sidebar-title mb-0" style="color: var(--church-gold);"><i class="bi bi-journal-richtext me-2"></i>Inscripciones a Cursos</h2>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Curso</th>
                    <th>Inicio</th>
                    <th>Término</th>
                    <th>Inscritos</th>
                    <th>Cupo Máximo</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cursos as $curso)
                <tr>
                    <td><strong>{{ $curso->nombre }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($curso->f_inicio)->format('d/m/Y') }}</td>
                    <td>{{ $curso->f_fin ? \Carbon\Carbon::parse($curso->f_fin)->format('d/m/Y') : 'N/A' }}</td>
                    <td><span class="badge bg-info text-dark">{{ $curso->inscripciones_count }}</span></td>
                    <td>{{ $curso->cupo_max ?? 'Sin límite' }}</td>
                    <td class="text-end">
                        <a href="{{ route('inscripciones.gestion', $curso->id) }}" class="btn btn-sm btn-church" title="Gestionar Inscripciones">
                            <i class="bi bi-people me-1"></i> Gestionar
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No hay cursos registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $cursos->links() }}
    </div>
</div>
@endsection