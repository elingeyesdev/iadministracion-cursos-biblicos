<?php

namespace App\Http\Controllers\Api;

use App\Enums\TipoCompensacion;
use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContratoController extends Controller
{
    public function index(): JsonResponse
    {
        $contratos = Contrato::with(['miembro', 'lider.cargo', 'egresos'])->get();

        return response()->json($contratos, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->reglas($request));

        $contrato = Contrato::create($validated);

        return response()->json($contrato, 201);
    }

    public function show(string $id): JsonResponse
    {
        $contrato = Contrato::with(['miembro', 'lider.cargo', 'egresos'])
            ->findOrFail($id);

        return response()->json($contrato, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $contrato = Contrato::findOrFail($id);

        $validated = $request->validate($this->reglas($request, $contrato, parcial: true));

        $contrato->update($validated);

        return response()->json($contrato, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $contrato = Contrato::findOrFail($id);
        $contrato->delete();

        return response()->json('Eliminado', 200);
    }

    /**
     * @return array<string, mixed>
     */
    private function reglas(Request $request, ?Contrato $contrato = null, bool $parcial = false): array
    {
        $obligatorio = $parcial ? 'sometimes' : 'required';

        return [
            'id_miembro' => [
                $obligatorio,
                'exists:miembros,id',
                // Laravel invoca la regla como ($attribute, $value, $fail).
                function ($attribute, $value, $fail) use ($request, $contrato) {
                    if ($value === null) {
                        return;
                    }

                    $fechaInicio = $request->input('fecha_inicio', $contrato?->fecha_inicio);
                    $fechaFin = $request->input('fecha_fin', $contrato?->fecha_fin);

                    if ($fechaInicio === null) {
                        return;
                    }

                    if (Contrato::tieneSolapamiento(
                        (int) $value,
                        (string) $fechaInicio,
                        $fechaFin !== null ? (string) $fechaFin : null,
                        $contrato?->id
                    )) {
                        $fail('El miembro ya tiene un contrato cuyas fechas se solapan con las ingresadas. Cierra o renueva el contrato anterior.');
                    }
                },
            ],
            'id_lider_iglesia' => [$obligatorio, 'exists:lideres_iglesia,id'],
            'tipo_compensacion' => [$obligatorio, Rule::enum(TipoCompensacion::class)],
            'salario' => [
                'nullable',
                'required_if:tipo_compensacion,Salario,Bono',
                'numeric',
                'min:0',
            ],
            'fecha_inicio' => [$obligatorio, 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'activo' => ['nullable', 'boolean'],
        ];
    }
}
