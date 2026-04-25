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

    public function __construct(private readonly Nomina $nomina) {}

    public function handle(): void
    {
        Log::info("CalcularNominaJob: Iniciando cálculo de nómina #{$this->nomina->numero}");

        DB::transaction(function () {
            // Limpiar detalles previos si es un re-cálculo
            $this->nomina->detalles()->delete();

            $empleados         = Empleado::activos()->with('cargo')->get();
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

                foreach ($conceptos as $concepto) {
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

        Log::info("CalcularNominaJob: Nómina #{$this->nomina->numero} calculada. {$this->nomina->total_neto} Bs. neto");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("CalcularNominaJob: Falló el cálculo de nómina #{$this->nomina->numero}: {$e->getMessage()}");

        // Marcar la nómina con error para que el usuario lo sepa
        $this->nomina->update(['estado' => 'borrador']);
    }
}
