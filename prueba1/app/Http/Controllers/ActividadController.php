<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\CategoriaActividad;
use App\Models\Miembro;
use Illuminate\Http\Request;

class ActividadController extends Controller
{
    /**
     * Listar todas las actividades (con búsqueda).
     */
    public function index(Request $request)
    {
        $busqueda = $request->input('busqueda');

        $actividades = Actividad::when($busqueda, function ($query, $busqueda) {
                $query->where('nombre', 'like', "%{$busqueda}%")
                      ->orWhere('descripcion', 'like', "%{$busqueda}%");
            })
            ->with(['categoria', 'responsable'])
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('actividad.index', compact('actividades', 'busqueda'));
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create()
    {
        $categorias = CategoriaActividad::where('estado', true)->orderBy('nombre')->get();
        $miembros = Miembro::where('estado', true)->orderBy('nombre')->get();
        return view('actividad.create', compact('categorias', 'miembros'));
    }

    /**
     * Guardar nueva actividad.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'            => 'required|string|max:255|unique:actividad,nombre',
            'descripcion'       => 'nullable|string',
            'fecha_inicio'      => 'required|date',
            'fecha_fin'         => 'nullable|date|after_or_equal:fecha_inicio',
            'estado'            => 'required|in:pendiente,en_proceso,finalizada',
            'id_categoria'      => 'required|exists:categoriaactividad,id',
            'id_responsable'    => 'required|exists:miembro,id',
        ], [
            'nombre.required'   => 'El nombre es obligatorio.',
            'nombre.unique'     => 'Ya existe una actividad con ese nombre.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
            'id_categoria.required' => 'Debe seleccionar una categoría.',
            'id_responsable.required' => 'Debe seleccionar un responsable.',
            'estado.required'   => 'El estado es obligatorio.',
        ]);

        Actividad::create($request->only('nombre', 'descripcion', 'fecha_inicio', 'fecha_fin', 'estado', 'id_categoria', 'id_responsable'));

        return redirect()->route('actividad.index')
                         ->with('success', 'Actividad creada correctamente.');
    }

    /**
     * Ver detalle de una actividad.
     */
    public function show(Actividad $actividad)
    {
        $actividad->load(['categoria', 'responsable']);
        return view('actividad.show', compact('actividad'));
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(Actividad $actividad)
    {
        $categorias = CategoriaActividad::where('estado', true)->orderBy('nombre')->get();
        $miembros = Miembro::where('estado', true)->orderBy('nombre')->get();
        $actividad->load(['categoria', 'responsable']);
        return view('actividad.edit', compact('actividad', 'categorias', 'miembros'));
    }

    /**
     * Actualizar actividad.
     */
    public function update(Request $request, Actividad $actividad)
    {
        $request->validate([
            'nombre'            => 'required|string|max:255|unique:actividad,nombre,' . $actividad->id,
            'descripcion'       => 'nullable|string',
            'fecha_inicio'      => 'required|date',
            'fecha_fin'         => 'nullable|date|after_or_equal:fecha_inicio',
            'estado'            => 'required|in:pendiente,en_proceso,finalizada',
            'id_categoria'      => 'required|exists:categoriaactividad,id',
            'id_responsable'    => 'required|exists:miembro,id',
        ], [
            'nombre.required'   => 'El nombre es obligatorio.',
            'nombre.unique'     => 'Ya existe una actividad con ese nombre.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
            'id_categoria.required' => 'Debe seleccionar una categoría.',
            'id_responsable.required' => 'Debe seleccionar un responsable.',
            'estado.required'   => 'El estado es obligatorio.',
        ]);

        $actividad->update($request->only('nombre', 'descripcion', 'fecha_inicio', 'fecha_fin', 'estado', 'id_categoria', 'id_responsable'));

        return redirect()->route('actividad.index')
                         ->with('success', 'Actividad actualizada correctamente.');
    }

    /**
     * Eliminar actividad.
     */
    public function destroy(Actividad $actividad)
    {
        $actividad->delete();

        return redirect()->route('actividad.index')
                         ->with('success', 'Actividad eliminada correctamente.');
    }
}
