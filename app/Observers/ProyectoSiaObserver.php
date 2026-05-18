<?php

namespace App\Observers;

use App\Models\ProyectoSia;
use App\Services\CatalogoCache;

/**
 * Invalida el caché de proyectos SIA al crear, actualizar o eliminar.
 */
class ProyectoSiaObserver
{
    public function created(ProyectoSia $model): void  { CatalogoCache::olvidarProyectos(); }
    public function updated(ProyectoSia $model): void  { CatalogoCache::olvidarProyectos(); }
    public function deleted(ProyectoSia $model): void  { CatalogoCache::olvidarProyectos(); }
    public function restored(ProyectoSia $model): void { CatalogoCache::olvidarProyectos(); }
}
