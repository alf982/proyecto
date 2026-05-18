<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/**
 * Modelo de Retención Fiscal
 * 
 * Gestiona el catálogo centralizado de impuestos y retenciones aplicables
 * a diferentes módulos del sistema (Causaciones, Pagos, Órdenes de Compra, Nómina).
 * Define reglas de cálculo dinámicas (porcentajes sobre base bruta/neta, porcentajes
 * sobre el IVA, o montos fijos).
 */
class Retencion extends Model
{
    use SoftDeletes;

    protected $table = 'retenciones';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'porcentaje',
        'alicuota_iva',
        'monto_fijo',
        'aplica_a',
        'base_calculo',
        'obligatoria',
        'activo',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'porcentaje'  => 'decimal:4',
            'alicuota_iva'=> 'decimal:2',
            'monto_fijo'  => 'decimal:2',
            'aplica_a'    => 'array',
            'obligatoria' => 'boolean',
            'activo'      => 'boolean',
        ];
    }

    // ── Relaciones ──────────────────────────────────────────────────

    /** Aplicaciones polimórficas de esta retención (Dónde y a quién se aplicó) */
    public function aplicaciones()
    {
        return $this->hasMany(RetencionAplicada::class, 'retencion_id');
    }

    // ── Scopes ──────────────────────────────────────────────────────

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /** Devuelve solo las retenciones que aplican a un módulo dado (ej. 'causacion') */
    public function scopeParaModulo(Builder $query, string $modulo): Builder
    {
        return $query->whereJsonContains('aplica_a', $modulo);
    }

    // ── Lógica de Negocio y Helpers ─────────────────────────────────

    /**
     * Calcula el monto a retener dado un monto base (totalConIva).
     *
     * Reglas de negocio:
     * - `porcentaje_iva`: Extrae el IVA usando la `alicuota_iva` configurada, y a ese IVA le aplica el `porcentaje`.
     *                   Ej: Total factura = 116, alícuota = 16%. IVA extraído = 16. Retención = 75% de 16 = 12.
     * - `porcentaje`:     Aplica el porcentaje directamente al monto proveído.
     * - `monto_fijo`:     Retorna el valor fijo independientemente del monto base.
     */
    public function calcularMonto(float $totalConIva): float
    {
        if ($this->tipo === 'porcentaje_iva') {
            $alicuota       = (float) $this->alicuota_iva / 100;          // ej. 0.16
            $baseImponible  = $totalConIva / (1 + $alicuota);             // ej. 100,000
            $montoIva       = $baseImponible * $alicuota;                 // ej. 16,000
            return round($montoIva * ((float) $this->porcentaje / 100), 2); // ej. 12,000
        }

        if ($this->tipo === 'porcentaje') {
            return round($totalConIva * ((float) $this->porcentaje / 100), 2);
        }

        return round((float) $this->monto_fijo, 2);
    }

    /** Etiqueta legible para el tipo de retención en la UI */
    public function getTipoLabelAttribute(): string
    {
        return match($this->tipo) {
            'porcentaje'     => number_format((float) $this->porcentaje, 2) . '%',
            'porcentaje_iva' => number_format((float) $this->porcentaje, 0) . '% del IVA (' . number_format((float) $this->alicuota_iva, 0) . '%)',
            'monto_fijo'     => 'Bs. ' . number_format((float) $this->monto_fijo, 2),
            default          => $this->tipo,
        };
    }

    /** Badge CSS según estado */
    public function getEstadoBadgeAttribute(): string
    {
        return $this->activo ? 'badge-active' : 'badge-danger';
    }

    /** Array de opciones de módulos disponibles (Diccionario para UI) */
    public static function modulosDisponibles(): array
    {
        return [
            'causacion'   => 'Causaciones',
            'pago'        => 'Pagos',
            'orden_compra'=> 'Órdenes de Compra',
            'nomina'      => 'Nóminas',
        ];
    }
}
