<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\UsuarioController;
use App\Http\Controllers\Web\RolController;
use App\Http\Controllers\Web\PermisoController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// El perfil queda accesible con solo estar autenticado: así un usuario que aún
// no verifica su correo puede igualmente gestionar su cuenta o cerrar sesión.
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/**
 * Registra un recurso protegido por permisos:
 *   - index / show        → permiso <modulo>.ver
 *   - create/store/...    → permiso <modulo>.gestionar
 */
$recurso = function (string $uri, string $controlador, string $modulo): void {
    Route::resource($uri, $controlador)
        ->middlewareFor(['index', 'show'], "permiso:{$modulo}.ver")
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], "permiso:{$modulo}.gestionar");
};

// Todas las rutas internas exigen el correo verificado (middleware 'verified').
Route::middleware(['auth', 'verified'])->group(function () use ($recurso) {

    // ── Acceso y seguridad ─────────────────────────────────
    $recurso('usuarios', UsuarioController::class, 'usuarios');
    $recurso('roles', RolController::class, 'roles');

    Route::get('permisos', [PermisoController::class, 'index'])
        ->middleware('permiso:permisos.ver')
        ->name('permisos.index');
    Route::put('permisos/{rol}', [PermisoController::class, 'update'])
        ->middleware('permiso:permisos.gestionar')
        ->name('permisos.roles.update');

    Route::resource('asignar-roles', AsignarRolController::class)
        ->only(['index', 'edit', 'update'])
        ->middlewareFor(['index'], 'permiso:asignar-roles.ver')
        ->middlewareFor(['edit', 'update'], 'permiso:asignar-roles.gestionar');

    Route::patch('usuarios/{usuario}/roles/{rol}/estado',
        [UsuarioController::class, 'cambiarEstadoRol'])
        ->middleware('permiso:usuarios.gestionar')
        ->name('usuarios.roles.estado');

    // ── Personas y organización ────────────────────────────
    $recurso('miembros', MiembroController::class, 'miembros');
    $recurso('ministerios', MinisterioController::class, 'ministerios');
    $recurso('actividades', ActividadController::class, 'actividades');
    $recurso('seguimientos', SeguimientoActividadController::class, 'seguimientos');
    $recurso('docentes', DocenteController::class, 'docentes');
    $recurso('cargos', CargoController::class, 'cargos');
    $recurso('lideres', LiderIglesiaController::class, 'lideres');

    // ── Finanzas ───────────────────────────────────────────
    $recurso('cultos', CultoController::class, 'cultos');
    $recurso('ingresos', IngresoController::class, 'ingresos');
    $recurso('egresos', EgresoController::class, 'egresos');
    $recurso('contratos', ContratoController::class, 'contratos');

    // ── Académico ──────────────────────────────────────────
    $recurso('cursos', CursoController::class, 'cursos');
    $recurso('sesiones', SesionController::class, 'sesiones');

    // Inscripciones
    Route::get('inscripciones', [InscripcionController::class, 'index'])
        ->middleware('permiso:inscripciones.ver')->name('inscripciones.index');
    Route::get('inscripciones/{curso}/gestionar', [InscripcionController::class, 'gestion'])
        ->middleware('permiso:inscripciones.ver')->name('inscripciones.gestion');
    Route::post('cursos/{curso}/inscripciones', [InscripcionController::class, 'store'])
        ->middleware('permiso:inscripciones.gestionar')->name('inscripciones.store');
    Route::get('inscripciones/{inscripcion}/edit', [InscripcionController::class, 'edit'])
        ->middleware('permiso:inscripciones.gestionar')->name('inscripciones.edit');
    Route::put('inscripciones/{inscripcion}', [InscripcionController::class, 'update'])
        ->middleware('permiso:inscripciones.gestionar')->name('inscripciones.update');
    Route::delete('inscripciones/{inscripcion}', [InscripcionController::class, 'destroy'])
        ->middleware('permiso:inscripciones.gestionar')->name('inscripciones.destroy');
    Route::post('inscripciones/{id}/restore', [InscripcionController::class, 'restore'])
        ->middleware('permiso:inscripciones.gestionar')->name('inscripciones.restore');

    // Asistencia
    Route::get('asistencia', [AsistenciaController::class, 'index'])
        ->middleware('permiso:asistencia.ver')->name('asistencia.index');
    Route::get('asistencia/registrar', [AsistenciaController::class, 'registrar'])
        ->middleware('permiso:asistencia.ver')->name('asistencia.registrar');
    Route::post('asistencia/registrar/{sesion}', [AsistenciaController::class, 'store'])
        ->middleware('permiso:asistencia.gestionar')->name('asistencia.store');

    // ── Reportes ───────────────────────────────────────────
    Route::get('reportes', [ReporteController::class, 'index'])
        ->middleware('permiso:reportes.ver')->name('reportes.index');
    Route::get('reportes/miembros', [ReporteController::class, 'miembros'])
        ->middleware('permiso:reportes.ver')->name('reportes.miembros');
    Route::get('reportes/asistencia', [ReporteController::class, 'asistencia'])
        ->middleware('permiso:reportes.ver')->name('reportes.asistencia');
    Route::get('reportes/financiero', [ReporteController::class, 'financiero'])
        ->middleware('permiso:reportes.ver')->name('reportes.financiero');

    // ── Biblia digital ─────────────────────────────────────
    Route::get('/biblia', [App\Http\Controllers\BibliaDigitalController::class, 'index'])
        ->middleware('permiso:biblia.ver')->name('biblia.index');
    Route::get('/biblia/buscar/versiculos', [App\Http\Controllers\BibliaDigitalController::class, 'buscar'])
        ->middleware('permiso:biblia.ver')->name('biblia.buscar');
});

require __DIR__.'/auth.php';
