@extends('layouts.app')
@section('content')
<div class="d-flex flex-column align-items-center justify-content-center" style="min-height:60vh">
    <i class="bi bi-shield-lock-fill text-danger" style="font-size:6rem"></i>
    <h1 class="mt-3 fw-bold">403</h1>
    <h4 class="text-muted">Acceso Denegado</h4>
    <p class="text-muted">No ten├®s permiso para acceder a esta secci├│n.</p>
    <a href="{{ url()->previous() }}" class="btn btn-secondary mt-2">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>
@endsection
