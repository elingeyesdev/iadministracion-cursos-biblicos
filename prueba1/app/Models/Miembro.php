<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Miembro extends Model
{
    use SoftDeletes;

    protected $table = 'miembros';

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'f_nacimiento',
        'estado',
        'direccion',
        'fecha_ingreso',
        'sexo'
    ];

    public function docente(): HasOne
    {
        return $this->hasOne(Docente::class, 'id_miembro');
    }

    public function sesiones(): BelongsToMany
    {
        return $this->belongsToMany(Sesion::class, 'registra', 'id_miembro', 'id_sesion')
                    ->withPivot('estado', 'observacion')
                    ->withTimestamps();
    }

    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class, 'id_miembro');
    }
}
