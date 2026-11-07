<?php

namespace App\Http\Controllers\Api;

use App\Enums\EstadoActividad;
use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Rules\SoloLetras;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActividadController extends Controller
{
    public function index(): JsonResponse
    {
        $actividades = Actividad::with(['ministerio', 'seguimientos'])->get();

        return response()->json($actividades, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->reglas());

        $actividad = Actividad::create($validated);

        return response()->json($actividad, 201);
    }

    public function show(string $id): JsonResponse
    {
        $actividad = Actividad::with(['ministerio', 'seguimientos'])->findOrFail($id);

        return response()->json($actividad, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $actividad = Actividad::findOrFail($id);

        $validated = $request->validate($this->reglas(parcial: true));

        $actividad->update($validated);

        return response()->json($actividad, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $actividad = Actividad::findOrFail($id);
        $actividad->delete();

        return response()->json('Eliminado', 200);
    }

    /**
     * @return array<string, mixed>
     */
    private function reglas(bool $parcial = false): array
    {
        $obligatorio = $parcial ? 'sometimes' : 'required';

        return [
            'id_ministerio' => [$obligatorio, 'exists:ministerios,id'],
            'nombre' => [$obligatorio, 'string', 'max:255', new SoloLetras],
            'fecha' => [$obligatorio, 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha'],
            'presupuesto' => ['nullable', 'numeric', 'min:0'],
            'costo_real' => ['nullable', 'numeric', 'min:0'],
            'lugar' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['nullable', Rule::enum(EstadoActividad::class)],
        ];
    }
}
