<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Egreso;
use App\Models\Ministerio;
use App\Models\Contrato;
use Illuminate\Http\Request;

class EgresoController extends Controller
{
    public function index()
    {
        $egresos = Egreso::with(['ministerio', 'contrato.miembro'])->paginate(10);
        return view('egresos.index', compact('egresos'));
    }

    public function create()
    {
        $ministerios = Ministerio::orderBy('nombre')->get();
        $contratos = Contrato::with('miembro')->get();
        return view('egresos.create', compact('ministerios', 'contratos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_ministerio' => 'nullable|exists:ministerios,id',
            'id_contrato' => 'nullable|exists:contratos,id',
            'tipo_egreso' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'responsable' => 'nullable|string|max:100',
            'comprobante' => 'nullable|string|max:200',
        ]);

        Egreso::create($request->all());

        return redirect()->route('egresos.index')
            ->with('success', 'Egreso creado correctamente');
    }

    public function show($id)
    {
        $egreso = Egreso::with(['ministerio', 'contrato.miembro'])->findOrFail($id);
        return view('egresos.show', compact('egreso'));
    }

    public function edit($id)
    {
        $egreso = Egreso::findOrFail($id);
        $ministerios = Ministerio::orderBy('nombre')->get();
        $contratos = Contrato::with('miembro')->get();
        return view('egresos.edit', compact('egreso', 'ministerios', 'contratos'));
    }

    public function update(Request $request, $id)
    {
        $egreso = Egreso::findOrFail($id);
        $request->validate([
            'id_ministerio' => 'nullable|exists:ministerios,id',
            'id_contrato' => 'nullable|exists:contratos,id',
            'tipo_egreso' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'responsable' => 'nullable|string|max:100',
            'comprobante' => 'nullable|string|max:200',
        ]);

        $egreso->update($request->all());

        return redirect()->route('egresos.index')
            ->with('success', 'Egreso actualizado correctamente');
    }

    public function destroy($id)
    {
        Egreso::findOrFail($id)->delete();
        return redirect()->route('egresos.index')
            ->with('success', 'Egreso eliminado correctamente');
    }
}
