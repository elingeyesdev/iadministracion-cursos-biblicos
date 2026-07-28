<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Culto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CultoController extends Controller
{
    public function index(): JsonResponse
    {
        $cultos = Culto::withSum('ingresos', 'monto_total')->get();
        return response()->json($cultos, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre'     => 'required|string|max:150',
            'dia_semana' => 'required|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'hora'       => 'required',
            'descripcion'=> 'nullable|string',
        ]);

        $culto = Culto::create($validated);
        return response()->json($culto, 201);
    }

    public function show(string $id): JsonResponse
    {
        $culto = Culto::with('ingresos')->findOrFail($id);
        return response()->json($culto, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $culto = Culto::findOrFail($id);

        $validated = $request->validate([
            'nombre'     => 'sometimes|required|string|max:150',
            'dia_semana' => 'sometimes|required|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'hora'       => 'sometimes|required',
            'descripcion'=> 'nullable|string',
        ]);

        $culto->update($validated);
        return response()->json($culto, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $culto = Culto::findOrFail($id);
        if ($culto->ingresos()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar: el culto tiene ingresos registrados.'
            ], 422);
        }
        $culto->delete();
        return response()->json('Eliminado', 200);
    }
}
