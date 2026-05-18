<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Concepto de Nómina (Asignaciones y Deducciones)
 * 
 * Define las reglas de cálculo y montos para los diferentes ítems 
 * que componen un recibo de pago (ej: Prima por hijos, IVSS, FAOV, Paro Forzoso).
 * 
 * Puede ser un monto 'fijo' o un 'porcentaje' calculado sobre el salario base.
 * Los conceptos marcados como 'es_obligatorio' se aplican automáticamente
 * a todos los empleados elegibles al generar una nueva nómina.
 */
class ConceptoNomina extends Model
{
    protected $table = 'conceptos_nomina';

    protected $fillable = [
        'codigo', 'nombre', 'tipo', 'calculo', 'valor', 'aplica_a',
        'es_obligatorio', 'activo', 'descripcion',
    ];

    protected $casts = [
        'valor'          => 'decimal:4',
        'es_obligatorio' => 'boolean',
        'activo'         => 'boolean',
    ];

    // ── Scopes ──────────────────────────────────────────────────────
    
    public function scopeActivos($q)      { return $q->where('activo', true); }
    public function scopeAsignaciones($q) { return $q->where('tipo', 'asignacion'); }
    public function scopeDeducciones($q)  { return $q->where('tipo', 'deduccion'); }

    // ── Lógica de Negocio ───────────────────────────────────────────
    
    /**
     * Motor de cálculo: Determina el monto monetario real del concepto.
     * Si es porcentaje, lo extrae del salario base provisto.
     */
    public function calcularMonto(float $salarioBase): float
    {
        if ($this->calculo === 'porcentaje') {
            return round($salarioBase * ($this->valor / 100), 2);
        }
        return (float) $this->valor;
    }
}
