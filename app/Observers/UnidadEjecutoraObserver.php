<?php

namespace App\Observers;

use App\Services\CatalogoCache;
use App\Models\UnidadEjecutora;

/**
 * Invalida el caché de unidades ejecutoras al crear, actualizar o eliminar.
 */
class UnidadEjecutoraObserver
{
    public function created(UnidadEjecutora $model): void  { CatalogoCache::olvidarUnidades(); }
    public function updated(UnidadEjecutora $model): void  { CatalogoCache::olvidarUnidades(); }
    public function deleted(UnidadEjecutora $model): void  { CatalogoCache::olvidarUnidades(); }
    public function restored(UnidadEjecutora $model): void { CatalogoCache::olvidarUnidades(); }
}
