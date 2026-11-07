@extends('layouts.app')

@section('title', 'Pago exitoso')

@section('content')
<div class="mb-4">
    <h1 class="sidebar-title">Pago exitoso</h1>
    <p class="text-muted">Gracias por tu donaci├│n. El pago se proces├│ correctamente.</p>
</div>

<div class="card">
    <div class="card-body">
        <p class="mb-0">ID de sesi├│n de pago: <strong>{{ $sessionId ?? 'N/A' }}</strong></p>
    </div>
</div>
@endsection
