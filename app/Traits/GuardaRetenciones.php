<?php

namespace App\Traits;

use App\Models\Retencion;
use App\Models\RetencionAplicada;
use Illuminate\Database\Eloquent\Model;

trait GuardaRetenciones
{
    /**
     * Persiste las retenciones seleccionadas en el formulario para un modelo polimórfico.
     *
     * @param  Model  $modelo       — El registro al que se aplicarán (Causacion, Pago, etc.)
     * @param  array  $ids          — IDs de retenciones seleccionadas
     * @param  float  $montoTotal   — Monto TOTAL con IVA (para tipo porcentaje_iva)
     * @param  float|null $montoBase — Monto BASE sin IVA (para tipo porcentaje/ISLR)
     *                                 Si null, se usa $montoTotal para todo.
     */
    protected function guardarRetenciones(Model $modelo, array $ids, float $montoTotal, ?float $montoBase = null): void
    {
        if (empty($ids)) {
            // Limpiar retenciones anteriores si las hubiera
            $modelo->retenciones()->delete();
            return;
        }

        // Si no se pasa montoBase, usamos el total para todo (comportamiento legado)
        $baseNeta      = $montoBase ?? $montoTotal;
        $retenciones   = Retencion::activas()->whereIn('id', $ids)->get();
        $montoAcumulado = $baseNeta;  // se reduce progresivamente para retenciones tipo monto_neto

        foreach ($retenciones as $ret) {
            if ($ret->tipo === 'porcentaje_iva') {
                // Retención IVA: calcular sobre el TOTAL (extrae IVA del bruto)
                $base  = $montoTotal;
                $monto = $ret->calcularMonto($montoTotal);
            } else {
                // ISLR y similares: calcular sobre la BASE NETA
                $base  = $ret->base_calculo === 'monto_neto' ? $montoAcumulado : $baseNeta;
                $monto = $ret->calcularMonto($base);
                $montoAcumulado -= $monto;
            }

            RetencionAplicada::updateOrCreate(
                [
                    'retencionable_type' => get_class($modelo),
                    'retencionable_id'   => $modelo->id,
                    'retencion_id'       => $ret->id,
                ],
                [
                    'monto_base'          => $base,
                    'monto_retenido'      => $monto,
                    'porcentaje_aplicado' => in_array($ret->tipo, ['porcentaje', 'porcentaje_iva']) ? $ret->porcentaje : null,
                ]
            );
        }

        // Eliminar retenciones que fueron desmarcadas
        $modelo->retenciones()
            ->whereNotIn('retencion_id', $ids)
            ->delete();
    }

    /**
     * Suma el total retenido de un array de IDs.
     *
     * @param  float  $montoTotal  — Total con IVA (para porcentaje_iva)
     * @param  float|null $montoBase — Base sin IVA (para ISLR y similares)
     */
    protected function calcularTotalRetenciones(array $ids, float $montoTotal, ?float $montoBase = null): float
    {
        if (empty($ids)) {
            return 0.0;
        }

        $baseNeta       = $montoBase ?? $montoTotal;
        $retenciones    = Retencion::activas()->whereIn('id', $ids)->get();
        $total          = 0.0;
        $montoAcumulado = $baseNeta;

        foreach ($retenciones as $ret) {
            if ($ret->tipo === 'porcentaje_iva') {
                $monto = $ret->calcularMonto($montoTotal);
            } else {
                $base  = $ret->base_calculo === 'monto_neto' ? $montoAcumulado : $baseNeta;
                $monto = $ret->calcularMonto($base);
                $montoAcumulado -= $monto;
            }
            $total += $monto;
        }

        return round($total, 2);
    }
}
