<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait para filtrar automáticamente modelos por el ejercicio fiscal seleccionado en la sesión.
 */
trait FiltraPorEjercicio
{
    /**
     * Se ejecuta automáticamente al iniciar el modelo.
     */
    protected static function bootFiltraPorEjercicio(): void
    {
        static::addGlobalScope('ejercicio_fiscal', function (Builder $builder) {
            // Obtenemos el ID del ejercicio de la sesión (seteado por EjercicioContext middleware)
            $ejercicioId = session('ejercicio_id');

            if ($ejercicioId) {
                // Aplicamos el filtro automáticamente a todas las consultas
                $builder->where($builder->getQuery()->from . '.ejercicio_fiscal_id', $ejercicioId);
            }
        });
    }
}
