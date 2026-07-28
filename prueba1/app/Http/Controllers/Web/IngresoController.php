<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ingreso;
use App\Models\Culto;
use Illuminate\Http\Request;

class IngresoController extends Controller {

    public function index() {
        $ingresos = Ingreso::with('culto')->paginate(10);
        return view('ingresos.index', compact('ingresos'));
    }

    public function create() {
        $cultos = Culto::orderBy('dia_semana')->get();
        return view('ingresos.create', compact('cultos'));
    }

    public function store(Request $request) {
        $request->validate([
            'id_culto'    => 'required|exists:cultos,id_culto',
            'monto_total' => 'required|numeric|min:0',
            'fecha'       => 'required|date',
            'tipo'        => 'nullable|string|max:100',
            'metodo_pago' => 'nullable|in:efectivo,transferencia,otro',
        ]);
        Ingreso::create($request->all());
        return redirect()->route('ingresos.index')
            ->with('success', 'Ingreso registrado correctamente');
    }

    public function show($id) {
        $ingreso = Ingreso::with('culto')->findOrFail($id);
        return view('ingresos.show', compact('ingreso'));
    }

    public function edit($id) {
        $ingreso = Ingreso::findOrFail($id);
        $cultos  = Culto::orderBy('dia_semana')->get();
        return view('ingresos.edit', compact('ingreso', 'cultos'));
    }

    public function update(Request $request, $id) {
        $ingreso = Ingreso::findOrFail($id);
        $request->validate([
            'id_culto'    => 'sometimes|required|exists:cultos,id_culto',
            'monto_total' => 'required|numeric|min:0',
            'fecha'       => 'required|date',
            'tipo'        => 'nullable|string|max:100',
            'metodo_pago' => 'nullable|in:efectivo,transferencia,otro',
        ]);
        $ingreso->update($request->all());
        return redirect()->route('ingresos.index')
            ->with('success', 'Ingreso actualizado correctamente');
    }

    public function destroy($id) {
        Ingreso::findOrFail($id)->delete();
        return redirect()->route('ingresos.index')
            ->with('success', 'Ingreso eliminado correctamente');
    }
}
