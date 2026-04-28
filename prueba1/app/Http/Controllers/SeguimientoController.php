<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Seguimiento;
use Illuminate\Http\Request;

class SeguimientoController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->input('busqueda');

        $seguimientos = Seguimiento::query()
            ->with('actividad')
            ->when($busqueda, function ($query, $busqueda) {
                $query->whereHas('actividad', function ($q) use ($busqueda) {
                    $q->where('nombre', 'like', "%{$busqueda}%");
                })->orWhere('comentario', 'like', "%{$busqueda}%")
                  ->orWhere('estado', 'like', "%{$busqueda}%");
            })
            ->orderByDesc('fecha')
            ->paginate(10)
            ->withQueryString();

        return view('seguimiento.index', compact('seguimientos', 'busqueda'));
    }

    public function create()
    {
        $actividades = Actividad::orderBy('nombre')->get();
        return view('seguimiento.create', compact('actividades'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_actividad' => 'required|exists:actividad,id',
            'fecha' => 'required|date',
            'comentario' => 'nullable|string',
            'porcentaje_avance' => 'required|integer|min:0|max:100',
            'estado' => 'required|in:pendiente,en_proceso,finalizada',
        ], [
            'id_actividad.required' => 'Debe seleccionar una actividad.',
            'fecha.required' => 'La fecha es obligatoria.',
            'porcentaje_avance.required' => 'El porcentaje de avance es obligatorio.',
        ]);

        Seguimiento::create($data);

        return redirect()->route('seguimiento.index')->with('success', 'Seguimiento creado correctamente.');
    }

    public function show(Seguimiento $seguimiento)
    {
        $seguimiento->load('actividad');
        return view('seguimiento.show', compact('seguimiento'));
    }

    public function edit(Seguimiento $seguimiento)
    {
        $seguimiento->load('actividad');
        $actividades = Actividad::orderBy('nombre')->get();
        return view('seguimiento.edit', compact('seguimiento', 'actividades'));
    }

    public function update(Request $request, Seguimiento $seguimiento)
    {
        $data = $request->validate([
            'id_actividad' => 'required|exists:actividad,id',
            'fecha' => 'required|date',
            'comentario' => 'nullable|string',
            'porcentaje_avance' => 'required|integer|min:0|max:100',
            'estado' => 'required|in:pendiente,en_proceso,finalizada',
        ]);

        $seguimiento->update($data);

        return redirect()->route('seguimiento.index')->with('success', 'Seguimiento actualizado correctamente.');
    }

    public function destroy(Seguimiento $seguimiento)
    {
        $seguimiento->delete();

        return redirect()->route('seguimiento.index')->with('success', 'Seguimiento eliminado correctamente.');
    }
}

