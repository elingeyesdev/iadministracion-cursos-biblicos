<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * Caché en memoria de los permisos resueltos del usuario.
     *
     * @var array<string, bool>|null
     */
    protected ?array $permisosResueltos = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot('deleted_at')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    public function docente(): HasOne
    {
        return $this->hasOne(Docente::class, 'user_id');
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('nombre', $roleName)->exists();
    }

    public function esDocente(): bool
    {
        return $this->hasRole('Docente');
    }

    public function docenteActivo(): bool
    {
        return (bool) $this->docente?->activo;
    }

    public function esAdministrador(): bool
    {
        return $this->hasRole('Administrador');
    }

    /**
     * Alias heredado usado por módulos anteriores (API/Biblia).
     */
    public function esAdmin(): bool
    {
        return $this->esAdministrador();
    }

    public function isAdmin(): bool
    {
        return $this->esAdministrador();
    }

    /**
     * Indica si el usuario (a través de alguno de sus roles) puede realizar
     * la acción indicada sobre el módulo. El rol Administrador siempre puede.
     *
     * Acepta dos formas:
     *   puede('miembros', 'ver')  -> módulo + acción (esquema nuevo)
     *   puede('miembros.ver')     -> permiso completo (módulos heredados)
     */
    public function puede(string $modulo, string ...$acciones): bool
    {
        $permisos = $this->resolverPermisos();

        if (! empty($permisos['*'])) {
            return true;
        }

        if ($acciones === []) {
            if (! empty($permisos[$modulo])) {
                return true;
            }

            // "modulo.gestionar" equivale a crear, editar o eliminar.
            if (str_ends_with($modulo, '.gestionar')) {
                $base = substr($modulo, 0, -strlen('.gestionar'));

                foreach (['crear', 'editar', 'eliminar'] as $accion) {
                    if (! empty($permisos["{$base}.{$accion}"])) {
                        return true;
                    }
                }
            }

            return false;
        }

        foreach ($acciones as $accion) {
            if (! empty($permisos["{$modulo}.{$accion}"])) {
                return true;
            }

            // Compatibilidad: un permiso ".gestionar" heredado concede acciones
            // de escritura sobre el módulo.
            if (in_array($accion, ['crear', 'editar', 'eliminar'], true)
                && ! empty($permisos["{$modulo}.gestionar"])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compatibilidad con los módulos que usan permisos como cadenas
     * ("miembros.ver", "miembros.gestionar", ...).
     */
    public function tienePermiso(string ...$permisos): bool
    {
        if ($permisos === []) {
            return false;
        }

        foreach ($permisos as $permiso) {
            if ($this->puede($permiso)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Lista de permisos efectivos en formato "modulo.accion".
     *
     * @return array<int, string>
     */
    public function permisos(): array
    {
        return collect($this->resolverPermisos())
            ->except('*')
            ->keys()
            ->values()
            ->all();
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
     * Limpia la caché de permisos (útil si los roles cambian en la petición).
     */
    public function limpiarPermisos(): void
    {
        $this->permisosResueltos = null;
    }

    public function olvidarPermisos(): void
    {
        $this->limpiarPermisos();
    }

    /**
     * @return array<string, bool>
     */
    private function resolverPermisos(): array
    {
        if ($this->permisosResueltos !== null) {
            return $this->permisosResueltos;
        }

        $mapa = [];

        foreach ($this->roles()->with('permisos')->get() as $role) {
            if ($role->nombre === 'Administrador') {
                return $this->permisosResueltos = ['*' => true];
            }

            foreach ($role->permisos as $permiso) {
                foreach (['ver', 'crear', 'editar', 'eliminar', 'buscar'] as $accion) {
                    if ($permiso->{$accion}) {
                        $mapa["{$permiso->modulo}.{$accion}"] = true;
                    }
                }
            }
        }

        return $this->permisosResueltos = $mapa;
    }
}
