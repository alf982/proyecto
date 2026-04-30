<?php

namespace App\Services\Presupuesto;

use App\Models\Causacion;
use App\Models\Compromiso;
use App\Models\EjercicioFiscal;
use App\Models\MovimientoPartida;
use App\Traits\GuardaRetenciones;
use Illuminate\Support\Facades\DB;

class CausacionService
{
    use GuardaRetenciones;

    /**
     * Crea una Causación vinculada a un Compromiso aprobado.
     * Registra retenciones y el movimiento de trazabilidad.
     *
     * @throws \RuntimeException en validaciones de negocio
     */
    public function crear(Compromiso $compromiso, array $datos, array $idsRetenciones = []): Causacion
    {
        if (!$compromiso->esAprobado()) {
            throw new \RuntimeException('El compromiso debe estar en estado Aprobado.');
        }

        // Guardia anti-duplicación
        $existente = $compromiso->causaciones()
            ->whereNotIn('estado', ['anulada'])
            ->first();

        if ($existente) {
            throw new \RuntimeException(
                'Este compromiso ya tiene una causación registrada (' . $existente->numero . ').'
            );
        }

        $montoCausado   = (float) $datos['monto_causado'];
        $totalRetencion = $this->calcularTotalRetenciones($idsRetenciones, $montoCausado);

        if ($montoCausado > (float) $compromiso->monto) {
            throw new \RuntimeException(
                'El monto causado (Bs. ' . number_format($montoCausado, 2) .
                ') supera el monto del compromiso (Bs. ' . number_format($compromiso->monto, 2) . ').'
            );
        }

        $ejercicio = EjercicioFiscal::where('estado', 'activo')->first();

        return DB::transaction(function () use ($datos, $compromiso, $ejercicio, $montoCausado, $totalRetencion, $idsRetenciones) {
            $causacion = Causacion::create([
                'compromiso_id'             => $compromiso->id,
                'numero'                    => Causacion::generarNumero(now()->year),
                'ejercicio_fiscal_id'       => $compromiso->ejercicio_fiscal_id ?? $ejercicio?->id,
                'unidad_ejecutora_id'       => $compromiso->unidad_ejecutora_id,
                'partida_presupuestaria_id' => $compromiso->partida_presupuestaria_id,
                'proyecto_id'               => $compromiso->proyecto_id,
                'beneficiario'              => $compromiso->beneficiario,
                'rif_beneficiario'          => $compromiso->rif_beneficiario,
                'tipo_documento'            => $datos['tipo_documento'],
                'numero_documento'          => $datos['numero_documento'] ?? null,
                'fecha_documento'           => $datos['fecha_documento'] ?? null,
                'descripcion_documento'     => $datos['descripcion_documento'] ?? null,
                'concepto'                  => $datos['concepto'],
                'fecha_causacion'           => $datos['fecha_causacion'],
                'monto_sin_iva'             => $datos['monto_sin_iva'] ?? null,
                'alicuota_iva'              => $datos['alicuota_iva'] ?? null,
                'monto_causado'             => $montoCausado,
                'monto_retencion'           => $totalRetencion,
                'observaciones'             => $datos['observaciones'] ?? null,
                'estado'                    => 'borrador',
                'created_by'               => auth()->id(),
            ]);

            $this->guardarRetenciones($causacion, $idsRetenciones, $montoCausado);

            $compromiso->update(['estado' => 'causado']);

            // Movimiento de trazabilidad (el saldo ya fue descontado en el compromiso)
            if ($compromiso->partida) {
                $saldoAnt = (float) $compromiso->partida->saldo_actual;
                MovimientoPartida::create([
                    'numero'                    => MovimientoPartida::generarNumero(now()->year),
                    'partida_presupuestaria_id' => $compromiso->partida_presupuestaria_id,
                    'cuenta_bancaria_id'        => $compromiso->partida->cuenta_bancaria_id,
                    'ejercicio_fiscal_id'       => $compromiso->ejercicio_fiscal_id ?? $ejercicio?->id,
                    'tipo'                      => 'causacion',
                    'concepto'                  => 'Causación ' . $causacion->numero . ' del compromiso ' . $compromiso->numero,
                    'monto'                     => $montoCausado,
                    'fecha_movimiento'          => $datos['fecha_causacion'],
                    'referencia'                => $causacion->numero,
                    'saldo_anterior'            => $saldoAnt,
                    'saldo_posterior'           => $saldoAnt, // ya descontado en compromiso
                    'estado'                    => 'confirmado',
                    'creado_por'               => auth()->id(),
                ]);
            }

            return $causacion;
        });
    }

    /**
     * Aprueba la Causación y avanza el Compromiso vinculado a 'causado'.
     */
    public function aprobar(Causacion $causacion): void
    {
        if (!$causacion->esBorrador()) {
            throw new \RuntimeException('Solo se pueden aprobar causaciones en Borrador.');
        }

        DB::transaction(function () use ($causacion) {
            $causacion->update([
                'estado'           => 'aprobada',
                'fecha_aprobacion' => now()->toDateString(),
                'aprobado_por'     => auth()->id(),
            ]);

            if ($causacion->compromiso_id) {
                $compromiso = Compromiso::find($causacion->compromiso_id);
                if ($compromiso?->esAprobado()) {
                    $compromiso->update(['estado' => 'causado']);
                }
            }
        });
    }

    /**
     * Anula la Causación y revierte el Compromiso a 'aprobado'.
     *
     * @throws \RuntimeException si ya está pagada
     */
    public function anular(Causacion $causacion, string $motivo): void
    {
        if ($causacion->esPagada()) {
            throw new \RuntimeException('No se pueden anular causaciones ya pagadas.');
        }

        DB::transaction(function () use ($causacion, $motivo) {
            $causacion->update([
                'estado'           => 'anulada',
                'motivo_anulacion' => $motivo,
            ]);

            if ($causacion->compromiso) {
                $causacion->compromiso->update(['estado' => 'aprobado']);
            }
        });
    }
}
