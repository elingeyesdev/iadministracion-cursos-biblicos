<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = ['nombre', 'descripcion', 'estado'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->withPivot('fecha_asignacion', 'estado')
            ->withTimestamps();
    }

    /**
     * Permisos asignados al rol.
     */
    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'permiso_role')
            ->withTimestamps();
    }

    public function tienePermiso(string $nombre): bool
    {
        return $this->permisos()->where('permisos.nombre', $nombre)->exists();
    }
}
