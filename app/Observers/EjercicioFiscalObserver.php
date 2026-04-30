<?php

namespace App\Observers;

use App\Services\CatalogoCache;
use App\Models\EjercicioFiscal;

class EjercicioFiscalObserver
{
    public function created(EjercicioFiscal $model): void  { CatalogoCache::olvidarEjercicios(); }
    public function updated(EjercicioFiscal $model): void  { CatalogoCache::olvidarEjercicios(); }
    public function deleted(EjercicioFiscal $model): void  { CatalogoCache::olvidarEjercicios(); }
    public function restored(EjercicioFiscal $model): void { CatalogoCache::olvidarEjercicios(); }
}
