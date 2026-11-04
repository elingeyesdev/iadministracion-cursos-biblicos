<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\Ministerio;
use Illuminate\Http\Request;

class ActividadController extends Controller
{
    public function index()
    {
        $actividades = Actividad::with('ministerio')->paginate(10);
        return view('actividades.index', compact('actividades'));
    }

    public function create()
    {
        $ministerios = Ministerio::orderBy('nombre')->get();
        return view('actividades.create', compact('ministerios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_ministerio' => 'required|exists:ministerios,id',
            'nombre' => 'required|string|max:150',
            'fecha' => 'required|date',
            'lugar' => 'nullable|string|max:200',
            'estado' => 'required|in:planificada,en_curso,completada,cancelada',
            'descripcion' => 'nullable|string',
        ]);

        Actividad::create($request->all());

        return redirect()->route('actividades.index')
            ->with('success', 'Actividad creada correctamente');
    }

    public function show($id)
    {
        $actividad = Actividad::with(['ministerio', 'seguimientos'])->findOrFail($id);
        return view('actividades.show', compact('actividad'));
    }

    public function edit($id)
    {
        $actividad = Actividad::findOrFail($id);
        $ministerios = Ministerio::orderBy('nombre')->get();
        return view('actividades.edit', compact('actividad', 'ministerios'));
    }

    public function update(Request $request, $id)
    {
        $actividad = Actividad::findOrFail($id);
        $request->validate([
            'id_ministerio' => 'required|exists:ministerios,id',
            'nombre' => 'required|string|max:150',
            'fecha' => 'required|date',
            'lugar' => 'nullable|string|max:200',
            'estado' => 'required|in:planificada,en_curso,completada,cancelada',
            'descripcion' => 'nullable|string',
        ]);

        $actividad->update($request->all());

        return redirect()->route('actividades.index')
            ->with('success', 'Actividad actualizada correctamente');
    }

    public function destroy($id)
    {
        Actividad::findOrFail($id)->delete();
        return redirect()->route('actividades.index')
            ->with('success', 'Actividad eliminada correctamente');
    }
}
