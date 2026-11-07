<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Rules\SoloLetras;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function index(): JsonResponse
    {
        $cursos = Curso::with(['sesiones', 'inscripciones'])->get();

        return response()->json($cursos, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->reglas());

        $validated['dias_semana'] = $validated['dias_semana'] ?? [];

        $curso = Curso::create($validated);

        return response()->json($curso, 201);
    }

    public function show(string $id): JsonResponse
    {
        $curso = Curso::with(['sesiones.docente.miembro', 'inscripciones.miembro'])
            ->findOrFail($id);

        return response()->json($curso, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $curso = Curso::findOrFail($id);

        $validated = $request->validate($this->reglas(parcial: true));

        if (array_key_exists('dias_semana', $validated)) {
            $validated['dias_semana'] = $validated['dias_semana'] ?? [];
        }

        $curso->update($validated);

        return response()->json($curso, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $curso = Curso::findOrFail($id);
        $curso->delete();

        return response()->json('Eliminado', 200);
    }

    /**
     * @return array<string, mixed>
     */
    private function reglas(bool $parcial = false): array
    {
        $obligatorio = $parcial ? 'sometimes' : 'required';

        return [
            'nombre' => [$obligatorio, 'string', 'max:255', new SoloLetras],
            'f_inicio' => [$obligatorio, 'date'],
            'f_fin' => ['nullable', 'date', 'after_or_equal:f_inicio'],
            'cupo_max' => [$obligatorio, 'integer', 'min:1'],
            'dias_semana' => ['nullable', 'array'],
            'dias_semana.*' => ['integer', 'between:1,7'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'required_with:hora_inicio', 'date_format:H:i', 'after:hora_inicio'],
            'tiene_pago' => ['nullable', 'boolean'],
            'monto_inscripcion' => [$obligatorio, 'numeric', 'min:0'],
        ];
    }
}
