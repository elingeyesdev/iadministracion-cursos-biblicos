<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ministerio;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MinisterioController extends Controller
{
    public function index(): JsonResponse
    {
        $ministerios = Ministerio::with(['lider', 'actividades'])->get();
        return response()->json($ministerios, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'id_lider' => 'nullable|exists:miembros,id',
        ]);

        $ministerio = Ministerio::create($validated);
        return response()->json($ministerio, 201);
    }

    public function show(string $id): JsonResponse
    {
        $ministerio = Ministerio::with(['lider', 'actividades'])->findOrFail($id);
        return response()->json($ministerio, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $ministerio = Ministerio::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:100',
            'descripcion' => 'nullable|string',
            'id_lider' => 'nullable|exists:miembros,id',
        ]);

        $ministerio->update($validated);
        return response()->json($ministerio, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $ministerio = Ministerio::findOrFail($id);
        $ministerio->delete();
        return response()->json('Eliminado', 200);
    }
}
