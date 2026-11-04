<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Los permisos funcionan como habilidades de Gate: @can('miembros.ver')
        // y $user->can('miembros.ver') consultan los permisos de los roles.
        Gate::before(function (User $user, string $ability) {
            return $user->tienePermiso($ability) ? true : null;
        });

        // Directiva Blade: @permiso('roles.gestionar') ... @endpermiso
        Blade::if('permiso', function (string ...$permisos) {
            return auth()->check() && auth()->user()->tienePermiso(...$permisos);
        });
    }
}
