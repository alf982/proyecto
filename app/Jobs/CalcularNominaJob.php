<?php

namespace App\Jobs;

use App\Models\ConceptoNomina;
use App\Models\Empleado;
use App\Models\Nomina;
use App\Models\NominaDetalle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalcularNominaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Número máximo de reintentos si falla */
    public int $tries = 2;

    /** Timeout en segundos (5 minutos) */
    public int $timeout = 300;

    public function __construct(
        private readonly Nomina $nomina,
        private readonly array  $idsRetenciones = [],
    ) {}

    public function handle(): void
    {
        Log::info("CalcularNominaJob: Iniciando cálculo de nómina #{$this->nomina->numero}");

        DB::transaction(function () {
            // Limpiar detalles previos si es un re-cálculo
            $this->nomina->detalles()->delete();

            $empleados         = Empleado::activos()->with(['cargo', 'bonificaciones' => fn($q) => $q->where('activo', true)->with('concepto')])->get();
            $conceptos         = ConceptoNomina::activos()->get();
            $totalAsignaciones = 0;
            $totalDeducciones  = 0;

            foreach ($empleados as $empleado) {
                $salarioBase  = (float) $empleado->cargo?->salario_base ?? 0;
                $asignaciones = $salarioBase;
                $deducciones  = 0;
                $conceptosApp = [
                    ['codigo' => 'SUELDO', 'nombre' => 'Sueldo Base', 'tipo' => 'asignacion', 'monto' => $salarioBase],
                ];

                // Cargar y aplicar bonificaciones/conceptos individuales
                foreach ($empleado->bonificaciones as $bonoInd) {
                    if (!$bonoInd->concepto) continue;
                    
                    $monto = $bonoInd->obtenerMontoReal($salarioBase);
                    if ($bonoInd->concepto->tipo === 'asignacion') {
                        $asignaciones += $monto;
                    } else {
                        $deducciones += $monto;
                    }
                    $conceptosApp[] = [
                        'codigo'  => $bonoInd->concepto->codigo,
                        'nombre'  => $bonoInd->concepto->nombre . ' (Indiv.)',
                        'tipo'    => $bonoInd->concepto->tipo,
                        'calculo' => is_null($bonoInd->monto) ? $bonoInd->concepto->calculo : 'fijo_personalizado',
                        'valor'   => is_null($bonoInd->monto) ? $bonoInd->concepto->valor : (float) $bonoInd->monto,
                        'monto'   => $monto,
                    ];
                }

                foreach ($conceptos as $concepto) {
                    // Los conceptos no obligatorios solo se aplican si están asignados individualmente
                    if (!$concepto->es_obligatorio) {
                        continue;
                    }

                    // Filtrar por tipo de empleado
                    if ($concepto->aplica_a !== 'todos' && $concepto->aplica_a !== $empleado->tipo . 's') {
                        continue;
                    }

                    $monto = $concepto->calcularMonto($salarioBase);

                    if ($concepto->tipo === 'asignacion') {
                        $asignaciones += $monto;
                    } else {
                        $deducciones += $monto;
                    }

                    $conceptosApp[] = [
                        'codigo'  => $concepto->codigo,
                        'nombre'  => $concepto->nombre,
                        'tipo'    => $concepto->tipo,
                        'calculo' => $concepto->calculo,
                        'valor'   => $concepto->valor,
                        'monto'   => $monto,
                    ];
                }

                $neto = $asignaciones - $deducciones;

                NominaDetalle::create([
                    'nomina_id'           => $this->nomina->id,
                    'empleado_id'         => $empleado->id,
                    'salario_base'        => $salarioBase,
                    'total_asignaciones'  => $asignaciones,
                    'total_deducciones'   => $deducciones,
                    'neto'                => $neto,
                    'conceptos_aplicados' => $conceptosApp,
                ]);

                $totalAsignaciones += $asignaciones;
                $totalDeducciones  += $deducciones;
            }

            $this->nomina->update([
                'total_asignaciones' => $totalAsignaciones,
                'total_deducciones'  => $totalDeducciones,
                'total_neto'         => $totalAsignaciones - $totalDeducciones,
                'estado'             => 'calculada',
            ]);
        });

        // Guardar retenciones si se seleccionaron al crear la nómina
        if (!empty($this->idsRetenciones)) {
            $this->nomina->refresh();
            $totalNeto = (float) $this->nomina->total_neto;

            foreach ($this->idsRetenciones as $retencionId) {
                $retencion = \App\Models\Retencion::find($retencionId);
                if (!$retencion) continue;

                $monto = $retencion->calcularMonto($totalNeto);
                \App\Models\RetencionAplicada::create([
                    'retencion_id'        => $retencion->id,
                    'retencionable_id'    => $this->nomina->id,
                    'retencionable_type'  => \App\Models\Nomina::class,
                    'monto_base'          => $totalNeto,
                    'monto_retenido'      => $monto,
                    'porcentaje_aplicado' => $retencion->porcentaje ?? 0,
                ]);
            }
        }

        Log::info("CalcularNominaJob: Nómina #{$this->nomina->numero} calculada. {$this->nomina->total_neto} Bs. neto");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("CalcularNominaJob: Falló el cálculo de nómina #{$this->nomina->numero}: {$e->getMessage()}");

        // Marcar la nómina con error para que el usuario lo sepa
        $this->nomina->update(['estado' => 'borrador']);
    }
}
