<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Culto extends Model {
    protected $table    = 'cultos';
    protected $primaryKey = 'id_culto';

    protected $fillable = [
        'nombre', 'dia_semana', 'hora', 'descripcion'
    ];

    // Un culto tiene muchos ingresos
    public function ingresos() {
        return $this->hasMany(Ingreso::class, 'id_culto', 'id_culto');
    }

    // Total recaudado en este culto
    public function totalRecaudado(): float {
        return $this->ingresos()->sum('monto_total');
    }
}
