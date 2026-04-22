<?php

namespace App\Http\Controllers;

use App\Models\CategoriaActividad;
use Illuminate\Http\Request;

class CategoriaActividadController extends Controller
{
    /**
     * Listar todas las categorías (con búsqueda).
     */
    public function index(Request $request)
    {
        $busqueda = $request->input('busqueda');

        $categorias = CategoriaActividad::when($busqueda, function ($query, $busqueda) {
                $query->where('nombre', 'like', "%{$busqueda}%")
                      ->orWhere('descripcion', 'like', "%{$busqueda}%");
            })
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('categoriaactividad.index', compact('categorias', 'busqueda'));
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create()
    {
        return view('categoriaactividad.create');
    }

    /**
     * Guardar nueva categoría.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255|unique:categoriaactividad,nombre',
            'descripcion' => 'nullable|string',
            'estado'      => 'required|boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique'   => 'Ya existe una categoría con ese nombre.',
            'estado.required' => 'El estado es obligatorio.',
        ]);

        CategoriaActividad::create($request->only('nombre', 'descripcion', 'estado'));

        return redirect()->route('categoriaactividad.index')
                         ->with('success', 'Categoría creada correctamente.');
    }

    /**
     * Ver detalle de una categoría.
     */
    public function show(CategoriaActividad $categoriaactividad)
    {
        return view('categoriaactividad.show', ['categoria' => $categoriaactividad]);
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(CategoriaActividad $categoriaactividad)
    {
        return view('categoriaactividad.edit', ['categoria' => $categoriaactividad]);
    }

    /**
     * Actualizar categoría.
     */
    public function update(Request $request, CategoriaActividad $categoriaactividad)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255|unique:categoriaactividad,nombre,' . $categoriaactividad->id,
            'descripcion' => 'nullable|string',
            'estado'      => 'required|boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique'   => 'Ya existe una categoría con ese nombre.',
            'estado.required' => 'El estado es obligatorio.',
        ]);

        $categoriaactividad->update($request->only('nombre', 'descripcion', 'estado'));

        return redirect()->route('categoriaactividad.index')
                         ->with('success', 'Categoría actualizada correctamente.');
    }

    /**
     * Eliminar categoría.
     */
    public function destroy(CategoriaActividad $categoriaactividad)
    {
        $categoriaactividad->delete();

        return redirect()->route('categoriaactividad.index')
                         ->with('success', 'Categoría eliminada correctamente.');
    }
}