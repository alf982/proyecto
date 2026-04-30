<?php

namespace App\Services\Presupuesto;

use App\Models\Causacion;
use App\Models\OrdenPago;
use App\Models\Pago;
use App\Traits\GuardaRetenciones;
use Illuminate\Support\Facades\DB;

class PagoService
{
    use GuardaRetenciones;

    /**
     * Registra un pago sobre una Causación aprobada.
     * Calcula retenciones y actualiza el estado de la causación.
     *
     * @throws \RuntimeException si la causación no está aprobada
     */
    public function crear(Causacion $causacion, array $datos, array $idsRetenciones = []): Pago
    {
        if ($causacion->estado !== 'aprobada') {
            throw new \RuntimeException('Solo se pueden pagar causaciones en estado Aprobada.');
        }

        $montoTotal  = (float) $datos['monto_pagado'];
        $montoSinIva = isset($datos['monto_sin_iva']) ? (float) $datos['monto_sin_iva'] : null;

        $totalRetenciones = $this->calcularTotalRetenciones($idsRetenciones, $montoTotal, $montoSinIva);
        $montoNeto        = round($montoTotal - $totalRetenciones, 2);

        return DB::transaction(function () use ($datos, $causacion, $idsRetenciones, $totalRetenciones, $montoTotal, $montoSinIva, $montoNeto) {
            $pago = Pago::create([
                'numero'              => Pago::generarNumero(now()->year),
                'causacion_id'        => $causacion->id,
                'ejercicio_fiscal_id' => $causacion->ejercicio_fiscal_id,
                'unidad_ejecutora_id' => $causacion->unidad_ejecutora_id,
                'beneficiario'        => $causacion->beneficiario,
                'rif_beneficiario'    => $causacion->rif_beneficiario,
                'tipo_pago'           => $datos['tipo_pago'],
                'numero_referencia'   => $datos['numero_referencia'] ?? null,
                'banco'               => $datos['banco'] ?? null,
                'cuenta_bancaria'     => $datos['cuenta_bancaria'] ?? null,
                'monto_pagado'        => $montoNeto,
                'fecha_pago'          => $datos['fecha_pago'],
                'concepto'            => $datos['concepto'],
                'estado'              => 'procesado',
                'observaciones'       => $datos['observaciones'] ?? null,
                'created_by'         => auth()->id(),
            ]);

            $this->guardarRetenciones($pago, $idsRetenciones, $montoTotal, $montoSinIva);

            $causacion->update([
                'estado'          => 'pagada',
                'fecha_pago'      => $datos['fecha_pago'],
                'monto_retencion' => $totalRetenciones,
            ]);

            return $pago;
        });
    }

    /**
     * Procesa un pago en estado 'pendiente' (asigna referencia bancaria).
     *
     * @throws \RuntimeException si el pago no está pendiente
     */
    public function procesar(Pago $pago, array $datos): void
    {
        if (!$pago->esPendiente()) {
            throw new \RuntimeException('Solo se procesan pagos en estado Pendiente.');
        }

        // Cargar relación antes de la transacción para evitar lazy loading
        $pago->load('causacion');

        DB::transaction(function () use ($pago, $datos) {
            $pago->update([
                'estado'            => 'procesado',
                'numero_referencia' => $datos['numero_referencia'] ?? null,
                'banco'             => $datos['banco'] ?? null,
                'cuenta_bancaria'   => $datos['cuenta_bancaria'] ?? null,
                'fecha_pago'        => $datos['fecha_pago'],
            ]);

            if ($pago->causacion) {
                $pago->causacion->update(['estado' => 'pagada', 'fecha_pago' => $datos['fecha_pago']]);
            }

            if ($pago->orden_pago_id) {
                OrdenPago::where('id', $pago->orden_pago_id)->update([
                    'estado'              => 'pagada',
                    'numero_referencia'   => $datos['numero_referencia'] ?? null,
                    'banco'               => $datos['banco'] ?? null,
                    'cuenta_bancaria_num' => $datos['cuenta_bancaria'] ?? null,
                    'fecha_pago'          => $datos['fecha_pago'],
                ]);
            }
        });
    }

    /**
     * Anula un pago y revierte la causación a 'aprobada'.
     *
     * @throws \RuntimeException si ya está anulado
     */
    public function anular(Pago $pago, string $motivo): void
    {
        if ($pago->esAnulado()) {
            throw new \RuntimeException('El pago ya está anulado.');
        }

        $pago->load('causacion');

        DB::transaction(function () use ($pago, $motivo) {
            $pago->update(['estado' => 'anulado', 'motivo_anulacion' => $motivo]);

            if ($pago->causacion) {
                $pago->causacion->update(['estado' => 'aprobada', 'fecha_pago' => null]);
            }
        });
    }
}
