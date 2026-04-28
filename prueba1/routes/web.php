<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoriaActividadController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\MiembroController;
use App\Http\Controllers\AsignacionActividadController;
use App\Http\Controllers\SeguimientoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UsuarioRolController;
use App\Http\Controllers\UsuarioController;

Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    Route::get('asignar-roles', [UsuarioRolController::class, 'index'])->name('asignar-roles.index');
    Route::get('asignar-roles/{user}/edit', [UsuarioRolController::class, 'edit'])->name('asignar-roles.edit');
    Route::put('asignar-roles/{user}', [UsuarioRolController::class, 'update'])->name('asignar-roles.update');

    Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::get('usuarios/create', [UsuarioController::class, 'create'])->name('usuarios.create');
    Route::post('usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::get('usuarios/{user}', [UsuarioController::class, 'show'])->name('usuarios.show');
    Route::get('usuarios/{user}/edit', [UsuarioController::class, 'edit'])->name('usuarios.edit');
    Route::put('usuarios/{user}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('usuarios/{user}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
});

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Rutas protegidas
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('categoriaactividad', CategoriaActividadController::class);
    Route::resource('actividad', ActividadController::class);
    Route::resource('miembro', MiembroController::class);
    Route::resource('asignacionactividad', AsignacionActividadController::class);
    Route::resource('seguimiento', SeguimientoController::class);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';