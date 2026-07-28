<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ingreso extends Model
{
    protected $table = 'ingresos';

    protected $fillable = [
        'id_culto',
        'monto_total',
        'fecha',
        'tipo',
        'metodo_pago'
    ];

    public function culto(): BelongsTo
    {
        return $this->belongsTo(Culto::class, 'id_culto', 'id_culto');
    }
}
