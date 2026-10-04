<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    use SoftDeletes;

    protected $table = 'asistencias_sesion';

    protected $fillable = [
        'miembro_id',
        'sesion_id',
        'user_id',
        'estado',
        'observacion',
    ];

    public function sesion(): BelongsTo
    {
        return $this->belongsTo(Sesion::class, 'sesion_id');
    }

    public function miembro(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'miembro_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePorCurso($query, int $cursoId)
    {
        return $query->whereHas('sesion', fn ($q) => $q->where('id_curso', $cursoId));
    }

    public function scopePorMiembro($query, int $miembroId)
    {
        return $query->where('miembro_id', $miembroId);
    }
}