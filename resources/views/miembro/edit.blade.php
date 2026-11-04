@extends('layouts.app')

@section('title', 'Editar Miembro')

@section('content')

<div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-3">
    <div>
        <h1 class="sidebar-title" style="font-size:2rem;">Editar Miembro</h1>
        <p class="text-muted mb-0">Actualiza la información del miembro.</p>
    </div>
    <a href="{{ route('miembro.index') }}" class="btn btn-outline-church">← Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('miembro.update', $miembro) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Nombre</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $miembro->nombre) }}" class="form-control @error('nombre') is-invalid @enderror">
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Apellido</label>
                    <input type="text" name="apellido" value="{{ old('apellido', $miembro->apellido) }}" class="form-control @error('apellido') is-invalid @enderror">
                    @error('apellido') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Correo</label>
                    <input type="email" name="correo" value="{{ old('correo', $miembro->correo) }}" class="form-control @error('correo') is-invalid @enderror">
                    @error('correo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Teléfono (opcional)</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $miembro->telefono) }}" class="form-control">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted" style="letter-spacing:.18em; text-transform:uppercase; font-size:.75rem;">Rol</label>
                    <select name="rol" class="form-select @error('rol') is-invalid @enderror">
                        @foreach(['administrador','miembro','supervisor'] as $rol)
                            <option value="{{ $rol }}" {{ old('rol', $miembro->rol) === $rol ? 'selected' : '' }}>{{ ucfirst($rol) }}</option>
                        @endforeach
                    </select>
                    @error('rol') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-church">Actualizar</button>
                <a href="{{ route('miembro.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@endsection