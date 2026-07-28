@extends('layouts.app')

@section('title', 'Donar')

@section('content')
<div class="mb-4">
    <h1 class="sidebar-title">Donaciones / Ingresos</h1>
    <p class="text-muted">Ingrese el monto y proceda al pago mediante Stripe Checkout (modo prueba).</p>
</div>

<div class="row">
    <div class="col-12 col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="/stripe">
                    @csrf
                    <div class="mb-3">
                        <label for="amount" class="form-label">Monto (USD)</label>
                        <input id="amount" name="amount" type="number" step="0.01" min="0.01" value="1.00" class="form-control" required>
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-primary">Pagar con Stripe (Checkout)</button>
                    </div>
                </form>

                <hr>
                <div class="text-muted small">Tarjeta de prueba: <strong>4242 4242 4242 4242</strong> — Cualquier fecha futura / CVC</div>
            </div>
        </div>
    </div>
</div>

@endsection
