@extends('layouts.app')

@section('title', 'Editar Seguimiento')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <h1 class="sidebar-title" style="font-size:2rem;">Editar seguimiento</h1>
        <p class="text-muted mb-0">Actualiza el avance registrado.</p>
    </div>
    <a href="{{ route('seguimiento.index') }}" class="btn btn-outline-church">← Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('seguimiento.update', $seguimiento) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-12">
                    <label for="id_actividad" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Actividad</label>
                    <select id="id_actividad" name="id_actividad" class="form-select @error('id_actividad') is-invalid @enderror">
                        <option value="">Seleccionar…</option>
                        @foreach($actividades as $act)
                            <option value="{{ $act->id }}" @selected(old('id_actividad', $seguimiento->id_actividad) == $act->id)>{{ $act->nombre }}</option>
                        @endforeach
                    </select>
                    @error('id_actividad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="fecha" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Fecha</label>
                    <input type="date" id="fecha" name="fecha" value="{{ old('fecha', $seguimiento->fecha->format('Y-m-d')) }}"
                           class="form-control @error('fecha') is-invalid @enderror">
                    @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="porcentaje_avance" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Porcentaje de avance</label>
                    <input type="number" min="0" max="100" id="porcentaje_avance" name="porcentaje_avance" value="{{ old('porcentaje_avance', $seguimiento->porcentaje_avance) }}"
                           class="form-control @error('porcentaje_avance') is-invalid @enderror">
                    @error('porcentaje_avance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label for="estado" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Estado</label>
                    <select id="estado" name="estado" class="form-select @error('estado') is-invalid @enderror">
                        @foreach(['pendiente' => 'Pendiente', 'en_proceso' => 'En proceso', 'finalizada' => 'Finalizada'] as $k => $label)
                            <option value="{{ $k }}" @selected(old('estado', $seguimiento->estado) === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label for="comentario" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Comentario (opcional)</label>
                    <textarea id="comentario" name="comentario" rows="4" class="form-control @error('comentario') is-invalid @enderror">{{ old('comentario', $seguimiento->comentario) }}</textarea>
                    @error('comentario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-church">Actualizar</button>
                <a href="{{ route('seguimiento.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@endsection
