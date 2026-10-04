<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inscripcion extends Model
{
    use SoftDeletes;

    protected $table = 'inscripciones';

    protected $fillable = [
        'miembro_id',
        'curso_id',
        'fecha_inscripcion',
        'estado',
    ];

    public function miembro(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'miembro_id');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public static function getMiembrosDisponibles(int $cursoId)
    {
        return Miembro::where('estado', 'activo')
            ->whereDoesntHave('inscripciones', fn ($q) => $q->where('curso_id', $cursoId))
            ->orderBy('nombre')
            ->get();
    }
}