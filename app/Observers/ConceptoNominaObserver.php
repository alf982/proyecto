<?php

namespace App\Observers;

use App\Services\CatalogoCache;
use App\Models\ConceptoNomina;

class ConceptoNominaObserver
{
    public function created(ConceptoNomina $model): void  { CatalogoCache::olvidarConceptosNomina(); }
    public function updated(ConceptoNomina $model): void  { CatalogoCache::olvidarConceptosNomina(); }
    public function deleted(ConceptoNomina $model): void  { CatalogoCache::olvidarConceptosNomina(); }
    public function restored(ConceptoNomina $model): void { CatalogoCache::olvidarConceptosNomina(); }
}
