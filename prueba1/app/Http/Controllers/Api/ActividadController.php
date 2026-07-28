<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ActividadController extends Controller
{
    public function index(): JsonResponse
    {
        $actividades = Actividad::with(['ministerio', 'seguimientos'])->get();
        return response()->json($actividades, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_ministerio' => 'required|exists:ministerios,id',
            'nombre' => 'required|string|max:150',
            'fecha' => 'required|date',
            'lugar' => 'nullable|string|max:200',
            'estado' => 'nullable|in:planificada,en_curso,completada,cancelada',
            'descripcion' => 'nullable|string',
        ]);

        $actividad = Actividad::create($validated);
        return response()->json($actividad, 201);
    }

    public function show(string $id): JsonResponse
    {
        $actividad = Actividad::with(['ministerio', 'seguimientos'])->findOrFail($id);
        return response()->json($actividad, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $actividad = Actividad::findOrFail($id);

        $validated = $request->validate([
            'id_ministerio' => 'sometimes|exists:ministerios,id',
            'nombre' => 'sometimes|string|max:150',
            'fecha' => 'sometimes|date',
            'lugar' => 'nullable|string|max:200',
            'estado' => 'sometimes|in:planificada,en_curso,completada,cancelada',
            'descripcion' => 'nullable|string',
        ]);

        $actividad->update($validated);
        return response()->json($actividad, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $actividad = Actividad::findOrFail($id);
        $actividad->delete();
        return response()->json('Eliminado', 200);
    }
}
