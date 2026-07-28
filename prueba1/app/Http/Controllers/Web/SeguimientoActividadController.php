<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SeguimientoActividad;
use App\Models\Actividad;
use Illuminate\Http\Request;

class SeguimientoActividadController extends Controller
{
    public function index()
    {
        $seguimientos = SeguimientoActividad::with('actividad')->paginate(10);
        return view('seguimientos.index', compact('seguimientos'));
    }

    public function create()
    {
        $actividades = Actividad::orderBy('nombre')->get();
        return view('seguimientos.create', compact('actividades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_actividad' => 'required|exists:actividades,id',
            'fecha_registro' => 'required|date',
            'estado' => 'required|string|max:50',
            'observacion' => 'nullable|string',
            'responsable' => 'nullable|string|max:100',
            'porcentaje_avance' => 'required|numeric|min:0|max:100',
        ]);

        SeguimientoActividad::create($request->all());

        return redirect()->route('seguimientos.index')
            ->with('success', 'Seguimiento creado correctamente');
    }

    public function show($id)
    {
        $seguimiento = SeguimientoActividad::with('actividad')->findOrFail($id);
        return view('seguimientos.show', compact('seguimiento'));
    }

    public function edit($id)
    {
        $seguimiento = SeguimientoActividad::findOrFail($id);
        $actividades = Actividad::orderBy('nombre')->get();
        return view('seguimientos.edit', compact('seguimiento', 'actividades'));
    }

    public function update(Request $request, $id)
    {
        $seguimiento = SeguimientoActividad::findOrFail($id);
        $request->validate([
            'id_actividad' => 'required|exists:actividades,id',
            'fecha_registro' => 'required|date',
            'estado' => 'required|string|max:50',
            'observacion' => 'nullable|string',
            'responsable' => 'nullable|string|max:100',
            'porcentaje_avance' => 'required|numeric|min:0|max:100',
        ]);

        $seguimiento->update($request->all());

        return redirect()->route('seguimientos.index')
            ->with('success', 'Seguimiento actualizado correctamente');
    }

    public function destroy($id)
    {
        SeguimientoActividad::findOrFail($id)->delete();
        return redirect()->route('seguimientos.index')
            ->with('success', 'Seguimiento eliminado correctamente');
    }
}
