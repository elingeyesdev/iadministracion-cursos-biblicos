<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsignacionActividad extends Model
{
    protected $table = 'asignacionactividad';

    protected $fillable = [
        'id_actividad',
        'id_miembro',
        'rol_en_actividad',
        'fecha_asignacion',
    ];

    protected $casts = [
        'fecha_asignacion' => 'date',
    ];

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'id_actividad');
    }

    public function miembro()
    {
        return $this->belongsTo(Miembro::class, 'id_miembro');
    }
}

