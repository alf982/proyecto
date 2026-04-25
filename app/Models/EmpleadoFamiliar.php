<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadoFamiliar extends Model
{
    protected $table = 'empleado_familiares';

    protected $fillable = [
        'empleado_id', 'nombre_completo', 'parentesco',
        'cedula', 'fecha_nacimiento', 'telefono',
        'es_carga_familiar', 'observaciones',
    ];

    protected $casts = [
        'fecha_nacimiento'  => 'date',
        'es_carga_familiar' => 'boolean',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    /** Edad calculada en años enteros */
    public function getEdadAttribute(): ?int
    {
        return $this->fecha_nacimiento?->diffInYears(now());
    }

    public static function parentescoLabel(string $p): string
    {
        return match($p) {
            'hijo'     => 'Hijo/a',
            'conyuge'  => 'Cónyuge',
            'padre'    => 'Padre',
            'madre'    => 'Madre',
            'hermano'  => 'Hermano/a',
            'otro'     => 'Otro',
            default    => ucfirst($p),
        };
    }
}
