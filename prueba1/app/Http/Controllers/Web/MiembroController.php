<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Miembro;
use Illuminate\Http\Request;

class MiembroController extends Controller
{
    public function index()
    {
        $miembros = Miembro::paginate(10);
        return view('miembros.index', compact('miembros'));
    }

    public function create()
    {
        return view('miembros.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'email'  => 'required|email|unique:miembros,email',
            'telefono' => 'nullable|string|max:20|regex:/^[0-9+\s\-()]+$/',
            'f_nacimiento' => 'nullable|date|before_or_equal:today',
            'estado' => 'required|in:activo,inactivo,visita',
            'direccion' => 'nullable|string|max:200',
            'fecha_ingreso' => 'nullable|date|before_or_equal:today',
            'sexo' => 'nullable|in:M,F,otro',
        ], [
            'telefono.regex' => 'El teléfono no puede contener letras.',
            'f_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser posterior a la fecha actual.',
            'fecha_ingreso.before_or_equal' => 'La fecha de ingreso no puede ser posterior a la fecha actual.',
        ]);

        Miembro::create($request->all());

        return redirect()->route('miembros.index')
            ->with('success', 'Miembro creado correctamente');
    }

    public function show($id)
    {
        $miembro = Miembro::with(['contratos', 'sesiones'])->findOrFail($id);
        return view('miembros.show', compact('miembro'));
    }

    public function edit($id)
    {
        $miembro = Miembro::findOrFail($id);
        return view('miembros.edit', compact('miembro'));
    }

    public function update(Request $request, $id)
    {
        $miembro = Miembro::findOrFail($id);
        $request->validate([
            'nombre' => 'required|string|max:100',
            'email'  => 'required|email|unique:miembros,email,' . $id,
            'telefono' => 'nullable|string|max:20|regex:/^[0-9+\s\-()]+$/',
            'f_nacimiento' => 'nullable|date|before_or_equal:today',
            'estado' => 'required|in:activo,inactivo,visita',
            'direccion' => 'nullable|string|max:200',
            'fecha_ingreso' => 'nullable|date|before_or_equal:today',
            'sexo' => 'nullable|in:M,F,otro',
        ], [
            'telefono.regex' => 'El teléfono no puede contener letras.',
            'f_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser posterior a la fecha actual.',
            'fecha_ingreso.before_or_equal' => 'La fecha de ingreso no puede ser posterior a la fecha actual.',
        ]);

        $miembro->update($request->all());

        return redirect()->route('miembros.index')
            ->with('success', 'Miembro actualizado correctamente');
    }

    public function destroy($id)
    {
        Miembro::findOrFail($id)->delete();
        return redirect()->route('miembros.index')
            ->with('success', 'Miembro eliminado correctamente');
    }
}
