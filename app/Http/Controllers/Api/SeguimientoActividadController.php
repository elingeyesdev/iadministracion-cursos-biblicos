<?php

namespace App\Http\Controllers\Api;

use App\Enums\EstadoSeguimiento;
use App\Http\Controllers\Controller;
use App\Models\SeguimientoActividad;
use App\Rules\SoloLetras;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SeguimientoActividadController extends Controller
{
    public function index(): JsonResponse
    {
        $seguimientos = SeguimientoActividad::with('actividad')->get();

        return response()->json($seguimientos, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->reglas());

        $seguimiento = SeguimientoActividad::create($validated);

        return response()->json($seguimiento, 201);
    }

    public function show(string $id): JsonResponse
    {
        $seguimiento = SeguimientoActividad::with('actividad')->findOrFail($id);

        return response()->json($seguimiento, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $seguimiento = SeguimientoActividad::findOrFail($id);

        $validated = $request->validate($this->reglas(parcial: true));

        $seguimiento->update($validated);

        return response()->json($seguimiento, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $seguimiento = SeguimientoActividad::findOrFail($id);
        $seguimiento->delete();

        return response()->json('Eliminado', 200);
    }

    /**
     * @return array<string, mixed>
     */
    private function reglas(bool $parcial = false): array
    {
        $obligatorio = $parcial ? 'sometimes' : 'required';

        return [
            'id_actividad' => [$obligatorio, 'exists:actividades,id'],
            'fecha_registro' => [$obligatorio, 'date'],
            'observacion' => ['nullable', 'string'],
            'responsable' => [$obligatorio, 'string', 'max:255', new SoloLetras],
            'porcentaje_avance' => [$obligatorio, 'numeric', 'min:0', 'max:100'],
            'estado' => [$obligatorio, Rule::enum(EstadoSeguimiento::class)],
        ];
    }
}
