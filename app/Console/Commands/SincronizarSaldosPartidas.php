<?php

namespace App\Console\Commands;

use App\Models\CreditoPresupuestario;
use App\Models\MovimientoPartida;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SincronizarSaldosPartidas extends Command
{
    protected $signature   = 'partidas:sincronizar-saldos {--dry-run : Solo muestra qué haría sin ejecutar nada}';
    protected $description = 'Sincroniza el saldo_actual de las partidas con los créditos presupuestarios existentes';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->info($dryRun ? '🔍 MODO SIMULACIÓN (sin cambios reales)' : '🔄 Sincronizando saldos de partidas con créditos presupuestarios...');
        $this->newLine();

        // Trae créditos con partida que tiene saldo_actual == 0 y monto_aprobado > 0
        $creditos = CreditoPresupuestario::with('partida')
            ->whereNotNull('partida_presupuestaria_id')
            ->get()
            ->filter(function ($c) {
                return $c->partida
                    && (float)$c->partida->saldo_actual === 0.0
                    && (float)$c->monto_aprobado > 0;
            });

        if ($creditos->isEmpty()) {
            $this->info('✅ No hay créditos pendientes de sincronizar.');
            return self::SUCCESS;
        }

        $rows = [];

        foreach ($creditos as $c) {
            $monto = (float)$c->monto_aprobado + (float)$c->monto_modificado;

            $rows[] = [
                "#{$c->id}",
                $c->partida->codigo,
                number_format($c->monto_aprobado, 2),
                number_format($c->monto_modificado, 2),
                number_format($monto, 2),
            ];
        }

        $this->table(['Crédito', 'Partida', 'Aprobado', 'Modificado', 'Vigente a asignar'], $rows);

        if ($dryRun) {
            $this->warn('⚠  Ejecuta sin --dry-run para aplicar los cambios.');
            return self::SUCCESS;
        }

        if (! $this->confirm("¿Deseas crear los movimientos de asignación para {$creditos->count()} crédito(s)? Esta acción no es reversible.")) {
            $this->info('Operación cancelada.');
            return self::SUCCESS;
        }

        $creados  = 0;
        $errores  = 0;

        foreach ($creditos as $c) {
            try {
                DB::transaction(function () use ($c) {
                    // Bloquear la partida para evitar condición de carrera
                    $partida = \App\Models\PartidaPresupuestaria::lockForUpdate()->find($c->partida_presupuestaria_id);
                    if (! $partida) return;

                    // Si ya tiene saldo (por otro proceso concurrente), no tocar
                    if ((float)$partida->saldo_actual > 0) return;

                    $monto     = (float)$c->monto_aprobado + (float)$c->monto_modificado;
                    $anio      = now()->year;

                    MovimientoPartida::create([
                        'numero'                    => MovimientoPartida::generarNumero($anio),
                        'partida_presupuestaria_id' => $partida->id,
                        'cuenta_bancaria_id'        => $partida->cuenta_bancaria_id,
                        'ejercicio_fiscal_id'       => $c->ejercicio_fiscal_id,
                        'tipo'                      => 'asignacion',
                        'concepto'                  => "Asignación inicial retroactiva — Crédito #{$c->id} ({$partida->codigo})",
                        'monto'                     => $monto,
                        'fecha_movimiento'          => now()->toDateString(),
                        'referencia'                => 'SYNC-CRED-' . str_pad($c->id, 5, '0', STR_PAD_LEFT),
                        'saldo_anterior'            => 0,
                        'saldo_posterior'           => $monto,
                        'estado'                    => 'confirmado',
                        'observaciones'             => 'Generado por comando partidas:sincronizar-saldos para alinear saldo_actual con crédito existente.',
                        'creado_por'                => 1, // admin
                    ]);

                    $partida->update(['saldo_actual' => $monto]);

                    // Actualizar cuenta bancaria vinculada si existe
                    if ($partida->cuenta_bancaria_id) {
                        $partida->cuentaBancaria->recalcularSaldo();
                    }
                });

                $this->line("  ✅ Crédito #{$c->id} ({$c->partida->codigo}) → saldo fijado en Bs. " . number_format((float)$c->monto_aprobado + (float)$c->monto_modificado, 2));
                $creados++;

            } catch (\Throwable $e) {
                $this->error("  ❌ Crédito #{$c->id}: {$e->getMessage()}");
                $errores++;
            }
        }

        $this->newLine();
        $this->info("Sincronización completada: {$creados} crédito(s) actualizados, {$errores} error(es).");

        // Mostrar créditos sin partida para informar al usuario
        $sinPartida = CreditoPresupuestario::whereNull('partida_presupuestaria_id')->get();
        if ($sinPartida->isNotEmpty()) {
            $this->newLine();
            $this->warn("⚠  Los siguientes {$sinPartida->count()} crédito(s) NO tienen partida vinculada y no fueron procesados:");
            foreach ($sinPartida as $c) {
                $this->line("  — Crédito #{$c->id} | monto_aprobado={$c->monto_aprobado}");
            }
            $this->warn("   Asigna una partida a cada crédito desde: " . route('presupuesto.creditos.edit', $sinPartida->first()));
        }

        return self::SUCCESS;
    }
}
