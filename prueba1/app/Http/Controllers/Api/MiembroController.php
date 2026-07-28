<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Miembro;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MiembroController extends Controller
{
    public function index(): JsonResponse
    {
        $miembros = Miembro::with(['docente', 'contratos'])->get();
        return response()->json($miembros, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|unique:miembros,email',
            'telefono' => 'nullable|string|max:20|regex:/^[0-9+\s\-()]+$/',
            'f_nacimiento' => 'nullable|date|before_or_equal:today',
            'estado' => 'nullable|in:activo,inactivo,visita',
            'direccion' => 'nullable|string|max:200',
            'fecha_ingreso' => 'nullable|date|before_or_equal:today',
            'sexo' => 'nullable|in:M,F,otro',
        ], [
            'telefono.regex' => 'El teléfono no puede contener letras.',
            'f_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser posterior a la fecha actual.',
            'fecha_ingreso.before_or_equal' => 'La fecha de ingreso no puede ser posterior a la fecha actual.',
        ]);

        $miembro = Miembro::create($validated);
        return response()->json($miembro, 201);
    }

    public function show(string $id): JsonResponse
    {
        $miembro = Miembro::with(['docente', 'contratos'])->findOrFail($id);
        return response()->json($miembro, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $miembro = Miembro::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:miembros,email,' . $id,
            'telefono' => 'nullable|string|max:20|regex:/^[0-9+\s\-()]+$/',
            'f_nacimiento' => 'nullable|date|before_or_equal:today',
            'estado' => 'sometimes|in:activo,inactivo,visita',
            'direccion' => 'nullable|string|max:200',
            'fecha_ingreso' => 'nullable|date|before_or_equal:today',
            'sexo' => 'nullable|in:M,F,otro',
        ], [
            'telefono.regex' => 'El teléfono no puede contener letras.',
            'f_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser posterior a la fecha actual.',
            'fecha_ingreso.before_or_equal' => 'La fecha de ingreso no puede ser posterior a la fecha actual.',
        ]);

        $miembro->update($validated);
        return response()->json($miembro, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $miembro = Miembro::findOrFail($id);
        $miembro->delete();
        return response()->json('Eliminado', 200);
    }
}
