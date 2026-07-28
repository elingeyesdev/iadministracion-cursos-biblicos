<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\Miembro;
use Illuminate\Http\Request;

class DocenteController extends Controller
{
    public function index()
    {
        $docentes = Docente::with('miembro')->paginate(10);
        return view('docentes.index', compact('docentes'));
    }

    public function create()
    {
        $miembros = Miembro::whereDoesntHave('docente')->orderBy('nombre')->get();
        return view('docentes.create', compact('miembros'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_miembro' => 'required|exists:miembros,id|unique:docentes,id_miembro',
            'especialidad' => 'nullable|string|max:150',
            'titulo' => 'nullable|string|max:150',
            'activo' => 'boolean',
        ]);

        $data = $request->all();
        $data['activo'] = $request->has('activo') ? true : false;

        Docente::create($data);

        return redirect()->route('docentes.index')
            ->with('success', 'Docente creado correctamente');
    }

    public function show($id)
    {
        $docente = Docente::with(['miembro', 'cursos'])->findOrFail($id);
        return view('docentes.show', compact('docente'));
    }

    public function edit($id)
    {
        $docente = Docente::findOrFail($id);
        $miembros = Miembro::whereDoesntHave('docente')
            ->orWhere('id', $docente->id_miembro)
            ->orderBy('nombre')
            ->get();
        return view('docentes.edit', compact('docente', 'miembros'));
    }

    public function update(Request $request, $id)
    {
        $docente = Docente::findOrFail($id);
        $request->validate([
            'id_miembro' => 'required|exists:miembros,id|unique:docentes,id_miembro,' . $id,
            'especialidad' => 'nullable|string|max:150',
            'titulo' => 'nullable|string|max:150',
            'activo' => 'boolean',
        ]);

        $data = $request->all();
        $data['activo'] = $request->has('activo') ? true : false;

        $docente->update($data);

        return redirect()->route('docentes.index')
            ->with('success', 'Docente actualizado correctamente');
    }

    public function destroy($id)
    {
        Docente::findOrFail($id)->delete();
        return redirect()->route('docentes.index')
            ->with('success', 'Docente eliminado correctamente');
    }
}
