<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sesion extends Model
{
    protected $table = 'sesiones';

    protected $fillable = [
        'id_curso',
        'fecha',
        'hora',
        'tema',
        'observacion'
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'id_curso');
    }

    public function miembros(): BelongsToMany
    {
        return $this->belongsToMany(Miembro::class, 'registra', 'id_sesion', 'id_miembro')
                    ->withPivot('estado', 'observacion')
                    ->withTimestamps();
    }
}
