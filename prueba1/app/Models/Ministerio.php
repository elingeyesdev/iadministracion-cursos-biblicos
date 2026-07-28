<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ministerio extends Model
{
    use SoftDeletes;

    protected $table = 'ministerios';

    protected $fillable = [
        'nombre',
        'descripcion',
        'id_lider'
    ];

    public function lider(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'id_lider');
    }

    public function actividades(): HasMany
    {
        return $this->hasMany(Actividad::class, 'id_ministerio');
    }

    public function egresos(): HasMany
    {
        return $this->hasMany(Egreso::class, 'id_ministerio');
    }
}
