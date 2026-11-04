<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SeguimientoActividad;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SeguimientoActividadController extends Controller
{
    public function index(): JsonResponse
    {
        $seguimientos = SeguimientoActividad::all();
        return response()->json($seguimientos, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_actividad' => 'required|exists:actividades,id',
            'fecha_registro' => 'required|date',
            'estado' => 'required|string|max:50',
            'observacion' => 'nullable|string',
            'responsable' => 'nullable|string|max:100',
            'porcentaje_avance' => 'nullable|numeric|min:0|max:100',
        ]);

        $seguimiento = SeguimientoActividad::create($validated);
        return response()->json($seguimiento, 201);
    }

    public function show(string $id): JsonResponse
    {
        $seguimiento = SeguimientoActividad::findOrFail($id);
        return response()->json($seguimiento, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $seguimiento = SeguimientoActividad::findOrFail($id);

        $validated = $request->validate([
            'id_actividad' => 'sometimes|exists:actividades,id',
            'fecha_registro' => 'sometimes|date',
            'estado' => 'sometimes|string|max:50',
            'observacion' => 'nullable|string',
            'responsable' => 'nullable|string|max:100',
            'porcentaje_avance' => 'nullable|numeric|min:0|max:100',
        ]);

        $seguimiento->update($validated);
        return response()->json($seguimiento, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $seguimiento = SeguimientoActividad::findOrFail($id);
        $seguimiento->delete();
        return response()->json('Eliminado', 200);
    }
}
