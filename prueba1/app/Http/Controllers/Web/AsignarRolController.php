<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class AsignarRolController extends Controller {

    public function index() {
        $usuarios = User::with(['profile','roles'])->paginate(10);
        return view('asignar-roles.index', compact('usuarios'));
    }

    public function edit($id) {
        $usuario = User::with(['profile','roles'])->findOrFail($id);
        $roles   = Role::where('estado','activo')->get();
        $rolesAsignados = $usuario->roles->pluck('id')->toArray();
        return view('asignar-roles.edit', compact('usuario','roles','rolesAsignados'));
    }

    public function update(Request $request, $id) {
        $usuario = User::findOrFail($id);

        $request->validate([
            'roles'   => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $rolesData = [];
        foreach ($request->roles ?? [] as $roleId) {
            $rolesData[$roleId] = [
                'fecha_asignacion' => today(),
                'estado'           => 'activo',
            ];
        }
        $usuario->roles()->sync($rolesData);

        return redirect()->route('asignar-roles.index')
            ->with('success', 'Roles asignados correctamente al usuario');
    }
}
