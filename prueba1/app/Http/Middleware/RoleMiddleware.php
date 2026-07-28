<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Allow access only when authenticated user has one of the given roles.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        \Illuminate\Support\Facades\Log::info('RoleMiddleware Check', [
            'user' => $user ? $user->email : 'null',
            'user_role' => $user ? $user->role : 'null',
            'allowed_roles' => $roles
        ]);

        if (! $user) {
            abort(403);
        }

        if (! in_array($user->role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}

