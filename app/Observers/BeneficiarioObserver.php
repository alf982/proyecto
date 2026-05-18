<?php

namespace App\Observers;

use App\Models\Beneficiario;
use App\Services\CatalogoCache;

/**
 * Invalida el caché de beneficiarios al crear, actualizar o eliminar.
 */
class BeneficiarioObserver
{
    public function created(Beneficiario $model): void  { CatalogoCache::olvidarBeneficiarios(); }
    public function updated(Beneficiario $model): void  { CatalogoCache::olvidarBeneficiarios(); }
    public function deleted(Beneficiario $model): void  { CatalogoCache::olvidarBeneficiarios(); }
    public function restored(Beneficiario $model): void { CatalogoCache::olvidarBeneficiarios(); }
}
