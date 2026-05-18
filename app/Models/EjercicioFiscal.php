<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Modelo Ejercicio Fiscal
 * 
 * Representa un año presupuestario. Todos los recursos financieros
 * (Proyectos, Créditos, Compromisos, Pagos) deben estar atados a un ejercicio.
 * Utiliza SoftDeletes para retener registros borrados temporalmente y
 * Auditable para guardar trazas de auditoría sobre quién activó/cerró el año.
 */
class EjercicioFiscal extends Model implements Auditable
{
    protected $table = 'ejercicios_fiscales';

    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'anio', 'fecha_inicio', 'fecha_fin', 'estado',
        'observaciones', 'creado_por', 'cerrado_por', 'fecha_cierre',
    ];

    protected $casts = [
        'fecha_inicio'  => 'date',
        'fecha_fin'     => 'date',
        'fecha_cierre'  => 'datetime',
        'anio'          => 'integer',
    ];

    /**
     * Usuario que registró el ejercicio en el sistema.
     */
    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /**
     * Usuario que ejecutó la acción de cierre definitivo del año.
     */
    public function cerradoPor()
    {
        return $this->belongsTo(User::class, 'cerrado_por');
    }

    /**
     * Proyectos de inversión o acciones centralizadas formuladas para este año.
     */
    public function proyectos()
    {
        return $this->hasMany(ProyectoSia::class);
    }

    /**
     * Presupuesto de gastos asignado a las partidas durante este ejercicio.
     */
    public function creditosPresupuestarios()
    {
        return $this->hasMany(CreditoPresupuestario::class);
    }

    /**
     * Scope para buscar rápidamente el ejercicio actualmente vigente.
     * Ejemplo de uso: EjercicioFiscal::activo()->first()
     */
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Verifica si el ejercicio fiscal aún acepta modificaciones estructurales.
     */
    public function puedeModificarse(): bool
    {
        return in_array($this->estado, ['borrador', 'activo']);
    }

    /**
     * Devuelve la clase de color (badge) para la UI según el estado actual.
     * (Usado en las vistas Blade).
     */
    public function getEstadoBadgeAttribute(): string
    {
        return match($this->estado) {
            'borrador' => 'warn',
            'activo'   => 'active',
            'cerrado'  => 'danger',
            default    => 'warn',
        };
    }
}
