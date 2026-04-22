<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Miembro extends Model
{
    protected $table = 'miembro';

    protected $fillable = [
        'nombre',
        'apellido',
        'correo',
        'telefono',
        'rol',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'id_responsable');
    }
}
