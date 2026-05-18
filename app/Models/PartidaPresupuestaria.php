<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Modelo de Partida Presupuestaria
 * 
 * Representa un rubro en el catálogo de cuentas de presupuesto.
 * Implementa la estructura jerárquica del código ONAPRE (Genérica, 
 * Específica, Sub-específica) y mantiene los montos acumulados
 * para reportes rápidos (Aprobado, Vigente, Saldo Actual).
 */
class PartidaPresupuestaria extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'partidas_presupuestarias';

    protected $fillable = [
        'codigo', 
        'generica', 'especifica', 'subespecifica', // Componentes del código ONAPRE
        'descripcion', 'tipo', 'cuenta_bancaria_id',
        'monto_aprobado', 'monto_vigente', 'saldo_actual', // Contadores financieros
        'activo', 'observaciones',
    ];

    protected $casts = [
        'activo'          => 'boolean',
        'monto_aprobado'  => 'decimal:2',
        'monto_vigente'   => 'decimal:2',
        'saldo_actual'    => 'decimal:2',
    ];

    // ── Relaciones ────────────────────────────────────────────────
    
    /**
     * Historial de asignaciones de crédito (Presupuesto Ordinario) a esta partida.
     */
    public function creditosPresupuestarios()
    {
        return $this->hasMany(CreditoPresupuestario::class);
    }

    /**
     * Historial de Modificaciones Presupuestarias (Traspasos, Créditos Adicionales)
     * donde esta partida es el origen.
     */
    public function movimientos()
    {
        return $this->hasMany(MovimientoPartida::class, 'partida_presupuestaria_id');
    }

    /**
     * Cuenta Bancaria física de la que se debitarán los fondos asociados a esta partida.
     */
    public function cuentaBancaria()
    {
        return $this->belongsTo(CuentaBancaria::class);
    }

    // ── Scopes ────────────────────────────────────────────────────
    
    /**
     * Filtra solo las partidas activas (disponibles para comprometer).
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
}
