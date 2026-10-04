<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Curso extends Model
{
    protected $table = 'cursos';

    protected $fillable = [
        'nombre',
        'f_inicio',
        'f_fin',
        'cupo_max',
        'tiene_pago',
        'monto_inscripcion'
    ];

    protected $casts = [
        'tiene_pago' => 'boolean'
    ];

    public function sesiones(): HasMany
    {
        return $this->hasMany(Sesion::class, 'id_curso');
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class, 'curso_id');
    }

    public function docentes(): BelongsToMany
    {
        return $this->belongsToMany(Docente::class, 'docente_curso', 'id_curso', 'id_docente')
                    ->withTimestamps();
    }
}
