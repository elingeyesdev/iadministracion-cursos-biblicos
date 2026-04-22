<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $table = 'actividad';

    protected $fillable = [
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'id_categoria',
        'id_responsable',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaActividad::class, 'id_categoria');
    }

    public function responsable()
    {
        return $this->belongsTo(Miembro::class, 'id_responsable');
    }
}
