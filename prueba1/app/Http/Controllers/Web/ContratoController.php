<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Models\Miembro;
use Illuminate\Http\Request;

class ContratoController extends Controller
{
    public function index()
    {
        $contratos = Contrato::with('miembro')->paginate(10);
        return view('contratos.index', compact('contratos'));
    }

    public function create()
    {
        $miembros = Miembro::orderBy('nombre')->get();
        return view('contratos.create', compact('miembros'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_miembro' => 'required|exists:miembros,id',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'monto' => 'nullable|numeric|min:0',
            'estado' => 'required|in:activo,finalizado,cancelado',
        ]);

        Contrato::create($request->all());

        return redirect()->route('contratos.index')
            ->with('success', 'Contrato creado correctamente');
    }

    public function show($id)
    {
        $contrato = Contrato::with(['miembro', 'egresos'])->findOrFail($id);
        return view('contratos.show', compact('contrato'));
    }

    public function edit($id)
    {
        $contrato = Contrato::findOrFail($id);
        $miembros = Miembro::orderBy('nombre')->get();
        return view('contratos.edit', compact('contrato', 'miembros'));
    }

    public function update(Request $request, $id)
    {
        $contrato = Contrato::findOrFail($id);
        $request->validate([
            'id_miembro' => 'required|exists:miembros,id',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'monto' => 'nullable|numeric|min:0',
            'estado' => 'required|in:activo,finalizado,cancelado',
        ]);

        $contrato->update($request->all());

        return redirect()->route('contratos.index')
            ->with('success', 'Contrato actualizado correctamente');
    }

    public function destroy($id)
    {
        Contrato::findOrFail($id)->delete();
        return redirect()->route('contratos.index')
            ->with('success', 'Contrato eliminado correctamente');
    }
}
