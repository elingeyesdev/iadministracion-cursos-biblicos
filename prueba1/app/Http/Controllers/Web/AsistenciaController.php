<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\Miembro;
use App\Models\Sesion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsistenciaController extends Controller
{
    public function index(Request $request)
    {
        $asistencias = Asistencia::with(['miembro', 'sesion.curso', 'user'])
            ->join('sesiones', 'asistencias_sesion.sesion_id', '=', 'sesiones.id')
            ->select('asistencias_sesion.*')
            ->when($request->filled('q'), fn ($q) => $q->whereHas('miembro', fn ($m) => $m->where('nombre', 'like', '%' . $request->q . '%')))
            ->when($request->filled('curso_id'), fn ($q) => $q->porCurso($request->curso_id))
            ->when($request->filled('estado'), fn ($q) => $q->where('asistencias_sesion.estado', $request->estado))
            ->when($request->filled('desde'), fn ($q) => $q->where('sesiones.fecha', '>=', $request->desde))
            ->when($request->filled('hasta'), fn ($q) => $q->where('sesiones.fecha', '<=', $request->hasta))
            ->orderByDesc('sesiones.fecha')
            ->paginate(15)
            ->withQueryString();

        $cursos = Curso::orderBy('nombre')->get();

        return view('asistencia.index', compact('asistencias', 'cursos'));
    }

    public function registrar(Request $request)
    {
        $cursos = Curso::whereHas('sesiones', fn ($q) => $q->whereNull('deleted_at'))
            ->with(['sesiones' => fn ($q) => $q->whereNull('deleted_at')->orderBy('fecha')])
            ->orderBy('nombre')
            ->get();

        $sesion = null;
        $inscritos = collect();
        $asistencias = collect();
        $resumen = ['Presente' => 0, 'Ausente' => 0, 'Tardanza' => 0, 'Justificado' => 0];

        if ($request->filled('sesion_id')) {
            $sesion = Sesion::with('curso')->find($request->sesion_id);

            if ($sesion) {
                $inscritos = Inscripcion::where('curso_id', $sesion->id_curso)
                    ->whereNull('deleted_at')
                    ->with('miembro')
                    ->get();

                $asistencias = Asistencia::where('sesion_id', $sesion->id)
                    ->get()
                    ->keyBy('miembro_id');

                $resumen = [
                    'Presente' => $asistencias->where('estado', 'Presente')->count(),
                    'Ausente' => $asistencias->where('estado', 'Ausente')->count(),
                    'Tardanza' => $asistencias->where('estado', 'Tardanza')->count(),
                    'Justificado' => $asistencias->where('estado', 'Justificado')->count(),
                ];
            }
        }

        return view('asistencia.registrar', compact('cursos', 'sesion', 'inscritos', 'asistencias', 'resumen'));
    }

    public function store(Request $request, Sesion $sesion)
    {
        $validated = $request->validate([
            'asistencia' => 'required|array',
            'asistencia.*.estado' => 'required|in:Presente,Ausente,Tardanza,Justificado',
            'asistencia.*.observacion' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $sesion) {
            foreach ($validated['asistencia'] as $miembroId => $datos) {
                Asistencia::updateOrCreate(
                    ['sesion_id' => $sesion->id, 'miembro_id' => $miembroId],
                    [
                        'estado' => $datos['estado'],
                        'observacion' => $datos['observacion'] ?? null,
                        'user_id' => auth()->id(),
                    ]
                );
            }
        });

        return redirect()->route('asistencia.registrar', ['curso_id' => $sesion->id_curso, 'sesion_id' => $sesion->id])
            ->with('success', 'Asistencia guardada con éxito.');
    }
}