<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Docente extends Model
{
    protected $table = 'docentes';

    protected $fillable = [
        'id_miembro',
        'especialidad',
        'titulo',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function miembro(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'id_miembro');
    }

    public function cursos(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'docente_curso', 'id_docente', 'id_curso')
                    ->withTimestamps();
    }
}
