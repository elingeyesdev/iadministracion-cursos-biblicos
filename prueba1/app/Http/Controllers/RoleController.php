<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->input('busqueda', ''));
        $roles = Role::query()
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where('name', 'like', "%{$busqueda}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('roles.index', compact('roles', 'busqueda'));
    }

    public function show(Role $role): View
    {
        $usuariosConRol = User::query()->where('role', $role->name)->count();

        return view('roles.show', compact('role', 'usuariosConRol'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, ['administrador', 'usuario'], true)) {
            return redirect()
                ->route('roles.index')
                ->with('error', 'No se puede eliminar un rol base del sistema.');
        }

        $usersCount = User::query()->where('role', $role->name)->count();
        if ($usersCount > 0) {
            return redirect()
                ->route('roles.index')
                ->with('error', 'No se puede eliminar un rol asignado a usuarios.');
        }

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }
}
