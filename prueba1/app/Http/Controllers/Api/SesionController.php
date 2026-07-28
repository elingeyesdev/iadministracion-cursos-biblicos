<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sesion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SesionController extends Controller
{
    public function index(): JsonResponse
    {
        $sesiones = Sesion::with(['curso', 'miembros'])->get();
        return response()->json($sesiones, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_curso' => 'required|exists:cursos,id',
            'fecha' => 'required|date',
            'hora' => 'nullable',
            'tema' => 'nullable|string|max:200',
            'observacion' => 'nullable|string',
        ]);

        $sesion = Sesion::create($validated);
        return response()->json($sesion, 201);
    }

    public function show(string $id): JsonResponse
    {
        $sesion = Sesion::with(['curso', 'miembros'])->findOrFail($id);
        return response()->json($sesion, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $sesion = Sesion::findOrFail($id);

        $validated = $request->validate([
            'id_curso' => 'sometimes|exists:cursos,id',
            'fecha' => 'sometimes|date',
            'hora' => 'nullable',
            'tema' => 'nullable|string|max:200',
            'observacion' => 'nullable|string',
        ]);

        $sesion->update($validated);
        return response()->json($sesion, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $sesion = Sesion::findOrFail($id);
        $sesion->delete();
        return response()->json('Eliminado', 200);
    }
}
