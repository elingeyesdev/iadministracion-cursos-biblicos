<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRol {
    public function handle(Request $request, Closure $next, string ...$roles): Response {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        $user = auth()->user();
        if ($user->esAdmin()) {
            return $next($request);
        }
        foreach ($roles as $rol) {
            if ($user->tieneRol($rol)) {
                return $next($request);
            }
        }
        abort(403);
    }
}
