<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller {

    public function index() {
        $usuarios = User::with(['profile','roles'])->paginate(10);
        return view('usuarios.index', compact('usuarios'));
    }

    public function create() {
        return view('usuarios.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|unique:users,email',
            'username'              => 'nullable|string|max:50|unique:user_profiles,username',
            'telefono'              => 'nullable|string|max:20',
            'estado'                => 'in:activo,inactivo',
            'password'              => 'required|confirmed|min:8',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->profile()->create([
            'username'       => $request->username,
            'telefono'       => $request->telefono,
            'estado'         => $request->estado ?? 'activo',
            'fecha_registro' => today(),
        ]);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente');
    }

    public function show($id) {
        $usuario = User::with(['profile','roles'])->findOrFail($id);
        return view('usuarios.show', compact('usuario'));
    }

    public function edit($id) {
        $usuario = User::with('profile')->findOrFail($id);
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, $id) {
        $usuario = User::with('profile')->findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email,'.$id,
            'username' => 'nullable|string|max:50|unique:user_profiles,username,'
                          .optional($usuario->profile)->id,
            'telefono' => 'nullable|string|max:20',
            'estado'   => 'in:activo,inactivo',
            'password' => 'nullable|confirmed|min:8',
        ]);

        $usuario->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $usuario->update(['password' => Hash::make($request->password)]);
        }

        $usuario->profile()->updateOrCreate(
            ['user_id' => $usuario->id],
            [
                'username' => $request->username,
                'telefono' => $request->telefono,
                'estado'   => $request->estado ?? 'activo',
            ]
        );

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy($id) {
        if (auth()->id() == $id) {
            return back()->withErrors(['No podés eliminar tu propio usuario.']);
        }
        User::findOrFail($id)->delete();
        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado correctamente');
    }

    public function cambiarEstadoRol($userId, $roleId) {
        $user  = User::findOrFail($userId);
        $pivot = $user->roles()->where('role_id', $roleId)->first();

        if (!$pivot) return back()->withErrors(['Asignación no encontrada.']);

        $nuevoEstado = $pivot->pivot->estado === 'activo' ? 'inactivo' : 'activo';
        $user->roles()->updateExistingPivot($roleId, ['estado' => $nuevoEstado]);

        return back()->with('success', 'Estado del rol actualizado');
    }
}
