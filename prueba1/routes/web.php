<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoriaActividadController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\MiembroController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;

Route::resource('roles', RoleController::class)->middleware(['auth', 'role:administrador']);

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
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';