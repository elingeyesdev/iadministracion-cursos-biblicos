<?php

namespace App\Http\Controllers;

use App\Models\Miembro;
use Illuminate\Http\Request;

class MiembroController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $miembros = Miembro::when($buscar, function ($query, $buscar) {
            $query->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('apellido', 'like', "%{$buscar}%")
                  ->orWhere('correo', 'like', "%{$buscar}%");
        })->orderBy('nombre')->paginate(10)->withQueryString();

        return view('miembro.index', compact('miembros', 'buscar'));
    }

    public function create()
    {
        return view('miembro.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'correo'   => 'required|email|unique:miembro,correo',
            'telefono' => 'nullable|string|max:20',
            'rol'      => 'required|in:administrador,miembro,supervisor',
        ]);

        Miembro::create($request->all());

        return redirect()->route('miembro.index')->with('success', 'Miembro creado correctamente.');
    }

    public function show(Miembro $miembro)
    {
        return view('miembro.show', compact('miembro'));
    }

    public function edit(Miembro $miembro)
    {
        return view('miembro.edit', compact('miembro'));
    }

    public function update(Request $request, Miembro $miembro)
    {
        $request->validate([
            'nombre'   => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'correo'   => 'required|email|unique:miembro,correo,' . $miembro->id,
            'telefono' => 'nullable|string|max:20',
            'rol'      => 'required|in:administrador,miembro,supervisor',
        ]);

        $miembro->update($request->all());

        return redirect()->route('miembro.index')->with('success', 'Miembro actualizado correctamente.');
    }

    public function destroy(Miembro $miembro)
    {
        $miembro->delete();

        return redirect()->route('miembro.index')->with('success', 'Miembro eliminado correctamente.');
    }
}