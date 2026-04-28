<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seguimiento extends Model
{
    protected $table = 'seguimiento';

    protected $fillable = [
        'id_actividad',
        'fecha',
        'comentario',
        'porcentaje_avance',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'porcentaje_avance' => 'integer',
    ];

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'id_actividad');
    }
}

