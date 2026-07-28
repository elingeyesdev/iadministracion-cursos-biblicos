<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sesion;
use App\Models\Curso;
use App\Models\Miembro;
use Illuminate\Http\Request;

class SesionController extends Controller
{
    public function index()
    {
        $sesiones = Sesion::with('curso')->paginate(10);
        return view('sesiones.index', compact('sesiones'));
    }

    public function create()
    {
        $cursos = Curso::orderBy('nombre')->get();
        $miembros = Miembro::orderBy('nombre')->get();
        return view('sesiones.create', compact('cursos', 'miembros'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_curso' => 'required|exists:cursos,id',
            'fecha' => 'required|date',
            'hora' => 'nullable',
            'tema' => 'nullable|string|max:200',
            'observacion' => 'nullable|string',
            'miembros' => 'nullable|array',
            'miembros.*' => 'exists:miembros,id',
        ]);

        $sesion = Sesion::create($request->all());

        if ($request->has('miembros')) {
            $sesion->miembros()->sync($request->input('miembros'));
        }

        return redirect()->route('sesiones.index')
            ->with('success', 'Sesión creada correctamente');
    }

    public function show($id)
    {
        $sesion = Sesion::with(['curso', 'miembros'])->findOrFail($id);
        return view('sesiones.show', compact('sesion'));
    }

    public function edit($id)
    {
        $sesion = Sesion::findOrFail($id);
        $cursos = Curso::orderBy('nombre')->get();
        $miembros = Miembro::orderBy('nombre')->get();
        return view('sesiones.edit', compact('sesion', 'cursos', 'miembros'));
    }

    public function update(Request $request, $id)
    {
        $sesion = Sesion::findOrFail($id);
        $request->validate([
            'id_curso' => 'required|exists:cursos,id',
            'fecha' => 'required|date',
            'hora' => 'nullable',
            'tema' => 'nullable|string|max:200',
            'observacion' => 'nullable|string',
            'miembros' => 'nullable|array',
            'miembros.*' => 'exists:miembros,id',
        ]);

        $sesion->update($request->all());

        $sesion->miembros()->sync($request->input('miembros', []));

        return redirect()->route('sesiones.index')
            ->with('success', 'Sesión actualizada correctamente');
    }

    public function destroy($id)
    {
        Sesion::findOrFail($id)->delete();
        return redirect()->route('sesiones.index')
            ->with('success', 'Sesión eliminada correctamente');
    }
}
