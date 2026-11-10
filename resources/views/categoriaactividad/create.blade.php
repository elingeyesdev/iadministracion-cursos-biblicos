@extends('layouts.app')

@section('title', 'Nueva Categoría')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <h1 class="sidebar-title" style="font-size:2rem;">Nueva categoría</h1>
        <p class="text-muted mb-0">Agrega una categoría para organizar actividades.</p>
    </div>
    <a href="{{ route('categoriaactividad.index') }}" class="btn btn-outline-church">← Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('categoriaactividad.store') }}">
            @csrf
            <div class="mb-3">
                <label for="nombre" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}"
                       class="form-control @error('nombre') is-invalid @enderror">
                @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="4" class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion') }}</textarea>
                @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Estado</label>
                <div class="d-flex flex-wrap gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="estado" id="estado_1" value="1" @checked(old('estado', '1') == '1')>
                        <label class="form-check-label" for="estado_1">Activa</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="estado" id="estado_0" value="0" @checked(old('estado') == '0')>
                        <label class="form-check-label" for="estado_0">Inactiva</label>
                    </div>
                </div>
                @error('estado') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-church">Guardar categoría</button>
                <a href="{{ route('categoriaactividad.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@endsection
