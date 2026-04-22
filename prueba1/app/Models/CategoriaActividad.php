<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaActividad extends Model
{
    protected $table = 'categoriaactividad';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];
}