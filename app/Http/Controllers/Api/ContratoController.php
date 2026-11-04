<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ContratoController extends Controller
{
    public function index(): JsonResponse
    {
        $contratos = Contrato::with(['miembro', 'egresos'])->get();
        return response()->json($contratos, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_miembro' => 'required|exists:miembros,id',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date',
            'monto' => 'nullable|numeric|min:0',
            'estado' => 'nullable|in:activo,finalizado,cancelado',
        ]);

        $contrato = Contrato::create($validated);
        return response()->json($contrato, 201);
    }

    public function show(string $id): JsonResponse
    {
        $contrato = Contrato::with(['miembro', 'egresos'])->findOrFail($id);
        return response()->json($contrato, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $contrato = Contrato::findOrFail($id);

        $validated = $request->validate([
            'id_miembro' => 'sometimes|exists:miembros,id',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'nullable|date',
            'monto' => 'nullable|numeric|min:0',
            'estado' => 'sometimes|in:activo,finalizado,cancelado',
        ]);

        $contrato->update($validated);
        return response()->json($contrato, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $contrato = Contrato::findOrFail($id);
        $contrato->delete();
        return response()->json('Eliminado', 200);
    }
}
