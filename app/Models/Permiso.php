<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permiso extends Model
{
    protected $table = 'permisos';

    protected $fillable = [
        'role_id',
        'modulo',
        'ver',
        'crear',
        'editar',
        'eliminar',
        'buscar',
    ];

    protected function casts(): array
    {
        return [
            'ver' => 'boolean',
            'crear' => 'boolean',
            'editar' => 'boolean',
            'eliminar' => 'boolean',
            'buscar' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Categoría (grupo) a la que pertenece el módulo de este permiso.
     */
    public function categoria(): string
    {
        foreach (config('permisos.categorias', []) as $clave => $modulos) {
            if (array_key_exists($this->modulo, $modulos)) {
                return $clave;
            }
        }

        return 'otros';
    }
}
