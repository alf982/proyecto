<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\PartidaPresupuestaria;
use App\Observers\PartidaPresupuestariaObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Locale español para Carbon y fechas del sistema
        Carbon::setLocale('es');
        setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain.1252', 'Spanish');

        // Observer: mantiene saldo_actual de CuentaBancaria sincronizado
        // automáticamente cada vez que cambia el saldo de cualquier partida vinculada
        PartidaPresupuestaria::observe(PartidaPresupuestariaObserver::class);

        // Super-admin tiene acceso a todo — bypass de todos los gates
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }
        });
    }
}
