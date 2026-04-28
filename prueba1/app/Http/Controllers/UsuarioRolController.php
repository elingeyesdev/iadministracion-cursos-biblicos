<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuarioRolController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = (string) $request->input('buscar', '');

        $usuarios = User::query()
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('name', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('asignar-roles.index', compact('usuarios', 'buscar'));
    }

    public function edit(User $user): View
    {
        $roles = Role::query()
            ->orderBy('name')
            ->pluck('name');

        return view('asignar-roles.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $roleNames = Role::query()->pluck('name')->all();

        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in($roleNames)],
        ]);

        $newRole = $validated['role'];

        if ($user->role === 'administrador' && $newRole !== 'administrador') {
            $adminCount = User::query()->where('role', 'administrador')->count();
            if ($adminCount <= 1) {
                return redirect()
                    ->route('asignar-roles.edit', $user)
                    ->with('error', 'No puedes quitar el rol de administrador al único administrador del sistema.');
            }
        }

        $user->update(['role' => $newRole]);

        return redirect()
            ->route('asignar-roles.index')
            ->with('success', 'Rol actualizado para '.$user->name.'.');
    }
}
