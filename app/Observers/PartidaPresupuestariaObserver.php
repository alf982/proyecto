<?php

namespace App\Observers;

use App\Services\CatalogoCache;
use App\Models\PartidaPresupuestaria;

/**
 * Observer de PartidaPresupuestaria:
 *   1. Invalida el caché del catálogo de partidas (Fase 5)
 *   2. Sincroniza el saldo de la cuenta bancaria vinculada (comportamiento original)
 */
class PartidaPresupuestariaObserver
{
    public function created(PartidaPresupuestaria $model): void
    {
        CatalogoCache::olvidarPartidas();
    }

    public function updated(PartidaPresupuestaria $model): void
    {
        CatalogoCache::olvidarPartidas();

        // Sincronizar saldo de la cuenta bancaria vinculada si cambió saldo_actual
        if ($model->wasChanged('saldo_actual') && $model->cuenta_bancaria_id) {
            optional($model->cuentaBancaria)->recalcularSaldo();
        }
    }

    public function deleted(PartidaPresupuestaria $model): void
    {
        CatalogoCache::olvidarPartidas();
    }

    public function restored(PartidaPresupuestaria $model): void
    {
        CatalogoCache::olvidarPartidas();
    }
}
