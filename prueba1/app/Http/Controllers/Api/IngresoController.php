<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ingreso;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class IngresoController extends Controller
{
    public function index(): JsonResponse
    {
        $ingresos = Ingreso::with('culto')->get();
        return response()->json($ingresos, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_culto' => 'required|exists:cultos,id_culto',
            'monto_total' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'tipo' => 'nullable|string|max:100',
            'metodo_pago' => 'nullable|string|max:100',
        ]);

        $ingreso = Ingreso::create($validated);
        return response()->json($ingreso, 201);
    }

    public function show(string $id): JsonResponse
    {
        $ingreso = Ingreso::with('culto')->findOrFail($id);
        return response()->json($ingreso, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $ingreso = Ingreso::findOrFail($id);

        $validated = $request->validate([
            'id_culto' => 'sometimes|required|exists:cultos,id_culto',
            'monto_total' => 'sometimes|numeric|min:0',
            'fecha' => 'sometimes|date',
            'tipo' => 'nullable|string|max:100',
            'metodo_pago' => 'nullable|string|max:100',
        ]);

        $ingreso->update($validated);
        return response()->json($ingreso, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $ingreso = Ingreso::findOrFail($id);
        $ingreso->delete();
        return response()->json('Eliminado', 200);
    }
}
