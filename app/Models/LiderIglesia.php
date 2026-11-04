<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiderIglesia extends Model
{
    use SoftDeletes;

    protected $table = 'lideres_iglesia';

    protected $fillable = [
        'id_cargo',
        'nombre',
        'telefono',
        'fecha_inicio',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'id_cargo');
    }
}