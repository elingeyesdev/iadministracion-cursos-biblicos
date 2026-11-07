<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sesion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SesionController extends Controller
{
    public function index(): JsonResponse
    {
        $sesiones = Sesion::with(['curso', 'docente.miembro'])->get();

        return response()->json($sesiones, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->reglas());

        $sesion = Sesion::create($validated);

        return response()->json($sesion, 201);
    }

    public function show(string $id): JsonResponse
    {
        $sesion = Sesion::with(['curso', 'docente.miembro', 'miembrosAsistencia'])
            ->findOrFail($id);

        return response()->json($sesion, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $sesion = Sesion::findOrFail($id);

        $validated = $request->validate($this->reglas(parcial: true));

        $sesion->update($validated);

        return response()->json($sesion, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $sesion = Sesion::findOrFail($id);
        $sesion->delete();

        return response()->json('Eliminado', 200);
    }

    /**
     * @return array<string, mixed>
     */
    private function reglas(bool $parcial = false): array
    {
        $obligatorio = $parcial ? 'sometimes' : 'required';

        return [
            'id_curso' => [$obligatorio, 'exists:cursos,id'],
            'id_docente' => ['nullable', 'exists:docentes,id'],
            'fecha' => [$obligatorio, 'date'],
            'hora' => [$obligatorio, 'date_format:H:i'],
            'tema' => [$obligatorio, 'string', 'max:255'],
            'observacion' => ['nullable', 'string'],
        ];
    }
}
