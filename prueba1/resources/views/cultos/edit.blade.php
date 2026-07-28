@extends('layouts.app')

@section('title', 'Editar Culto')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('cultos.index') }}" class="text-gold-500 text-decoration-none">Cultos</a></li>
    <li class="breadcrumb-item active text-white" aria-current="page">Editar Culto</li>
@endsection

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h2 text-white mb-1">Editar Culto</h1>
            <p class="text-muted mb-0">Modifica los detalles del culto registrado.</p>
        </div>
        <a href="{{ route('cultos.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 mb-4 bg-danger text-white">
            <h5 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Por favor corrige los siguientes errores:</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important; max-width: 800px;">
        <div class="card-header border-0 bg-dark py-3">
            <h5 class="card-title text-white mb-0"><i class="bi bi-calendar-heart text-gold-500 me-2"></i>Detalles del Culto</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('cultos.update', $culto->id_culto) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="nombre" class="form-label text-white fw-semibold">Nombre del Culto <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" id="nombre" class="form-control bg-transparent text-white border-secondary @error('nombre') is-invalid @enderror" value="{{ old('nombre', $culto->nombre) }}" required>
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="dia_semana" class="form-label text-white fw-semibold">Día de la semana <span class="text-danger">*</span></label>
                        <select name="dia_semana" id="dia_semana" class="form-select bg-transparent text-white border-secondary @error('dia_semana') is-invalid @enderror" required style="background-color: var(--church-surface, #1e1e2d) !important;">
                            @foreach(['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'] as $dia)
                                <option value="{{ $dia }}" {{ old('dia_semana', $culto->dia_semana) === $dia ? 'selected' : '' }} style="background: #1e1e2d; color: #fff;">{{ ucfirst($dia) }}</option>
                            @endforeach
                        </select>
                        @error('dia_semana') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="hora" class="form-label text-white fw-semibold">Hora <span class="text-danger">*</span></label>
                        <input type="time" name="hora" id="hora" class="form-control bg-transparent text-white border-secondary @error('hora') is-invalid @enderror" value="{{ old('hora', \Carbon\Carbon::parse($culto->hora)->format('H:i')) }}" required>
                        @error('hora') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label text-white fw-semibold">Descripción</label>
                    <textarea name="descripcion" id="descripcion" rows="4" class="form-control bg-transparent text-white border-secondary @error('descripcion') is-invalid @enderror" placeholder="Agrega notas o detalles sobre este culto...">{{ old('descripcion', $culto->descripcion) }}</textarea>
                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mt-4 d-flex gap-3">
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="bi bi-save-fill me-1"></i> Actualizar Culto
                    </button>
                    <a href="{{ route('cultos.index') }}" class="btn btn-secondary px-4 py-2">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
