<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermiso
{
    /**
     * Permite el acceso si el usuario autenticado tiene al menos uno de los
     * permisos indicados. El rol Administrador tiene acceso total.
     *
     * Uso: ->middleware('permiso:miembros.ver')
     *      ->middleware('permiso:miembros.ver,miembros.gestionar')
     */
    public function handle(Request $request, Closure $next, string ...$permisos): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->tienePermiso(...$permisos)) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        return $next($request);
    }
}
