<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\authController;
use App\Http\Controllers\API\MiembroController;
use App\Http\Controllers\API\MinisterioController;
use App\Http\Controllers\API\DocenteController;
use App\Http\Controllers\API\ActividadController;
use App\Http\Controllers\API\SeguimientoActividadController;
use App\Http\Controllers\API\CursoController;
use App\Http\Controllers\API\SesionController;
use App\Http\Controllers\API\IngresoController;
use App\Http\Controllers\API\ContratoController;
use App\Http\Controllers\API\EgresoController;
use App\Http\Controllers\API\CultoController;

Route::post('/login', [authController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Illuminate\Http\Request $request) {
    return response()->json($request->user()->load('roles'), 200);
});

Route::name('api.')->group(function () {
    Route::apiResource('miembros', MiembroController::class);
    Route::apiResource('ministerios', MinisterioController::class);
    Route::apiResource('docentes', DocenteController::class);
    Route::apiResource('actividades', ActividadController::class);
    Route::apiResource('seguimientos', SeguimientoActividadController::class);
    Route::apiResource('cursos', CursoController::class);
    Route::apiResource('sesiones', SesionController::class);
    Route::apiResource('ingresos', IngresoController::class);
    Route::apiResource('contratos', ContratoController::class);
    Route::apiResource('egresos', EgresoController::class);
    Route::apiResource('cultos', CultoController::class);
});

use App\Http\Controllers\BibliaDigitalController;

Route::prefix('biblia')->group(function () {
    Route::get('/books', [BibliaDigitalController::class, 'getBooks']);
    Route::get('/book/{bookId}', [BibliaDigitalController::class, 'getBook']);
    Route::get('/verse/{book}/{chapter}/{verse}', [BibliaDigitalController::class, 'getVerse']);
    Route::get('/chapter/{book}/{chapter}', [BibliaDigitalController::class, 'getChapter']);
    Route::get('/search', [BibliaDigitalController::class, 'apiSearch']);
    Route::post('/cache/clear', [BibliaDigitalController::class, 'clearCache']);
});
