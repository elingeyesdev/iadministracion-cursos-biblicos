<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Egreso;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EgresoController extends Controller
{
    public function index(): JsonResponse
    {
        $egresos = Egreso::with(['ministerio', 'contrato'])->get();
        return response()->json($egresos, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_ministerio' => 'nullable|exists:ministerios,id',
            'id_contrato' => 'nullable|exists:contratos,id',
            'tipo_egreso' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'responsable' => 'nullable|string|max:100',
            'comprobante' => 'nullable|string|max:200',
        ]);

        $egreso = Egreso::create($validated);
        return response()->json($egreso, 201);
    }

    public function show(string $id): JsonResponse
    {
        $egreso = Egreso::with(['ministerio', 'contrato'])->findOrFail($id);
        return response()->json($egreso, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $egreso = Egreso::findOrFail($id);

        $validated = $request->validate([
            'id_ministerio' => 'nullable|exists:ministerios,id',
            'id_contrato' => 'nullable|exists:contratos,id',
            'tipo_egreso' => 'sometimes|string|max:100',
            'descripcion' => 'nullable|string',
            'monto' => 'sometimes|numeric|min:0',
            'fecha' => 'sometimes|date',
            'responsable' => 'nullable|string|max:100',
            'comprobante' => 'nullable|string|max:200',
        ]);

        $egreso->update($validated);
        return response()->json($egreso, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $egreso = Egreso::findOrFail($id);
        $egreso->delete();
        return response()->json('Eliminado', 200);
    }
}
