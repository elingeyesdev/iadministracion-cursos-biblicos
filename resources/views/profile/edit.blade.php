@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')

<div class="mb-4">
    <h1 class="sidebar-title" style="font-size:2rem;">Mi Perfil</h1>
    <p class="text-muted">Actualiza tu información, contraseña y opciones de cuenta.</p>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>

@endsection
