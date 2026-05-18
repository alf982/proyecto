<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Cargo (Estructura Organizativa y Salarial)
 * 
 * Define la estructura de puestos dentro de la institución.
 * Cada cargo determina el tabulador o salario base inicial para los empleados
 * que lo ocupan. Sirve como base para el cálculo de nómina regular.
 */
class Cargo extends Model
{
    protected $table = 'cargos';

    protected $fillable = ['codigo', 'nombre', 'nivel', 'salario_base', 'activo'];

    protected $casts = ['salario_base' => 'decimal:2', 'activo' => 'boolean'];

    // ── Relaciones ──────────────────────────────────────────────────
    
    /** Empleados que actualmente ocupan este cargo */
    public function empleados() { return $this->hasMany(Empleado::class); }

    // ── Scopes ──────────────────────────────────────────────────────
    
    public function scopeActivos($q) { return $q->where('activo', true); }
}
