<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use Illuminate\Http\Request;

class CargoController extends Controller
{
    public function index()
    {
        $cargos = Cargo::withCount('lideresIglesia')->paginate(10);
        return view('cargos.index', compact('cargos'));
    }

    public function create()
    {
        return view('cargos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'nivel_jerarquico' => 'nullable|integer|min:1',
        ]);

        Cargo::create($request->all());

        return redirect()->route('cargos.index')
            ->with('success', 'Cargo creado correctamente');
    }

    public function show($id)
    {
        $cargo = Cargo::with('lideresIglesia')->findOrFail($id);
        return view('cargos.show', compact('cargo'));
    }

    public function edit($id)
    {
        $cargo = Cargo::findOrFail($id);
        return view('cargos.edit', compact('cargo'));
    }

    public function update(Request $request, $id)
    {
        $cargo = Cargo::findOrFail($id);
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'nivel_jerarquico' => 'nullable|integer|min:1',
        ]);

        $cargo->update($request->all());

        return redirect()->route('cargos.index')
            ->with('success', 'Cargo actualizado correctamente');
    }

    public function destroy($id)
    {
        Cargo::findOrFail($id)->delete();
        return redirect()->route('cargos.index')
            ->with('success', 'Cargo eliminado correctamente');
    }
}