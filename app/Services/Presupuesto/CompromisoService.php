<?php

namespace App\Services\Presupuesto;

use App\Models\Causacion;
use App\Models\Compromiso;
use App\Models\EjercicioFiscal;
use App\Models\MovimientoPartida;
use App\Models\PartidaPresupuestaria;
use Illuminate\Support\Facades\DB;

class CompromisoService
{
    /**
     * Crea el Compromiso + el MovimientoPartida de tipo 'compromiso'.
     * Devuelve el Compromiso recién creado.
     *
     * @throws \RuntimeException si el monto supera el saldo disponible
     */
    public function crear(array $datos, string $nombreBen, string|null $rifBen): Compromiso
    {
        $partida    = PartidaPresupuestaria::findOrFail($datos['partida_presupuestaria_id']);
        $disponible = (float) $partida->saldo_actual;
        $monto      = (float) $datos['monto'];

        if ($monto > $disponible) {
            throw new \RuntimeException(
                'El monto supera el saldo disponible en la partida: Bs. ' . number_format($disponible, 2)
            );
        }

        $ejercicio = EjercicioFiscal::where('estado', 'activo')->first();

        return DB::transaction(function () use ($datos, $partida, $ejercicio, $nombreBen, $rifBen, $monto) {
            $saldoAnt  = (float) $partida->saldo_actual;
            $saldoPost = $saldoAnt - $monto;

            $compromiso = Compromiso::create([
                'numero'                    => Compromiso::generarNumero(now()->year),
                'ejercicio_fiscal_id'       => $ejercicio?->id,
                'unidad_ejecutora_id'       => $datos['unidad_ejecutora_id'],
                'partida_presupuestaria_id' => $partida->id,
                'proyecto_id'               => $datos['proyecto_id'] ?? null,
                'beneficiario_id'           => $datos['beneficiario_id'] ?? null,
                'beneficiario'              => $nombreBen,
                'rif_beneficiario'          => $rifBen,
                'concepto'                  => $datos['concepto'],
                'monto_sin_iva'             => $datos['monto_sin_iva'] ?? null,
                'alicuota_iva'              => $datos['alicuota_iva'] ?? null,
                'monto'                     => $monto,
                'tipo_documento'            => $datos['tipo_documento'] ?? null,
                'numero_documento'          => $datos['numero_documento'] ?? null,
                'fecha_documento'           => $datos['fecha_documento'] ?? null,
                'descripcion_documento'     => $datos['descripcion_documento'] ?? null,
                'fecha_compromiso'          => $datos['fecha_compromiso'],
                'fecha_vencimiento'         => $datos['fecha_vencimiento'] ?? null,
                'observaciones'             => $datos['observaciones'] ?? null,
                'estado'                    => 'borrador',
                'created_by'               => auth()->id(),
            ]);

            MovimientoPartida::create([
                'numero'                    => MovimientoPartida::generarNumero(now()->year),
                'partida_presupuestaria_id' => $partida->id,
                'cuenta_bancaria_id'        => $partida->cuenta_bancaria_id,
                'ejercicio_fiscal_id'       => $ejercicio?->id,
                'tipo'                      => 'compromiso',
                'concepto'                  => 'Compromiso ' . $compromiso->numero . ' — ' . $nombreBen,
                'monto'                     => $monto,
                'fecha_movimiento'          => $datos['fecha_compromiso'],
                'referencia'                => $compromiso->numero,
                'saldo_anterior'            => $saldoAnt,
                'saldo_posterior'           => $saldoPost,
                'estado'                    => 'confirmado',
                'creado_por'               => auth()->id(),
            ]);

            $partida->update(['saldo_actual' => $saldoPost]);

            return $compromiso;
        });
    }

    /**
     * Aprueba el Compromiso y genera la Causación en borrador.
     */
    public function aprobar(Compromiso $compromiso): void
    {
        if (!$compromiso->esBorrador()) {
            throw new \RuntimeException('Solo se pueden aprobar compromisos en Borrador.');
        }

        DB::transaction(function () use ($compromiso) {
            $compromiso->update([
                'estado'           => 'aprobado',
                'fecha_aprobacion' => now()->toDateString(),
                'aprobado_por'     => auth()->id(),
            ]);

            Causacion::create([
                'compromiso_id'             => $compromiso->id,
                'numero'                    => Causacion::generarNumero(now()->year),
                'ejercicio_fiscal_id'       => $compromiso->ejercicio_fiscal_id,
                'unidad_ejecutora_id'       => $compromiso->unidad_ejecutora_id,
                'partida_presupuestaria_id' => $compromiso->partida_presupuestaria_id,
                'proyecto_id'               => $compromiso->proyecto_id,
                'beneficiario'              => $compromiso->beneficiario,
                'rif_beneficiario'          => $compromiso->rif_beneficiario,
                'tipo_documento'            => $compromiso->tipo_documento ?? 'otro',
                'numero_documento'          => $compromiso->numero_documento,
                'fecha_documento'           => $compromiso->fecha_documento,
                'descripcion_documento'     => $compromiso->descripcion_documento,
                'concepto'                  => $compromiso->concepto,
                'fecha_causacion'           => $compromiso->fecha_compromiso,
                'monto_sin_iva'             => $compromiso->monto_sin_iva,
                'alicuota_iva'              => $compromiso->alicuota_iva,
                'monto_causado'             => $compromiso->monto,
                'monto_retencion'           => 0,
                'observaciones'             => $compromiso->observaciones,
                'estado'                    => 'borrador',
                'created_by'               => auth()->id(),
            ]);
        });
    }

    /**
     * Anula el Compromiso y sus causaciones en borrador/aprobadas.
     * Devuelve el saldo a la partida.
     */
    public function anular(Compromiso $compromiso, string $motivo): void
    {
        if ($compromiso->esAnulado()) {
            throw new \RuntimeException('El compromiso ya está anulado.');
        }

        DB::transaction(function () use ($compromiso, $motivo) {
            $compromiso->causaciones()
                ->whereIn('estado', ['borrador', 'aprobada'])
                ->update([
                    'estado'           => 'anulada',
                    'motivo_anulacion' => 'Anulado con el compromiso: ' . $motivo,
                ]);

            $compromiso->update([
                'estado'           => 'anulado',
                'motivo_anulacion' => $motivo,
            ]);

            if ($compromiso->partida) {
                $compromiso->partida->increment('saldo_actual', (float) $compromiso->monto);
            }
        });
    }

    /**
     * Actualiza un compromiso en Borrador y sincroniza la causación en borrador si existe.
     *
     * @throws \RuntimeException si el incremento supera el saldo disponible
     */
    public function actualizar(Compromiso $compromiso, array $datos): void
    {
        if (!$compromiso->esBorrador()) {
            throw new \RuntimeException('Solo se pueden editar compromisos en Borrador.');
        }

        $diferencia = (float) $datos['monto'] - (float) $compromiso->monto;

        if ($diferencia > 0 && $compromiso->partida) {
            $saldoDisponible = (float) $compromiso->partida->saldo_actual;
            if ($diferencia > $saldoDisponible) {
                throw new \RuntimeException(
                    'El incremento supera el saldo disponible: Bs. ' . number_format($saldoDisponible, 2)
                );
            }
        }

        DB::transaction(function () use ($datos, $compromiso, $diferencia) {
            $compromiso->update([
                'beneficiario'          => ucwords(strtolower($datos['beneficiario'])),
                'rif_beneficiario'      => $datos['rif_beneficiario'] ?? null,
                'concepto'              => $datos['concepto'],
                'monto_sin_iva'         => $datos['monto_sin_iva'] ?? null,
                'alicuota_iva'          => $datos['alicuota_iva'] ?? null,
                'monto'                 => $datos['monto'],
                'tipo_documento'        => $datos['tipo_documento'] ?? null,
                'numero_documento'      => $datos['numero_documento'] ?? null,
                'fecha_documento'       => $datos['fecha_documento'] ?? null,
                'descripcion_documento' => $datos['descripcion_documento'] ?? null,
                'fecha_compromiso'      => $datos['fecha_compromiso'],
                'fecha_vencimiento'     => $datos['fecha_vencimiento'] ?? null,
                'observaciones'         => $datos['observaciones'] ?? null,
            ]);

            if (abs($diferencia) >= 0.01 && $compromiso->partida) {
                $compromiso->partida->decrement('saldo_actual', $diferencia);
            }

            // Sincronizar la causación en borrador si existe
            $causacion = $compromiso->causaciones()->where('estado', 'borrador')->first();
            if ($causacion) {
                $causacion->update([
                    'beneficiario'          => ucwords(strtolower($datos['beneficiario'])),
                    'rif_beneficiario'      => $datos['rif_beneficiario'] ?? null,
                    'concepto'              => $datos['concepto'],
                    'monto_sin_iva'         => $datos['monto_sin_iva'] ?? null,
                    'alicuota_iva'          => $datos['alicuota_iva'] ?? null,
                    'monto_causado'         => $datos['monto'],
                    'tipo_documento'        => $datos['tipo_documento'] ?? null,
                    'numero_documento'      => $datos['numero_documento'] ?? null,
                    'fecha_documento'       => $datos['fecha_documento'] ?? null,
                    'descripcion_documento' => $datos['descripcion_documento'] ?? null,
                    'fecha_causacion'       => $datos['fecha_compromiso'],
                ]);
            }
        });
    }
}
