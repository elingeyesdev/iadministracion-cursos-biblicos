<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function index()
    {
        $roles = Role::withCount(['users', 'permisos'])->paginate(10);

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre'      => 'required|string|max:100|unique:roles,nombre',
            'descripcion' => 'nullable|string',
            'estado'      => 'in:activo,inactivo',
        ]);

        Role::create([
            'nombre'      => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'estado'      => $datos['estado'] ?? 'activo',
        ]);

        return redirect()->route('roles.index')
            ->with('success', 'Rol creado correctamente');
    }

    public function show($id)
    {
        $rol = Role::with(['users.profile', 'permisos'])->findOrFail($id);

        return view('roles.show', compact('rol'));
    }

    public function edit($id)
    {
        $rol = Role::findOrFail($id);

        return view('roles.edit', compact('rol'));
    }

    public function update(Request $request, $id)
    {
        $rol = Role::findOrFail($id);

        $datos = $request->validate([
            'nombre'      => 'required|string|max:100|unique:roles,nombre,'.$id,
            'descripcion' => 'nullable|string',
            'estado'      => 'in:activo,inactivo',
        ]);

        $rol->update([
            'nombre'      => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'estado'      => $datos['estado'] ?? 'activo',
        ]);

        return redirect()->route('roles.index')
            ->with('success', 'Rol actualizado correctamente');
    }

    public function destroy($id)
    {
        $rol = Role::findOrFail($id);

        if ($rol->users()->wherePivot('estado', 'activo')->exists()) {
            return back()->withErrors([
                'No se puede eliminar: el rol tiene usuarios activos asignados.',
            ]);
        }

        $rol->permisos()->detach();
        $rol->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Rol eliminado correctamente');
    }
}
