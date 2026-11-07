<?php

namespace App\Http\Controllers\Api;

use App\Enums\DiaSemana;
use App\Http\Controllers\Controller;
use App\Models\Culto;
use App\Rules\SoloLetras;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CultoController extends Controller
{
    public function index(): JsonResponse
    {
        $cultos = Culto::with('liderIglesia')
            ->withSum('ingresos', 'monto_total')
            ->get();

        return response()->json($cultos, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->reglas());

        $culto = Culto::create($validated);

        return response()->json($culto, 201);
    }

    public function show(string $id): JsonResponse
    {
        $culto = Culto::with(['liderIglesia', 'ingresos'])->findOrFail($id);

        return response()->json($culto, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $culto = Culto::findOrFail($id);

        $validated = $request->validate($this->reglas(parcial: true));

        $culto->update($validated);

        return response()->json($culto, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $culto = Culto::findOrFail($id);

        if ($culto->ingresos()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar: el culto tiene ingresos registrados.',
            ], 422);
        }

        $culto->delete();

        return response()->json('Eliminado', 200);
    }

    /**
     * @return array<string, mixed>
     */
    private function reglas(bool $parcial = false): array
    {
        $obligatorio = $parcial ? 'sometimes' : 'required';

        return [
            'id_lider_iglesia' => [$obligatorio, 'exists:lideres_iglesia,id'],
            'nombre' => [$obligatorio, 'string', 'max:255', new SoloLetras],
            'dia_semana' => [$obligatorio, Rule::enum(DiaSemana::class)],
            'hora' => [$obligatorio, 'date_format:H:i'],
            'descripcion' => ['nullable', 'string'],
        ];
    }
}
