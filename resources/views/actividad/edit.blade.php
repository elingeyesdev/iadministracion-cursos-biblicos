@extends('layouts.app')

@section('title', 'Editar Actividad')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <h1 class="sidebar-title" style="font-size:2rem;">Editar actividad</h1>
        <p class="text-muted mb-0">Modifica los datos de <strong>{{ $actividad->nombre }}</strong>.</p>
    </div>
    <a href="{{ route('actividad.index') }}" class="btn btn-outline-church">← Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('actividad.update', $actividad) }}">
            @csrf
            @method('PUT')
            @php $estadoActual = old('estado', $actividad->estado); @endphp
            <div class="row g-3">
                <div class="col-12">
                    <label for="nombre" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $actividad->nombre) }}" autofocus
                           class="form-control @error('nombre') is-invalid @enderror">
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="id_categoria" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Categoría</label>
                    <select id="id_categoria" name="id_categoria" class="form-select @error('id_categoria') is-invalid @enderror">
                        <option value="">Selecciona una categoría</option>
                        @forelse($categorias as $cat)
                            <option value="{{ $cat->id }}" @selected(old('id_categoria', $actividad->id_categoria) == $cat->id)>{{ $cat->nombre }}</option>
                        @empty
                            <option disabled>No hay categorías disponibles</option>
                        @endforelse
                    </select>
                    @error('id_categoria') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="id_responsable" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Responsable</label>
                    <select id="id_responsable" name="id_responsable" class="form-select @error('id_responsable') is-invalid @enderror">
                        <option value="">Selecciona un responsable</option>
                        @forelse($miembros as $miem)
                            <option value="{{ $miem->id }}" @selected(old('id_responsable', $actividad->id_responsable) == $miem->id)>{{ $miem->nombre }}</option>
                        @empty
                            <option disabled>No hay miembros disponibles</option>
                        @endforelse
                    </select>
                    @error('id_responsable') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="fecha_inicio" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Fecha de inicio</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', $actividad->fecha_inicio->format('Y-m-d')) }}"
                           class="form-control @error('fecha_inicio') is-invalid @enderror">
                    @error('fecha_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="fecha_fin" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Fecha de fin (opcional)</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="{{ old('fecha_fin', $actividad->fecha_fin ? $actividad->fecha_fin->format('Y-m-d') : '') }}"
                           class="form-control @error('fecha_fin') is-invalid @enderror">
                    @error('fecha_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label for="descripcion" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="4" class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $actividad->descripcion) }}</textarea>
                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Estado</label>
                    @foreach(['pendiente' => 'Pendiente', 'en_proceso' => 'En proceso', 'finalizada' => 'Finalizada'] as $value => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="estado" id="estado_{{ $value }}" value="{{ $value }}"
                                   @checked($estadoActual === $value)>
                            <label class="form-check-label" for="estado_{{ $value }}">{{ $label }}</label>
                        </div>
                    @endforeach
                    @error('estado') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="es_pago" id="es_pago" value="1"
                               @checked(old('es_pago', $actividad->es_pago))>
                        <label class="form-check-label" for="es_pago">Esta actividad es de pago</label>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-church">Guardar cambios</button>
                <a href="{{ route('actividad.show', $actividad) }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@endsection
