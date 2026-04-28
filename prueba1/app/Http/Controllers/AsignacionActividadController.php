<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\AsignacionActividad;
use App\Models\Miembro;
use Illuminate\Http\Request;

class AsignacionActividadController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->input('busqueda');

        $asignaciones = AsignacionActividad::query()
            ->with(['actividad', 'miembro'])
            ->when($busqueda, function ($query, $busqueda) {
                $query->whereHas('actividad', function ($q) use ($busqueda) {
                    $q->where('nombre', 'like', "%{$busqueda}%");
                })->orWhereHas('miembro', function ($q) use ($busqueda) {
                    $q->where('nombre', 'like', "%{$busqueda}%")
                      ->orWhere('apellido', 'like', "%{$busqueda}%")
                      ->orWhere('correo', 'like', "%{$busqueda}%");
                })->orWhere('rol_en_actividad', 'like', "%{$busqueda}%");
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('asignacionactividad.index', compact('asignaciones', 'busqueda'));
    }

    public function create()
    {
        $actividades = Actividad::orderBy('nombre')->get();
        $miembros = Miembro::orderBy('nombre')->get();

        return view('asignacionactividad.create', compact('actividades', 'miembros'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_actividad' => 'required|exists:actividad,id',
            'id_miembro' => 'required|exists:miembro,id',
            'rol_en_actividad' => 'nullable|string|max:255',
            'fecha_asignacion' => 'nullable|date',
        ], [
            'id_actividad.required' => 'Debe seleccionar una actividad.',
            'id_miembro.required' => 'Debe seleccionar un miembro.',
        ]);

        AsignacionActividad::create($data);

        return redirect()->route('asignacionactividad.index')->with('success', 'Asignación creada correctamente.');
    }

    public function show(AsignacionActividad $asignacionactividad)
    {
        $asignacionactividad->load(['actividad', 'miembro']);

        return view('asignacionactividad.show', ['asignacion' => $asignacionactividad]);
    }

    public function edit(AsignacionActividad $asignacionactividad)
    {
        $asignacionactividad->load(['actividad', 'miembro']);
        $actividades = Actividad::orderBy('nombre')->get();
        $miembros = Miembro::orderBy('nombre')->get();

        return view('asignacionactividad.edit', [
            'asignacion' => $asignacionactividad,
            'actividades' => $actividades,
            'miembros' => $miembros,
        ]);
    }

    public function update(Request $request, AsignacionActividad $asignacionactividad)
    {
        $data = $request->validate([
            'id_actividad' => 'required|exists:actividad,id',
            'id_miembro' => 'required|exists:miembro,id',
            'rol_en_actividad' => 'nullable|string|max:255',
            'fecha_asignacion' => 'nullable|date',
        ]);

        $asignacionactividad->update($data);

        return redirect()->route('asignacionactividad.index')->with('success', 'Asignación actualizada correctamente.');
    }

    public function destroy(AsignacionActividad $asignacionactividad)
    {
        $asignacionactividad->delete();

        return redirect()->route('asignacionactividad.index')->with('success', 'Asignación eliminada correctamente.');
    }
}

