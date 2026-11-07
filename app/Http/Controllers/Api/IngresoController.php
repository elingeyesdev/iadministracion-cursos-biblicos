<?php

namespace App\Http\Controllers\Api;

use App\Enums\MetodoPago;
use App\Enums\TipoIngreso;
use App\Http\Controllers\Controller;
use App\Models\CategoriaFinanciera;
use App\Models\Ingreso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IngresoController extends Controller
{
    public function index(): JsonResponse
    {
        $ingresos = Ingreso::with(['actividad', 'culto', 'categoria', 'pagoCurso'])->get();

        return response()->json($ingresos, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->reglas());

        $ingreso = Ingreso::create($this->conCategoria($validated));

        return response()->json($ingreso, 201);
    }

    public function show(string $id): JsonResponse
    {
        $ingreso = Ingreso::with(['actividad', 'culto', 'categoria', 'pagoCurso'])
            ->findOrFail($id);

        return response()->json($ingreso, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $ingreso = Ingreso::findOrFail($id);

        $validated = $request->validate($this->reglas(parcial: true));

        $ingreso->update($this->conCategoria($validated));

        return response()->json($ingreso, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $ingreso = Ingreso::findOrFail($id);
        $ingreso->delete();

        return response()->json('Eliminado', 200);
    }

    /**
     * @return array<string, mixed>
     */
    private function reglas(bool $parcial = false): array
    {
        $obligatorio = $parcial ? 'sometimes' : 'required';

        return [
            'id_actividad' => ['nullable', 'exists:actividades,id'],
            'id_culto' => ['nullable', 'exists:cultos,id'],
            'pago_curso_id' => ['nullable', 'exists:pago_cursos,id'],
            'categoria_id' => ['nullable', 'exists:categorias_financieras,id'],
            'monto_total' => ['nullable', 'required_without:pago_curso_id', 'numeric', 'min:0'],
            'fecha' => ['nullable', 'required_without:pago_curso_id', 'date'],
            'tipo' => [$obligatorio, Rule::enum(TipoIngreso::class)],
            'metodo_pago' => ['nullable', 'required_without:pago_curso_id', Rule::enum(MetodoPago::class)],
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
        if (empty($datos['categoria_id']) && ! empty($datos['tipo'])) {
            $tipo = $datos['tipo'] instanceof TipoIngreso ? $datos['tipo']->value : $datos['tipo'];

            $datos['categoria_id'] = CategoriaFinanciera::idPara(
                $tipo,
                CategoriaFinanciera::NATURALEZA_INGRESO
            );
        }

        return $datos;
    }
}
