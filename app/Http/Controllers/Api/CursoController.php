<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CursoController extends Controller
{
    public function index(): JsonResponse
    {
        $cursos = Curso::with(['sesiones', 'docentes'])->get();
        return response()->json($cursos, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'f_inicio' => 'required|date',
            'f_fin' => 'nullable|date',
            'cupo_max' => 'nullable|integer',
            'tiene_pago' => 'nullable|boolean',
            'monto_inscripcion' => 'nullable|numeric|min:0',
        ]);

        $curso = Curso::create($validated);
        return response()->json($curso, 201);
    }

    public function show(string $id): JsonResponse
    {
        $curso = Curso::with(['sesiones', 'docentes'])->findOrFail($id);
        return response()->json($curso, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $curso = Curso::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:150',
            'f_inicio' => 'sometimes|date',
            'f_fin' => 'nullable|date',
            'cupo_max' => 'nullable|integer',
            'tiene_pago' => 'nullable|boolean',
            'monto_inscripcion' => 'nullable|numeric|min:0',
        ]);

        $curso->update($validated);
        return response()->json($curso, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $curso = Curso::findOrFail($id);
        $curso->delete();
        return response()->json('Eliminado', 200);
    }
}
