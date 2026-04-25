<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function scopeActivos($q)      { return $q->where('activo', true); }
    public function scopeAsignaciones($q) { return $q->where('tipo', 'asignacion'); }
    public function scopeDeducciones($q)  { return $q->where('tipo', 'deduccion'); }

    public function calcularMonto(float $salarioBase): float
    {
        if ($this->calculo === 'porcentaje') {
            return round($salarioBase * ($this->valor / 100), 2);
        }
        return (float) $this->valor;
    }
}
