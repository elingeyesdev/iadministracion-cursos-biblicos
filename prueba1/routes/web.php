<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\Web\DashboardController;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

use App\Http\Controllers\Web\UsuarioController;
use App\Http\Controllers\Web\RolController;
use App\Http\Controllers\Web\AsignarRolController;
use App\Http\Controllers\Web\MiembroController;
use App\Http\Controllers\Web\MinisterioController;
use App\Http\Controllers\Web\DocenteController;
use App\Http\Controllers\Web\ActividadController;
use App\Http\Controllers\Web\SeguimientoActividadController;
use App\Http\Controllers\Web\CursoController;
use App\Http\Controllers\Web\SesionController;
use App\Http\Controllers\Web\IngresoController;
use App\Http\Controllers\Web\ContratoController;
use App\Http\Controllers\Web\EgresoController;
use App\Http\Controllers\Web\CultoController;
use App\Http\Controllers\Web\CargoController;
use App\Http\Controllers\Web\LiderIglesiaController;
use App\Http\Controllers\Web\InscripcionController;
use App\Http\Controllers\Web\AsistenciaController;
use App\Http\Controllers\Web\ReporteController;

// Solo Administrador
Route::middleware(['auth','rol:Administrador'])->group(function () {
    Route::resource('usuarios', UsuarioController::class);
    Route::resource('roles', RolController::class);
    Route::resource('asignar-roles', AsignarRolController::class)->only(['index', 'edit', 'update']);
    Route::patch('usuarios/{usuario}/roles/{rol}/estado',
        [UsuarioController::class, 'cambiarEstadoRol'])
        ->name('usuarios.roles.estado');
});

// Administrador o Secretario
Route::middleware(['auth','rol:Administrador,Secretario'])->group(function () {
    Route::resource('miembros', MiembroController::class);
    Route::resource('ministerios', MinisterioController::class);
    Route::resource('actividades', ActividadController::class);
    Route::resource('seguimientos', SeguimientoActividadController::class);
});

// Administrador o Tesorero
Route::middleware(['auth','rol:Administrador,Tesorero'])->group(function () {
    Route::resource('cultos', CultoController::class);
    Route::resource('ingresos', IngresoController::class);
    Route::resource('egresos', EgresoController::class);
    Route::resource('contratos', ContratoController::class);
});

// Todos los autenticados
Route::middleware(['auth'])->group(function () {
    Route::resource('cursos', CursoController::class);
    Route::resource('sesiones', SesionController::class);
    Route::resource('docentes', DocenteController::class);
    Route::resource('cargos', CargoController::class);
    Route::resource('lideres', LiderIglesiaController::class);
    Route::get('/biblia', [App\Http\Controllers\BibliaDigitalController::class, 'index'])->name('biblia.index');
    Route::get('/biblia/buscar/versiculos', [App\Http\Controllers\BibliaDigitalController::class, 'buscar'])->name('biblia.buscar');
});

// Inscripciones
Route::middleware(['auth'])->group(function () {
    Route::get('inscripciones', [InscripcionController::class, 'index'])->name('inscripciones.index');
    Route::get('inscripciones/{curso}/gestionar', [InscripcionController::class, 'gestion'])->name('inscripciones.gestion');
    Route::post('cursos/{curso}/inscripciones', [InscripcionController::class, 'store'])->name('inscripciones.store');
    Route::get('inscripciones/{inscripcion}/edit', [InscripcionController::class, 'edit'])->name('inscripciones.edit');
    Route::put('inscripciones/{inscripcion}', [InscripcionController::class, 'update'])->name('inscripciones.update');
    Route::delete('inscripciones/{inscripcion}', [InscripcionController::class, 'destroy'])->name('inscripciones.destroy');
    Route::post('inscripciones/{id}/restore', [InscripcionController::class, 'restore'])->name('inscripciones.restore');
});

// Asistencia
Route::middleware(['auth'])->group(function () {
    Route::get('asistencia', [AsistenciaController::class, 'index'])->name('asistencia.index');
    Route::get('asistencia/registrar', [AsistenciaController::class, 'registrar'])->name('asistencia.registrar');
    Route::post('asistencia/registrar/{sesion}', [AsistenciaController::class, 'store'])->name('asistencia.store');
});

// Reportes
Route::middleware(['auth'])->group(function () {
    Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('reportes/miembros', [ReporteController::class, 'miembros'])->name('reportes.miembros');
    Route::get('reportes/asistencia', [ReporteController::class, 'asistencia'])->name('reportes.asistencia');
    Route::get('reportes/financiero', [ReporteController::class, 'financiero'])->name('reportes.financiero');
});

require __DIR__.'/auth.php';