<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /** @var array<int, string>|null */
    protected ?array $rolesActivosCache = null;

    /** @var array<int, string>|null */
    protected ?array $permisosCache = null;

    public function isAdmin(): bool
    {
        return $this->esAdmin();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Perfil extendido 1:1
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    // Roles N:M
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot('fecha_asignacion', 'estado')
            ->withTimestamps();
    }

    // ── Roles ─────────────────────────────────────────────

    /**
     * Nombres de los roles activos del usuario (rol activo y asignación activa).
     *
     * @return array<int, string>
     */
    public function nombresRolesActivos(): array
    {
        if ($this->rolesActivosCache === null) {
            $this->rolesActivosCache = $this->roles()
                ->wherePivot('estado', 'activo')
                ->where('roles.estado', 'activo')
                ->pluck('roles.nombre')
                ->all();
        }

        return $this->rolesActivosCache;
    }

    public function tieneRol(string $nombre): bool
    {
        return in_array($nombre, $this->nombresRolesActivos(), true);
    }

    public function esAdmin(): bool
    {
        return $this->tieneRol('Administrador');
    }

    // ── Permisos ───────────────────────────────────────────

    /**
     * Permisos efectivos del usuario, obtenidos de sus roles activos.
     *
     * @return array<int, string>
     */
    public function permisos(): array
    {
        if ($this->permisosCache !== null) {
            return $this->permisosCache;
        }

        $this->permisosCache = $this->roles()
            ->wherePivot('estado', 'activo')
            ->where('roles.estado', 'activo')
            ->with('permisos:id,nombre')
            ->get()
            ->flatMap(fn (Role $rol) => $rol->permisos->pluck('nombre'))
            ->unique()
            ->values()
            ->all();

        return $this->permisosCache;
    }

    /**
     * Atajo para usar los permisos como atributo en las vistas.
     *
     * @return array<int, string>
     */
    public function getPermisosAttribute(): array
    {
        return $this->permisos();
    }

    /**
     * Indica si el usuario tiene al menos uno de los permisos indicados.
     *
     * Un permiso ".gestionar" concede también el ".ver" del mismo módulo.
     */
    public function tienePermiso(string ...$permisos): bool
    {
        if ($this->esAdmin()) {
            return true;
        }

        if ($permisos === []) {
            return false;
        }

        $asignados = $this->permisos();

        foreach ($permisos as $permiso) {
            if (in_array($permiso, $asignados, true)) {
                return true;
            }

            // "modulo.gestionar" incluye "modulo.ver".
            if (str_ends_with($permiso, '.ver')) {
                $gestionar = substr($permiso, 0, -4).'.gestionar';

                if (in_array($gestionar, $asignados, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Alias semántico de tienePermiso() para usar en controladores y vistas.
     */
    public function puede(string ...$permisos): bool
    {
        return $this->tienePermiso(...$permisos);
    }

    /**
     * Limpia la caché en memoria de roles y permisos.
     */
    public function olvidarPermisos(): void
    {
        $this->rolesActivosCache = null;
        $this->permisosCache = null;
    }
}
