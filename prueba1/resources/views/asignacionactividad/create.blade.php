@extends('layouts.app')

@section('title', 'Crear Asignación')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <h1 class="sidebar-title" style="font-size:2rem;">Crear asignación</h1>
        <p class="text-muted mb-0">Asigna un miembro a una actividad.</p>
    </div>
    <a href="{{ route('asignacionactividad.index') }}" class="btn btn-outline-church">← Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('asignacionactividad.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="id_actividad" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Actividad</label>
                    <select id="id_actividad" name="id_actividad" class="form-select @error('id_actividad') is-invalid @enderror">
                        <option value="">Seleccionar…</option>
                        @foreach($actividades as $act)
                            <option value="{{ $act->id }}" @selected(old('id_actividad') == $act->id)>{{ $act->nombre }}</option>
                        @endforeach
                    </select>
                    @error('id_actividad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="id_miembro" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Miembro</label>
                    <select id="id_miembro" name="id_miembro" class="form-select @error('id_miembro') is-invalid @enderror">
                        <option value="">Seleccionar…</option>
                        @foreach($miembros as $m)
                            <option value="{{ $m->id }}" @selected(old('id_miembro') == $m->id)>
                                {{ $m->nombre }} {{ $m->apellido }} ({{ $m->correo }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_miembro') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="rol_en_actividad" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Rol (opcional)</label>
                    <input type="text" id="rol_en_actividad" name="rol_en_actividad" value="{{ old('rol_en_actividad') }}"
                           class="form-control @error('rol_en_actividad') is-invalid @enderror">
                    @error('rol_en_actividad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="fecha_asignacion" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Fecha de asignación (opcional)</label>
                    <input type="date" id="fecha_asignacion" name="fecha_asignacion" value="{{ old('fecha_asignacion') }}"
                           class="form-control @error('fecha_asignacion') is-invalid @enderror">
                    @error('fecha_asignacion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-church">Guardar</button>
                <a href="{{ route('asignacionactividad.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@endsection
