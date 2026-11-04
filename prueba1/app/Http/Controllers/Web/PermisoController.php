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

        // Categorías en el orden definido en config/permisos.php.
        $categorias = collect(config('permisos.categorias', []))
            ->sortBy('orden');

        // Ordena los permisos siguiendo el orden de los módulos en la config.
        $ordenModulos = collect(config('permisos.modulos', []))->keys()->flip();

        $permisos = Permiso::query()
            ->orderBy('id')
            ->get()
            ->sortBy(fn (Permiso $permiso) => sprintf(
                '%03d-%03d',
                $ordenModulos[$permiso->modulo] ?? 999,
                $permiso->id
            ))
            ->values();

        $permisosPorCategoria = $categorias->map(fn (array $datos, string $clave) => [
            'nombre'   => $datos['nombre'],
            'icono'    => $datos['icono'] ?? 'bi-key',
            'permisos' => $permisos->filter(fn (Permiso $permiso) => $permiso->categoria() === $clave)->values(),
        ]);

        // Permisos sin categoría conocida (por ejemplo, creados manualmente).
        $sinCategoria = $permisos->reject(
            fn (Permiso $permiso) => $categorias->has($permiso->categoria())
        )->values();

        if ($sinCategoria->isNotEmpty()) {
            $permisosPorCategoria->put('otros', [
                'nombre'   => 'Otros',
                'icono'    => 'bi-key',
                'permisos' => $sinCategoria,
            ]);
        }

        $totalPermisos = $permisos->count();

        return view('permisos.index', compact('roles', 'permisosPorCategoria', 'totalPermisos'));
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
