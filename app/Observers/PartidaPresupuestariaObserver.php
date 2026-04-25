<?php

namespace App\Observers;

use App\Models\CuentaBancaria;
use App\Models\PartidaPresupuestaria;

/**
 * Observer que mantiene CuentaBancaria.saldo_actual = Σ PartidaPresupuestaria.saldo_actual
 * para todas las cuentas afectadas, incluyendo el caso en que se mueve una
 * partida de una cuenta bancaria a otra.
 */
class PartidaPresupuestariaObserver
{
    public function updated(PartidaPresupuestaria $partida): void
    {
        $cuentasARecalcular = [];

        // CASO 1: Cambió el saldo_actual → recalcular la cuenta actual
        if ($partida->wasChanged('saldo_actual') && $partida->cuenta_bancaria_id) {
            $cuentasARecalcular[] = $partida->cuenta_bancaria_id;
        }

        // CASO 2: Cambió la cuenta bancaria (partida movida de cuenta)
        // → recalcular TANTO la cuenta anterior como la nueva
        if ($partida->wasChanged('cuenta_bancaria_id')) {
            $cuentaAnterior = $partida->getOriginal('cuenta_bancaria_id');
            $cuentaNueva    = $partida->cuenta_bancaria_id;

            if ($cuentaAnterior) {
                $cuentasARecalcular[] = $cuentaAnterior;
            }
            if ($cuentaNueva) {
                $cuentasARecalcular[] = $cuentaNueva;
            }
        }

        // Recalcular cada cuenta afectada (sin repetir)
        foreach (array_unique($cuentasARecalcular) as $cuentaId) {
            CuentaBancaria::find($cuentaId)?->recalcularSaldo();
        }
    }

    public function deleted(PartidaPresupuestaria $partida): void
    {
        if ($partida->cuenta_bancaria_id) {
            CuentaBancaria::find($partida->cuenta_bancaria_id)?->recalcularSaldo();
        }
    }

    public function restored(PartidaPresupuestaria $partida): void
    {
        if ($partida->cuenta_bancaria_id) {
            CuentaBancaria::find($partida->cuenta_bancaria_id)?->recalcularSaldo();
        }
    }
}
