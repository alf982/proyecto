<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Modelo UnidadEjecutora
 * 
 * Representa la estructura organizativa de la institución (Departamentos, Direcciones).
 * Es fundamental para la segregación del presupuesto (Créditos Presupuestarios) y
 * la agrupación del personal (Usuarios, Empleados).
 * Soporta jerarquías de N niveles (Padre -> Hijos).
 */
class UnidadEjecutora extends Model implements Auditable
{
    protected $table = 'unidades_ejecutoras';

    // Permite "eliminar" registros sin borrarlos de la BD (crea fecha en deleted_at)
    use SoftDeletes;
    
    // Registra el historial de cambios en la tabla 'audits'
    use \OwenIt\Auditing\Auditable;

    /**
     * @var array<int, string>
     */
    protected $fillable = ['codigo', 'nombre', 'descripcion', 'parent_id', 'activo'];

    /**
     * En Laravel 11 se recomienda protected function casts(): array, 
     * pero $casts array sigue siendo válido.
     */
    protected $casts = ['activo' => 'boolean'];

    /**
     * RELACIÓN: Unidad Padre (BelongsTo)
     * Referencia a la unidad jerárquicamente superior.
     */
    public function parent()
    {
        return $this->belongsTo(UnidadEjecutora::class, 'parent_id');
    }

    /**
     * RELACIÓN: Unidades Hijas (HasMany)
     * Departamentos o coordinaciones subordinadas a esta unidad.
     */
    public function hijos()
    {
        return $this->hasMany(UnidadEjecutora::class, 'parent_id');
    }

    /**
     * RELACIÓN: Usuarios (HasMany)
     * Personal del sistema adscrito a este departamento.
     */
    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    /**
     * RELACIÓN: Proyectos SIA (HasMany)
     * Proyectos presupuestarios que están bajo la responsabilidad de esta unidad.
     */
    public function proyectos()
    {
        return $this->hasMany(ProyectoSia::class);
    }

    /**
     * RELACIÓN: Créditos Presupuestarios (HasMany)
     * Fondos de presupuesto asignados directamente a este departamento.
     */
    public function creditosPresupuestarios()
    {
        return $this->hasMany(CreditoPresupuestario::class);
    }

    /**
     * SCOPE: Unidades Activas
     * Permite filtrar: UnidadEjecutora::activas()->get()
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * ACCESSOR: Nombre Completo
     * Retorna el formato: [CODIGO] Nombre de la Dirección
     */
    public function getNombreCompletoAttribute(): string
    {
        return "[{$this->codigo}] {$this->nombre}";
    }
}
