<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Permiso;
use App\Models\Role;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
    /**
     * Listado de roles con edición inline de sus permisos.
     */
    public function index()
    {
        $roles = Role::withCount('permisos')
            ->with('permisos')
            ->orderBy('nombre')
            ->get();

        $permisosPorModulo = Permiso::query()
            ->orderBy('id')
            ->get()
            ->groupBy('modulo');

        $totalPermisos = Permiso::count();

        return view('permisos.index', compact('roles', 'permisosPorModulo', 'totalPermisos'));
    }

    /**
     * Guarda los permisos de un rol desde el editor inline.
     */
    public function update(Request $request, $id)
    {
        $rol = Role::findOrFail($id);

        $datos = $request->validate([
            'permisos'   => 'nullable|array',
            'permisos.*' => 'exists:permisos,id',
        ]);

        $rol->permisos()->sync($datos['permisos'] ?? []);

        return redirect()
            ->route('permisos.index', ['rol' => $rol->id])
            ->with('success', "Permisos de «{$rol->nombre}» actualizados correctamente");
    }
}
