<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Rules\SoloLetras;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocenteController extends Controller
{
    public function index(): JsonResponse
    {
        $docentes = Docente::with(['miembro', 'user', 'sesiones'])->get();

        return response()->json($docentes, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->reglas());

        $existente = Docente::buscarPorClaveUnica(['id_miembro' => $validated['id_miembro']]);

        if ($existente && ! $existente->trashed()) {
            return response()->json([
                'message' => 'El miembro ya está registrado como docente.',
                'errors' => [
                    'id_miembro' => ['El miembro ya está registrado como docente.'],
                ],
            ], 422);
        }

        $docente = Docente::crearORestaurar(
            ['id_miembro' => $validated['id_miembro']],
            [
                'especialidad' => $validated['especialidad'],
                'titulo' => $validated['titulo'] ?? null,
                'activo' => $validated['activo'] ?? true,
            ]
        );

        return response()->json($docente, $existente ? 200 : 201);
    }

    public function show(string $id): JsonResponse
    {
        $docente = Docente::with([
            'miembro',
            'user',
            'sesiones' => fn ($q) => $q->with('curso')->orderByDesc('fecha'),
        ])->findOrFail($id);

        return response()->json($docente, 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $docente = Docente::findOrFail($id);

        $validated = $request->validate($this->reglas($docente->id, parcial: true));

        $docente->update($validated);

        return response()->json($docente, 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $docente = Docente::findOrFail($id);
        $docente->delete();

        return response()->json('Eliminado', 200);
    }

    /**
     * @return array<string, mixed>
     */
    private function reglas(?int $docenteId = null, bool $parcial = false): array
    {
        $obligatorio = $parcial ? 'sometimes' : 'required';

        return [
            'id_miembro' => [
                $obligatorio,
                'exists:miembros,id',
                Rule::unique('docentes', 'id_miembro')
                    ->whereNull('deleted_at')
                    ->ignore($docenteId),
            ],
            'especialidad' => [$obligatorio, 'string', 'max:255', new SoloLetras],
            'titulo' => ['nullable', 'string', 'max:255', new SoloLetras],
            'activo' => ['nullable', 'boolean'],
        ];
    }
}
