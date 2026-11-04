<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeguimientoActividad extends Model
{
    protected $table = 'seguimiento_actividades';

    protected $fillable = [
        'id_actividad',
        'fecha_registro',
        'estado',
        'observacion',
        'responsable',
        'porcentaje_avance'
    ];

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'id_actividad');
    }
}
