<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contrato extends Model
{
    protected $table = 'contratos';

    protected $fillable = [
        'id_miembro',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'monto',
        'estado'
    ];

    public function miembro(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'id_miembro');
    }

    public function egresos(): HasMany
    {
        return $this->hasMany(Egreso::class, 'id_contrato');
    }
}
