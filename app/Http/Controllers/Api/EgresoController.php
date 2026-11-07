<?php

namespace App\Http\Controllers\Api;

use App\Enums\TipoEgreso;
use App\Http\Controllers\Controller;
use App\Models\CategoriaFinanciera;
use App\Models\Egreso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EgresoController extends Controller
{
    public function index(): JsonResponse
    {
        $egresos = Egreso::with(['contrato', 'lider', 'categoria'])->get();

        return response()->json($egresos, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->reglas($request));

        $egreso = Egreso::create($this->conCategoria($validated));

        return response()->json($egreso, 201);
    }

    public function show(string $id): JsonResponse
    {
        $egreso = Egreso::with(['contrato', 'lider', 'categoria'])->findOrFail($id);

        return response()->json($egreso, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $egreso = Egreso::findOrFail($id);

        $validated = $request->validate($this->reglas($request, parcial: true));

        $egreso->update($this->conCategoria($validated));

        return response()->json($egreso, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $egreso = Egreso::findOrFail($id);
        $egreso->delete();

        return response()->json('Eliminado', 200);
    }

    /**
     * @return array<string, mixed>
     */
    private function reglas(Request $request, bool $parcial = false): array
    {
        $obligatorio = $parcial ? 'sometimes' : 'required';

        return [
            'id_contrato' => [
                'nullable',
                'exists:contratos,id',
                // Un egreso se paga por contrato o directamente a un líder,
                // nunca por ambas vías (evita duplicar el gasto en los KPI).
                function ($attribute, $value, $fail) use ($request) {
                    if ($value !== null && $request->filled('id_lider_iglesia')) {
                        $fail('Un egreso no puede asociarse a un contrato y a un líder a la vez.');
                    }
                },
            ],
            'id_lider_iglesia' => ['nullable', 'exists:lideres_iglesia,id'],
            'categoria_id' => ['nullable', 'exists:categorias_financieras,id'],
            'tipo_egreso' => [$obligatorio, Rule::enum(TipoEgreso::class)],
            'monto' => [$obligatorio, 'numeric', 'min:0'],
            'fecha' => [$obligatorio, 'date'],
            'descripcion' => ['nullable', 'string'],
            'responsable' => [$obligatorio, 'string', 'max:255'],
            'comprobante' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Completa `categoria_id` a partir del tipo cuando no se envía.
     *
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function conCategoria(array $datos): array
    {
        if (empty($datos['categoria_id']) && ! empty($datos['tipo_egreso'])) {
            $tipo = $datos['tipo_egreso'] instanceof TipoEgreso
                ? $datos['tipo_egreso']->value
                : $datos['tipo_egreso'];

            $datos['categoria_id'] = CategoriaFinanciera::idPara(
                $tipo,
                CategoriaFinanciera::NATURALEZA_EGRESO
            );
        }

        return $datos;
    }
}
