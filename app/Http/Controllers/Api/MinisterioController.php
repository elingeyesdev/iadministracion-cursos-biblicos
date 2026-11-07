<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ministerio;
use App\Rules\SoloLetras;
use App\Support\VinculoPivote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MinisterioController extends Controller
{
    public function index(): JsonResponse
    {
        $ministerios = Ministerio::with(['lider.cargo', 'miembros', 'actividades'])->get();

        return response()->json($ministerios, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->reglas());

        $ministerio = DB::transaction(function () use ($validated) {
            $miembros = Arr::pull($validated, 'miembros', []);

            $ministerio = Ministerio::create($validated);

            $this->sincronizarMiembros($ministerio, $miembros);

            return $ministerio;
        });

        return response()->json($ministerio->load(['lider.cargo', 'miembros']), 201);
    }

    public function show(string $id): JsonResponse
    {
        $ministerio = Ministerio::with(['lider.cargo', 'miembros', 'actividades'])
            ->findOrFail($id);

        return response()->json($ministerio, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $ministerio = Ministerio::findOrFail($id);

        $validated = $request->validate($this->reglas(parcial: true));

        DB::transaction(function () use ($validated, $request, $ministerio) {
            $miembros = Arr::pull($validated, 'miembros', []);

            $ministerio->update($validated);

            if ($request->has('miembros') || $request->boolean('miembros_presente')) {
                $this->sincronizarMiembros($ministerio, $miembros);
            }
        });

        return response()->json($ministerio->load(['lider.cargo', 'miembros']), 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $ministerio = Ministerio::findOrFail($id);
        $ministerio->delete();

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
            'descripcion' => ['nullable', 'string'],
            'id_lider_iglesia' => [
                'nullable',
                Rule::exists('lideres_iglesia', 'id')->whereNull('deleted_at'),
            ],
            'miembros' => ['nullable', 'array'],
            'miembros.*' => [
                'integer',
                'distinct',
                Rule::exists('miembros', 'id')->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * La composición vive solo en el pivote; se conserva `fecha_inicio`
     * cuando el vínculo ya existía.
     *
     * @param  array<int, int|string>  $miembros
     */
    private function sincronizarMiembros(Ministerio $ministerio, array $miembros): void
    {
        VinculoPivote::sincronizar(
            $ministerio->miembros(),
            $miembros,
            [
                'fecha_inicio' => now()->toDateString(),
                'fecha_fin' => null,
            ],
            ['fecha_inicio']
        );
    }
}
