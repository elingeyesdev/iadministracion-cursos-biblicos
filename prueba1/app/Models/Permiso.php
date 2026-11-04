<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permiso extends Model
{
    protected $table = 'permisos';

    protected $fillable = ['nombre', 'descripcion', 'modulo'];

    /**
     * Categoría a la que pertenece el permiso en la pantalla de permisos.
     *
     * Primero revisa las excepciones por permiso y luego la categoría por
     * defecto del módulo definida en config/permisos.php.
     */
    public function categoria(): string
    {
        // Se consulta el arreglo completo porque los nombres de permiso
        // contienen puntos (ej. "cultos.ver") y config() los interpretaría
        // como niveles anidados.
        $excepciones = config('permisos.categorias_permisos', []);

        return $excepciones[$this->nombre]
            ?? config("permisos.modulos.{$this->modulo}.categoria")
            ?? 'otros';
    }

    /**
     * Roles que tienen este permiso.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permiso_role')
            ->withTimestamps();
    }
}
