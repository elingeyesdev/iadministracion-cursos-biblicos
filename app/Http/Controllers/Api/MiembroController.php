<?php

namespace App\Http\Controllers\Api;

use App\Enums\EstadoMiembro;
use App\Http\Controllers\Controller;
use App\Models\Miembro;
use App\Rules\SoloLetras;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MiembroController extends Controller
{
    public function index(): JsonResponse
    {
        $miembros = Miembro::with(['docente', 'contratos'])->get();

        return response()->json($miembros, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->reglas());

        $miembro = Miembro::create($validated);

        return response()->json($miembro, 201);
    }

    public function show(string $id): JsonResponse
    {
        $miembro = Miembro::with(['docente', 'contratos', 'ministerios', 'cursos'])
            ->findOrFail($id);

        return response()->json($miembro, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $miembro = Miembro::findOrFail($id);

        $validated = $request->validate($this->reglas($miembro->id, parcial: true));

        $miembro->update($validated);

        return response()->json($miembro, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $miembro = Miembro::findOrFail($id);
        $miembro->delete();

        return response()->json('Eliminado', 200);
    }

    /**
     * Reglas compartidas por store() y update().
     *
     * @return array<string, mixed>
     */
    private function reglas(?int $miembroId = null, bool $parcial = false): array
    {
        $obligatorio = $parcial ? 'sometimes' : 'required';

        return [
            'nombre' => [
                $obligatorio,
                'string',
                'max:255',
                new SoloLetras,
                Rule::unique('miembros', 'nombre')->ignore($miembroId),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('miembros', 'email')->ignore($miembroId),
            ],
            'telefono' => ['nullable', 'string', 'max:8', 'regex:/^[0-9 ]*$/'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'f_nacimiento' => ['nullable', 'date', 'before_or_equal:'.now()->subYears(13)->toDateString()],
            'sexo' => ['nullable', 'string', Rule::in(['Masculino', 'Femenino'])],
            'fecha_ingreso' => [$obligatorio, 'date'],
            'estado' => [$obligatorio, Rule::enum(EstadoMiembro::class)],
            'fecha_baja' => ['nullable', 'date'],
            'motivo_baja' => ['nullable', 'string', 'max:255'],
            'fecha_conversion' => ['nullable', 'date'],
            'fecha_bautismo' => ['nullable', 'date'],
        ];
    }
}
