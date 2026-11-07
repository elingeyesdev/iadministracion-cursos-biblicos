<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cargo extends Model
{
    use SoftDeletes;

    protected $table = 'cargos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'nivel_jerarquico',
    ];

    public function lideresIglesia(): HasMany
    {
        return $this->hasMany(LiderIglesia::class, 'id_cargo');
    }
}
