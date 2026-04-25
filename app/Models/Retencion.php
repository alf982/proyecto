<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

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

    /** Aplicaciones polimórficas de esta retención */
    public function aplicaciones()
    {
        return $this->hasMany(RetencionAplicada::class, 'retencion_id');
    }

    // ── Scopes ──────────────────────────────────────────────────────

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /** Devuelve solo las retenciones que aplican a un módulo dado */
    public function scopeParaModulo(Builder $query, string $modulo): Builder
    {
        return $query->whereJsonContains('aplica_a', $modulo);
    }

    // ── Helpers ─────────────────────────────────────────────────────

    /**
     * Calcula el monto a retener dado un monto base.
     *
     * - porcentaje:     % sobre el monto_bruto o monto_neto de la factura.
     * - porcentaje_iva: % sobre el IVA ya incluido en la factura.
     *                   Ej: factura = 116,000 (base 100,000 + IVA 16%),
     *                   retención 75% del IVA = 75% × 16,000 = 12,000.
     * - monto_fijo:     valor fijo independiente del monto.
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

    /** Etiqueta legible para el tipo */
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

    /** Array de opciones de módulos disponibles */
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
