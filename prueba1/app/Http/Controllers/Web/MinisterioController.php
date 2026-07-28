<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ministerio;
use App\Models\Miembro;
use Illuminate\Http\Request;

class MinisterioController extends Controller
{
    public function index()
    {
        $ministerios = Ministerio::with('lider')->paginate(10);
        return view('ministerios.index', compact('ministerios'));
    }

    public function create()
    {
        $miembros = Miembro::orderBy('nombre')->get();
        return view('ministerios.create', compact('miembros'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'id_lider' => 'nullable|exists:miembros,id',
        ]);

        Ministerio::create($request->all());

        return redirect()->route('ministerios.index')
            ->with('success', 'Ministerio creado correctamente');
    }

    public function show($id)
    {
        $ministerio = Ministerio::with(['lider', 'actividades', 'egresos'])->findOrFail($id);
        return view('ministerios.show', compact('ministerio'));
    }

    public function edit($id)
    {
        $ministerio = Ministerio::findOrFail($id);
        $miembros = Miembro::orderBy('nombre')->get();
        return view('ministerios.edit', compact('ministerio', 'miembros'));
    }

    public function update(Request $request, $id)
    {
        $ministerio = Ministerio::findOrFail($id);
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'id_lider' => 'nullable|exists:miembros,id',
        ]);

        $ministerio->update($request->all());

        return redirect()->route('ministerios.index')
            ->with('success', 'Ministerio actualizado correctamente');
    }

    public function destroy($id)
    {
        Ministerio::findOrFail($id)->delete();
        return redirect()->route('ministerios.index')
            ->with('success', 'Ministerio eliminado correctamente');
    }
}
