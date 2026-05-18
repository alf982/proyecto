<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadoBonificacion extends Model
{
    protected $table = 'empleado_bonificaciones';

    protected $fillable = [
        'empleado_id', 'concepto_nomina_id', 'monto', 'activo', 'observaciones', 'registrado_por'
    ];

    protected $casts = [
        'monto'  => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function empleado() {
        return $this->belongsTo(Empleado::class);
    }

    public function concepto() {
        return $this->belongsTo(ConceptoNomina::class, 'concepto_nomina_id');
    }

    public function registradoPor() {
        return $this->belongsTo(User::class, 'registrado_por');
    }
    
    /**
     * Retorna el monto real a aplicar.
     * Si tiene monto personalizado (override), usa ese;
     * de lo contrario, evalúa el cálculo del concepto base contra el sueldo del trabajador.
     */
    public function obtenerMontoReal(float $salarioBase): float
    {
        if (!is_null($this->monto)) {
            return (float) $this->monto;
        }
        return $this->concepto->calcularMonto($salarioBase);
    }
}
