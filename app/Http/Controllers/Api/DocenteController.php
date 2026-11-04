<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DocenteController extends Controller
{
    public function index(): JsonResponse
    {
        $docentes = Docente::with(['miembro', 'cursos'])->get();
        return response()->json($docentes, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_miembro' => 'required|exists:miembros,id',
            'especialidad' => 'nullable|string|max:150',
            'titulo' => 'nullable|string|max:150',
            'activo' => 'nullable|boolean',
        ]);

        $docente = Docente::create($validated);
        return response()->json($docente, 201);
    }

    public function show(string $id): JsonResponse
    {
        $docente = Docente::with(['miembro', 'cursos'])->findOrFail($id);
        return response()->json($docente, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $docente = Docente::findOrFail($id);

        $validated = $request->validate([
            'id_miembro' => 'sometimes|exists:miembros,id',
            'especialidad' => 'nullable|string|max:150',
            'titulo' => 'nullable|string|max:150',
            'activo' => 'nullable|boolean',
        ]);

        $docente->update($validated);
        return response()->json($docente, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $docente = Docente::findOrFail($id);
        $docente->delete();
        return response()->json('Eliminado', 200);
    }
}
