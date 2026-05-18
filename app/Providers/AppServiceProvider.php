<?php

namespace App\Providers;

use App\Models\Beneficiario;
use App\Models\ConceptoNomina;
use App\Models\EjercicioFiscal;
use App\Models\PartidaPresupuestaria;
use App\Models\ProyectoSia;
use App\Models\UnidadEjecutora;
use App\Observers\BeneficiarioObserver;
use App\Observers\ConceptoNominaObserver;
use App\Observers\EjercicioFiscalObserver;
use App\Observers\PartidaPresupuestariaObserver;
use App\Observers\ProyectoSiaObserver;
use App\Observers\UnidadEjecutoraObserver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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

        // ── Model Observers ───────────────────────────────────────────
        // Invalidan el caché de catálogos automáticamente + lógica de negocio
        PartidaPresupuestaria::observe(PartidaPresupuestariaObserver::class);
        UnidadEjecutora::observe(UnidadEjecutoraObserver::class);
        EjercicioFiscal::observe(EjercicioFiscalObserver::class);
        ConceptoNomina::observe(ConceptoNominaObserver::class);
        Beneficiario::observe(BeneficiarioObserver::class);
        ProyectoSia::observe(ProyectoSiaObserver::class);

        // Super-admin tiene acceso a todo — bypass de todos los gates
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }
        });
    }
}

