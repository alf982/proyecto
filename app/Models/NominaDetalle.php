<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NominaDetalle extends Model
{
    protected $table = 'nominas_detalle';

    protected $fillable = [
        'nomina_id', 'empleado_id', 'salario_base',
        'total_asignaciones', 'total_deducciones', 'neto', 'conceptos_aplicados',
    ];

    protected $casts = [
        'salario_base'       => 'decimal:2',
        'total_asignaciones' => 'decimal:2',
        'total_deducciones'  => 'decimal:2',
        'neto'               => 'decimal:2',
        'conceptos_aplicados'=> 'array',
    ];

    public function nomina()   { return $this->belongsTo(Nomina::class); }
    public function empleado() { return $this->belongsTo(Empleado::class); }
}
