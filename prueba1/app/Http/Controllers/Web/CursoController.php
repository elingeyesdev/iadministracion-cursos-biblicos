<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Docente;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function index()
    {
        $cursos = Curso::paginate(10);
        return view('cursos.index', compact('cursos'));
    }

    public function create()
    {
        $docentes = Docente::with('miembro')->get();
        return view('cursos.create', compact('docentes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'f_inicio' => 'required|date',
            'f_fin' => 'nullable|date|after_or_equal:f_inicio',
            'cupo_max' => 'nullable|integer|min:1',
            'tiene_pago' => 'boolean',
            'monto_inscripcion' => 'nullable|required_if:tiene_pago,1|numeric|min:0',
            'docentes' => 'nullable|array',
            'docentes.*' => 'exists:docentes,id',
        ]);

        $data = $request->all();
        $data['tiene_pago'] = $request->has('tiene_pago') ? true : false;
        if (!$data['tiene_pago']) {
            $data['monto_inscripcion'] = null;
        }

        $curso = Curso::create($data);
        
        if ($request->has('docentes')) {
            $curso->docentes()->sync($request->input('docentes'));
        }

        return redirect()->route('cursos.index')
            ->with('success', 'Curso creado correctamente');
    }

    public function show($id)
    {
        $curso = Curso::with(['sesiones', 'docentes.miembro'])->findOrFail($id);
        return view('cursos.show', compact('curso'));
    }

    public function edit($id)
    {
        $curso = Curso::with('docentes')->findOrFail($id);
        $docentes = Docente::with('miembro')->get();
        return view('cursos.edit', compact('curso', 'docentes'));
    }

    public function update(Request $request, $id)
    {
        $curso = Curso::findOrFail($id);
        $request->validate([
            'nombre' => 'required|string|max:150',
            'f_inicio' => 'required|date',
            'f_fin' => 'nullable|date|after_or_equal:f_inicio',
            'cupo_max' => 'nullable|integer|min:1',
            'tiene_pago' => 'boolean',
            'monto_inscripcion' => 'nullable|required_if:tiene_pago,1|numeric|min:0',
            'docentes' => 'nullable|array',
            'docentes.*' => 'exists:docentes,id',
        ]);

        $data = $request->all();
        $data['tiene_pago'] = $request->has('tiene_pago') ? true : false;
        if (!$data['tiene_pago']) {
            $data['monto_inscripcion'] = null;
        }

        $curso->update($data);
        
        $curso->docentes()->sync($request->input('docentes', []));

        return redirect()->route('cursos.index')
            ->with('success', 'Curso actualizado correctamente');
    }

    public function destroy($id)
    {
        Curso::findOrFail($id)->delete();
        return redirect()->route('cursos.index')
            ->with('success', 'Curso eliminado correctamente');
    }
}
