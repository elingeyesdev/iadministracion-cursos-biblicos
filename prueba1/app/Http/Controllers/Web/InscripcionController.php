<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Inscripcion;
use Illuminate\Http\Request;

class InscripcionController extends Controller
{
    public function index()
    {
        $cursos = Curso::query()
            ->withCount(['inscripciones' => fn ($q) => $q->whereNull('inscripciones.deleted_at')])
            ->orderBy('f_inicio', 'desc')
            ->paginate(10);

        return view('inscripciones.index', compact('cursos'));
    }

    public function gestion($id)
    {
        $curso = Curso::findOrFail($id);

        $inscripciones = Inscripcion::withTrashed()
            ->with('miembro')
            ->where('curso_id', $id)
            ->orderBy('fecha_inscripcion')
            ->get();

        $miembrosDisponibles = Inscripcion::getMiembrosDisponibles($curso->id);

        return view('inscripciones.gestion', compact('curso', 'inscripciones', 'miembrosDisponibles'));
    }

    public function store(Request $request, Curso $curso)
    {
        $validated = $request->validate([
            'miembro_id' => ['required', 'exists:miembros,id'],
            'fecha_inscripcion' => ['required', 'date'],
        ]);

        $yaInscrito = Inscripcion::where('miembro_id', $validated['miembro_id'])
            ->where('curso_id', $curso->id)
            ->exists();

        if ($yaInscrito) {
            return back()->with('error', 'El miembro ya está inscrito en este curso.');
        }

        $inscritos = Inscripcion::where('curso_id', $curso->id)->count();

        if ($curso->cupo_max && $inscritos >= $curso->cupo_max) {
            return back()->with('error', 'El curso alcanzó su cupo máximo de ' . $curso->cupo_max . ' inscritos.');
        }

        Inscripcion::create($validated + [
            'curso_id' => $curso->id,
            'estado' => 'Activo',
        ]);

        return redirect()->route('inscripciones.gestion', $curso->id)
            ->with('success', 'Miembro inscrito con éxito.');
    }

    public function edit($id)
    {
        $inscripcion = Inscripcion::with(['miembro', 'curso'])->findOrFail($id);

        return view('inscripciones.edit', compact('inscripcion'));
    }

    public function update(Request $request, $id)
    {
        $inscripcion = Inscripcion::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'fecha_inscripcion' => ['required', 'date'],
            'estado' => ['required', 'string', 'max:50', 'in:Activo,Retirado,Completado'],
        ]);

        $inscripcion->update($validated);

        return redirect()->route('inscripciones.gestion', $inscripcion->curso_id)
            ->with('success', 'Inscripción actualizada con éxito.');
    }

    public function destroy($id)
    {
        $inscripcion = Inscripcion::findOrFail($id);
        $cursoId = $inscripcion->curso_id;
        $inscripcion->delete();

        return redirect()->route('inscripciones.gestion', $cursoId)
            ->with('success', 'Inscripción eliminada correctamente');
    }

    public function restore($id)
    {
        $inscripcion = Inscripcion::withTrashed()->findOrFail($id);
        $cursoId = $inscripcion->curso_id;
        $inscripcion->restore();

        return redirect()->route('inscripciones.gestion', $cursoId)
            ->with('success', 'Inscripción restaurada con éxito.');
    }
}