@extends('layouts.app')

@section('title', 'Nueva Sesión')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sesiones.index') }}" class="text-gold-500 text-decoration-none">Sesiones</a></li>
    <li class="breadcrumb-item active text-muted" aria-current="page">Nueva</li>
@endsection

@section('content')
<div class="card p-4 shadow-sm">
    <h2 class="sidebar-title mb-4" style="color: var(--church-gold);"><i class="bi bi-journal-plus me-2"></i>Registrar Nueva Sesión</h2>

    <form action="{{ route('sesiones.store') }}" method="POST">
        @csrf
        
        <div class="row g-3">
            <div class="col-md-6">
                <label for="id_curso" class="form-label">Curso <span class="text-danger">*</span></label>
                <select class="form-select @error('id_curso') is-invalid @enderror" id="id_curso" name="id_curso" required>
                    <option value="" disabled selected>Seleccione el curso...</option>
                    @foreach($cursos as $curso)
                        <option value="{{ $curso->id }}" {{ old('id_curso') == $curso->id ? 'selected' : '' }}>
                            {{ $curso->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('id_curso')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="tema" class="form-label">Tema de la Sesión</label>
                <input type="text" class="form-control @error('tema') is-invalid @enderror" id="tema" name="tema" value="{{ old('tema') }}" placeholder="Ej. Introducción al Génesis">
                @error('tema')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="fecha" class="form-label">Fecha de la Clase <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('fecha') is-invalid @enderror" id="fecha" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required>
                @error('fecha')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="hora" class="form-label">Hora</label>
                <input type="time" class="form-control @error('hora') is-invalid @enderror" id="hora" name="hora" value="{{ old('hora', '19:30') }}">
                @error('hora')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="observacion" class="form-label">Observaciones</label>
                <textarea class="form-control @error('observacion') is-invalid @enderror" id="observacion" name="observacion" rows="3" placeholder="Anotaciones importantes para la clase...">{{ old('observacion') }}</textarea>
                @error('observacion')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 mt-4">
                <label class="form-label d-block fw-bold" style="color: var(--church-gold);">Inscribir Miembros en esta Sesión</label>
                <div class="row g-2" style="max-height: 250px; overflow-y: auto;">
                    @forelse($miembros as $miembro)
                        <div class="col-md-4 col-sm-6">
                            <div class="p-2 rounded border" style="background-color: var(--church-surface-2); border-color: var(--church-border) !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="miembros[]" value="{{ $miembro->id }}" id="miembro_{{ $miembro->id }}" {{ is_array(old('miembros')) && in_array($miembro->id, old('miembros')) ? 'checked' : '' }}>
                                    <label class="form-check-label text-truncate w-100" for="miembro_{{ $miembro->id }}">
                                        {{ $miembro->nombre }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-muted small">No hay miembros disponibles para inscribir.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-church">
                <i class="bi bi-save me-1"></i> Guardar
            </button>
            <a href="{{ route('sesiones.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i> Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
