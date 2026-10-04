<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LiderIglesia;
use App\Models\Cargo;
use Illuminate\Http\Request;

class LiderIglesiaController extends Controller
{
    public function index()
    {
        $lideres = LiderIglesia::with('cargo')->paginate(10);
        return view('lideres.index', compact('lideres'));
    }

    public function create()
    {
        $cargos = Cargo::orderBy('nombre')->get();
        return view('lideres.create', compact('cargos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_cargo' => 'required|exists:cargos,id',
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'fecha_inicio' => 'required|date',
            'activo' => 'boolean',
        ]);

        $data = $request->all();
        $data['activo'] = $request->has('activo') ? true : false;

        LiderIglesia::create($data);

        return redirect()->route('lideres.index')
            ->with('success', 'Líder de iglesia registrado correctamente');
    }

    public function show($id)
    {
        $lider = LiderIglesia::with('cargo')->findOrFail($id);
        return view('lideres.show', compact('lider'));
    }

    public function edit($id)
    {
        $lider = LiderIglesia::findOrFail($id);
        $cargos = Cargo::orderBy('nombre')->get();
        return view('lideres.edit', compact('lider', 'cargos'));
    }

    public function update(Request $request, $id)
    {
        $lider = LiderIglesia::findOrFail($id);
        $request->validate([
            'id_cargo' => 'required|exists:cargos,id',
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'fecha_inicio' => 'required|date',
            'activo' => 'boolean',
        ]);

        $data = $request->all();
        $data['activo'] = $request->has('activo') ? true : false;

        $lider->update($data);

        return redirect()->route('lideres.index')
            ->with('success', 'Líder de iglesia actualizado correctamente');
    }

    public function destroy($id)
    {
        LiderIglesia::findOrFail($id)->delete();
        return redirect()->route('lideres.index')
            ->with('success', 'Líder de iglesia eliminado correctamente');
    }
}