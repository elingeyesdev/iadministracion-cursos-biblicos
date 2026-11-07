<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermiso
{
    /**
     * Autoriza la petición según los permisos del rol del usuario.
     *
     * El rol Administrador siempre tiene acceso. Las rutas que no estén
     * mapeadas en config/permisos.php se dejan pasar (fail-open) para no
     * romper funcionalidades no cubiertas.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $nombre = $request->route()?->getName();
        $mapa = config('permisos.rutas', []);

        if (! $user
            || (method_exists($user, 'esAdministrador') && $user->esAdministrador())
            || ! $nombre || ! isset($mapa[$nombre])) {
            return $next($request);
        }

        [$modulo, $accion] = $mapa[$nombre];

        if (! $user->puede($modulo, $accion)) {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }

        // El buscador de un listado requiere además el permiso "buscar".
        if ($request->isMethod('GET') && $request->filled('q')) {
            $conBusqueda = config('permisos.modulos_con_busqueda', []);

            if (in_array($modulo, $conBusqueda, true) && ! $user->puede($modulo, 'buscar')) {
                abort(403, 'No tienes permiso para buscar en este módulo.');
            }
        }

        return $next($request);
    }
}
