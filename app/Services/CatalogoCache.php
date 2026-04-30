<?php

namespace App\Services;

use App\Models\ConceptoNomina;
use App\Models\EjercicioFiscal;
use App\Models\PartidaPresupuestaria;
use App\Models\Retencion;
use App\Models\UnidadEjecutora;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * CatalogoCache — Punto único de acceso a catálogos estáticos.
 *
 * PATRÓN CORRECTO: Se guarda un array plano (nunca objetos Eloquent)
 * y se devuelve una Collection de stdClass.
 *
 * Razón: el driver `database` serializa con PHP serialize(). Si se
 * cachea un modelo Eloquent, al deserializarlo puede fallar con
 * __PHP_Incomplete_Class cuando el autoloader no resuelve la clase
 * (ej. entre peticiones con estado inconsistente). Guardar arrays
 * primitivos elimina completamente este problema.
 *
 * Las vistas y controladores siguen usando $obj->propiedad gracias
 * a que convertimos cada array en stdClass antes de devolver.
 */
class CatalogoCache
{
    private const TTL_SEGUNDOS = 21_600; // 6 horas

    // ── Helper interno: cache de arrays → Collection de stdClass ─────
    private static function recuerda(string $clave, callable $query): Collection
    {
        // Cache::remember guarda y lee el valor (array plano, serializable)
        $data = Cache::remember($clave, self::TTL_SEGUNDOS, function () use ($query) {
            // ->toArray() convierte la Collection de Eloquent en array PHP puro
            return $query()->toArray();
        });

        // Si el valor cacheado era de una versión anterior (objetos Eloquent),
        // puede no ser un array. En ese caso lo descartamos y reconstruimos.
        if (! is_array($data)) {
            Cache::forget($clave);
            $data = $query()->toArray();
            Cache::put($clave, $data, self::TTL_SEGUNDOS);
        }

        // Convertir cada fila a stdClass para mantener la sintaxis $obj->campo
        return collect($data)->map(fn ($row) => (object) $row);
    }

    // ── Unidades Ejecutoras ───────────────────────────────────────────
    public static function unidades(): Collection
    {
        return self::recuerda('catalogo.unidades', fn () =>
            UnidadEjecutora::activas()
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'codigo'])
        );
    }

    public static function olvidarUnidades(): void
    {
        Cache::forget('catalogo.unidades');
    }

    // ── Partidas Presupuestarias ──────────────────────────────────────
    public static function partidas(): Collection
    {
        return self::recuerda('catalogo.partidas', fn () =>
            PartidaPresupuestaria::where('activo', true)
                ->orderBy('codigo')
                ->get(['id', 'codigo', 'descripcion', 'cuenta_bancaria_id', 'saldo_actual', 'monto_vigente'])
        );
    }

    public static function olvidarPartidas(): void
    {
        Cache::forget('catalogo.partidas');
    }

    // ── Ejercicios Fiscales ───────────────────────────────────────────
    public static function ejercicios(): Collection
    {
        return self::recuerda('catalogo.ejercicios', fn () =>
            EjercicioFiscal::orderByDesc('anio')
                ->get(['id', 'anio', 'estado', 'fecha_inicio', 'fecha_fin'])
        );
    }

    /**
     * Retorna el ejercicio activo como stdClass (o null si no existe).
     * Nota: no se cachea como Eloquent para evitar __PHP_Incomplete_Class.
     */
    public static function ejercicioActivo(): ?object
    {
        $data = Cache::remember('catalogo.ejercicio_activo', self::TTL_SEGUNDOS, function () {
            $ej = EjercicioFiscal::where('estado', 'activo')->first();
            return $ej ? $ej->toArray() : null;
        });

        // Guardia contra versiones cacheadas con el modelo completo
        if ($data !== null && ! is_array($data)) {
            Cache::forget('catalogo.ejercicio_activo');
            $ej = EjercicioFiscal::where('estado', 'activo')->first();
            $data = $ej ? $ej->toArray() : null;
            Cache::put('catalogo.ejercicio_activo', $data, self::TTL_SEGUNDOS);
        }

        return $data ? (object) $data : null;
    }

    public static function olvidarEjercicios(): void
    {
        Cache::forget('catalogo.ejercicios');
        Cache::forget('catalogo.ejercicio_activo');
    }

    // ── Conceptos de Nómina ───────────────────────────────────────────
    public static function conceptosNomina(): Collection
    {
        return self::recuerda('catalogo.conceptos_nomina', fn () =>
            ConceptoNomina::activos()->orderBy('codigo')->get()
        );
    }

    public static function olvidarConceptosNomina(): void
    {
        Cache::forget('catalogo.conceptos_nomina');
    }

    // ── Retenciones ───────────────────────────────────────────────────
    public static function retenciones(): Collection
    {
        return self::recuerda('catalogo.retenciones', fn () =>
            Retencion::where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'codigo', 'tipo_calculo', 'valor', 'aplica_iva'])
        );
    }

    public static function olvidarRetenciones(): void
    {
        Cache::forget('catalogo.retenciones');
    }

    // ── Limpiar TODO el caché de catálogos ───────────────────────────
    public static function olvidarTodo(): void
    {
        self::olvidarUnidades();
        self::olvidarPartidas();
        self::olvidarEjercicios();
        self::olvidarConceptosNomina();
        self::olvidarRetenciones();
    }
}
