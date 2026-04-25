<?php

namespace App\Console\Commands;

use App\Models\CuentaBancaria;
use App\Models\PartidaPresupuestaria;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SincronizarSaldosCuentas extends Command
{
    protected $signature   = 'cuentas:sincronizar-saldos
                                {--dry-run : Muestra los cambios sin aplicarlos}';
    protected $description = 'Recalcula el saldo_actual de cada cuenta bancaria sumando los saldo_actual de sus partidas presupuestarias vinculadas.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->info('');
        $this->info('══════════════════════════════════════════════════════');
        $this->info('  SIA · Sincronización de Saldos Bancarios          ');
        $this->info('══════════════════════════════════════════════════════');

        // ── 1. Cuentas con partidas vinculadas ─────────────────────────
        $cuentas = CuentaBancaria::withoutTrashed()
            ->withCount('partidasPresupuestarias')
            ->with('partidasPresupuestarias')
            ->orderBy('codigo')
            ->get();

        $actualizadas = 0;
        $sinPartidas  = 0;

        $headers = ['Código', 'Nombre', 'Partidas', 'Saldo Anterior', 'Saldo Calculado', 'Diferencia'];
        $rows    = [];

        foreach ($cuentas as $cuenta) {
            $saldoAnterior   = (float) $cuenta->saldo_actual;
            $saldoCalculado  = (float) $cuenta->partidasPresupuestarias->sum('saldo_actual');
            $diferencia      = $saldoCalculado - $saldoAnterior;

            $rows[] = [
                $cuenta->codigo,
                mb_strimwidth($cuenta->nombre, 0, 30, '…'),
                $cuenta->partidas_presupuestarias_count,
                number_format($saldoAnterior,  2, '.', ','),
                number_format($saldoCalculado, 2, '.', ','),
                ($diferencia >= 0 ? '+' : '') . number_format($diferencia, 2, '.', ','),
            ];

            if ($cuenta->partidas_presupuestarias_count === 0) {
                $sinPartidas++;
                continue;
            }

            if (! $dryRun) {
                $cuenta->updateQuietly(['saldo_actual' => $saldoCalculado]);
            }
            $actualizadas++;
        }

        $this->table($headers, $rows);

        // ── 2. Resumen ─────────────────────────────────────────────────
        $this->info('');
        $this->info("  Cuentas procesadas : {$cuentas->count()}");
        $this->info("  Cuentas actualizadas: {$actualizadas}");
        $this->info("  Cuentas sin partidas: {$sinPartidas} (sin cambios)");

        if ($dryRun) {
            $this->warn('');
            $this->warn('  [DRY RUN] No se aplicaron cambios. Ejecuta sin --dry-run para aplicar.');
        } else {
            $this->newLine();
            $this->info('  ✅ Saldos sincronizados correctamente.');
        }

        $this->info('══════════════════════════════════════════════════════');
        $this->info('');

        return self::SUCCESS;
    }
}
