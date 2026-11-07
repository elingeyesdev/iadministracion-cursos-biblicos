<?php

use App\Http\Controllers\Api\ActividadController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContratoController;
use App\Http\Controllers\Api\CultoController;
use App\Http\Controllers\Api\CursoController;
use App\Http\Controllers\Api\DocenteController;
use App\Http\Controllers\Api\EgresoController;
use App\Http\Controllers\Api\IngresoController;
use App\Http\Controllers\Api\MiembroController;
use App\Http\Controllers\Api\MinisterioController;
use App\Http\Controllers\Api\SeguimientoActividadController;
use App\Http\Controllers\Api\SesionController;
use App\Http\Controllers\BibliaDigitalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
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

Route::prefix('biblia')->group(function () {
    Route::get('/books', [BibliaDigitalController::class, 'getBooks']);
    Route::get('/book/{bookId}', [BibliaDigitalController::class, 'getBook']);
    Route::get('/verse/{book}/{chapter}/{verse}', [BibliaDigitalController::class, 'getVerse']);
    Route::get('/chapter/{book}/{chapter}', [BibliaDigitalController::class, 'getChapter']);
    Route::get('/search', [BibliaDigitalController::class, 'apiSearch']);
    Route::post('/cache/clear', [BibliaDigitalController::class, 'clearCache']);
});
