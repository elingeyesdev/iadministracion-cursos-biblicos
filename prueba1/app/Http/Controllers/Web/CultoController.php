<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Culto;
use Illuminate\Http\Request;

class CultoController extends Controller {

    public function index() {
        $cultos = Culto::withSum('ingresos','monto_total')
                       ->paginate(10);
        return view('cultos.index', compact('cultos'));
    }

    public function create() {
        return view('cultos.create');
    }

    public function store(Request $request) {
        $request->validate([
            'nombre'     => 'required|string|max:150',
            'dia_semana' => 'required|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'hora'       => 'required',
            'descripcion'=> 'nullable|string',
        ]);
        Culto::create($request->all());
        return redirect()->route('cultos.index')
            ->with('success', 'Culto creado correctamente');
    }

    public function show($id) {
        $culto = Culto::with('ingresos')->findOrFail($id);
        $total = $culto->ingresos->sum('monto_total');
        return view('cultos.show', compact('culto', 'total'));
    }

    public function edit($id) {
        $culto = Culto::findOrFail($id);
        return view('cultos.edit', compact('culto'));
    }

    public function update(Request $request, $id) {
        $culto = Culto::findOrFail($id);
        $request->validate([
            'nombre'     => 'required|string|max:150',
            'dia_semana' => 'required|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'hora'       => 'required',
            'descripcion'=> 'nullable|string',
        ]);
        $culto->update($request->all());
        return redirect()->route('cultos.index')
            ->with('success', 'Culto actualizado correctamente');
    }

    public function destroy($id) {
        $culto = Culto::findOrFail($id);
        if ($culto->ingresos()->exists()) {
            return back()->withErrors([
                'No se puede eliminar: el culto tiene ingresos registrados.'
            ]);
        }
        $culto->delete();
        return redirect()->route('cultos.index')
            ->with('success', 'Culto eliminado correctamente');
    }
}
