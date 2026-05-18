<?php
namespace App\Http\Controllers\Presupuesto;

use App\Http\Controllers\Controller;
use App\Jobs\ExportarReporteEjecucionJob;
use App\Models\Causacion;
use App\Models\Compromiso;
use App\Models\CreditoPresupuestario;
use App\Models\EjercicioFiscal;
use App\Models\MovimientoPartida;
use App\Models\Pago;
use App\Models\PartidaPresupuestaria;
use App\Models\UnidadEjecutora;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

/**
 * Controlador de Reportes Presupuestarios (Ejecución)
 * 
 * Este controlador es el corazón analítico del módulo de presupuesto.
 * Su propósito es consolidar y calcular en tiempo real el estado de la ejecución
 * presupuestaria (Matemáticas Financieras Gubernamentales).
 * 
 * Calcula los siguientes KPIs globales y por partida:
 * - Aprobado: Presupuesto original.
 * - Vigente: Presupuesto modificado (Aprobado +/- Traspasos y Créditos Adicionales).
 * - Comprometido: Total reservado en la etapa 1 de ejecución.
 * - Causado: Deuda real consolidada en la etapa 2.
 * - Pagado: Órdenes de pago ejecutadas en la etapa 3.
 * - Disponible Real: Vigente - Comprometido. Lo que queda para nuevos gastos.
 * 
 * Este controlador despacha Jobs a las Queues (ExportarReporteEjecucionJob) para la 
 * generación de reportes pesados en PDF, mejorando la respuesta del sistema.
 */
class ReporteController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:presupuesto.reportes'),
        ];
    }

    /**
     * Calcula y muestra el Dashboard de Ejecución Presupuestaria.
     * Genera la sumatoria de las etapas del gasto, agrupadass por Partida y Unidad Ejecutora.
     */
    public function ejecucion(Request $request)
    {
        $ejercicioActivo    = EjercicioFiscal::where('estado', 'activo')->first();
        $totales            = [];
        $porUnidad          = collect();
        $porPartida         = collect();
        $ultimasCausaciones = collect();
        $ultimosPagos       = collect();
        $ultimosMovimientos = collect();
        $partidaSeleccionada = null;
        $todasPartidas       = collect();

        $filtroPartida = $request->input('partida_id');
        $filtroUnidad  = $request->input('unidad_id');
        $filtroTipo    = $request->input('tipo_movimiento');

        if ($ejercicioActivo) {

            // ── Selector dropdown ──────────────────────────────────
            $todasPartidas = PartidaPresupuestaria::activas()->orderBy('codigo')->get();

            if ($filtroPartida) {
                $partidaSeleccionada = PartidaPresupuestaria::find($filtroPartida);
            }

            // ── Créditos del ejercicio ─────────────────────────────
            $creditosQuery = CreditoPresupuestario::with('partida')
                ->where('ejercicio_fiscal_id', $ejercicioActivo->id);
            if ($filtroPartida) {
                $creditosQuery->where('partida_presupuestaria_id', $filtroPartida);
            }
            $creditos = $creditosQuery->get();

            // IDs de partidas vinculadas a créditos
            $partidaIds = $creditos->pluck('partida_presupuestaria_id')->unique()->filter()->values();

            // ── Query base de partidas (con o sin créditos) ────────
            if ($partidaIds->isNotEmpty()) {
                $qPartidas = PartidaPresupuestaria::whereIn('id', $partidaIds);
            } else {
                $qPartidas = PartidaPresupuestaria::activas()->where('saldo_actual', '>', 0);
                if ($filtroPartida) {
                    $qPartidas->where('id', $filtroPartida);
                }
            }

            // ── Aprobado: monto_aprobado (techo fijo, solo sube con 'asignacion') ─
            $totalAprobado = (float) (clone $qPartidas)->sum('monto_aprobado');
            if ($totalAprobado === 0.0) {
                $totalAprobado = (float) (clone $qPartidas)->sum('saldo_actual');
            }

            // ── Vigente: monto_vigente (aprobado +- modif. formales) ───────────
            $totalVigente = (float) (clone $qPartidas)->sum('monto_vigente');
            if ($totalVigente === 0.0) {
                $totalVigente = $totalAprobado; // fallback si aun no hay vigente calculado
            }

            // ── Disponible: saldo real disponible en la partida ────────────
            // saldo_actual = lo que queda luego de compromisos y ejecuciones
            $totalDisponible = (float) (clone $qPartidas)->sum('saldo_actual');

            // ── Comprometido: directo de tabla compromisos ─────────
            $comprometidoQuery = Compromiso::where('ejercicio_fiscal_id', $ejercicioActivo->id)
                ->whereIn('estado', ['borrador', 'aprobado']);
            if ($filtroPartida) {
                $comprometidoQuery->where('partida_presupuestaria_id', $filtroPartida);
            }
            $totalComprometido = (float) $comprometidoQuery->sum('monto');

            // ── Causado: solo causaciones aprobadas pendientes de pago ─
            // Las 'pagada' ya se contabilizan en el KPI Pagado, no aqui.
            $causadoQuery = Causacion::where('ejercicio_fiscal_id', $ejercicioActivo->id)
                ->where('estado', 'aprobada');   // excluye 'pagada'
            if ($filtroPartida) {
                $causadoQuery->where('partida_presupuestaria_id', $filtroPartida);
            }
            $totalCausado = (float) $causadoQuery->sum('monto_causado');

            // ── Pagado ─────────────────────────────────────────────
            $pagadoQuery = Pago::where('ejercicio_fiscal_id', $ejercicioActivo->id)
                ->where('estado', 'procesado');
            if ($filtroPartida) {
                $pagadoQuery->whereHas('causacion',
                    fn($q) => $q->where('partida_presupuestaria_id', $filtroPartida)
                );
            }
            $totalPagado = (float) $pagadoQuery->sum('monto_pagado');

            $totales = [
                'aprobado'         => $totalAprobado,
                'vigente'          => $totalVigente,
                'disponible_real'  => $totalDisponible,
                'comprometido'     => $totalComprometido,
                'causado'          => $totalCausado,
                'pagado'           => $totalPagado,
                // Disponible = saldo real de la partida (descontados ya compromisos y ejecuciones)
                'disponible'       => $totalDisponible,
                'pct_ejec'         => $totalVigente > 0 ? round(($totalCausado      / $totalVigente) * 100, 1) : 0,
                'pct_comprometido' => $totalVigente > 0 ? round(($totalComprometido / $totalVigente) * 100, 1) : 0,
                'pct_disponible'   => $totalVigente > 0 ? round(($totalDisponible   / $totalVigente) * 100, 1) : 0,
            ];

            // ── Por unidad ejecutora — una sola query agrupada (sin N+1) ──
            if (! $filtroPartida) {
                // Comprometido por unidad: 1 query groupBy
                $comprometidoPorUnidad = Compromiso::where('ejercicio_fiscal_id', $ejercicioActivo->id)
                    ->whereIn('estado', ['borrador', 'aprobado'])
                    ->when($filtroUnidad, fn($q) => $q->where('unidad_ejecutora_id', $filtroUnidad))
                    ->select('unidad_ejecutora_id', DB::raw('SUM(monto) as total_comprometido'))
                    ->groupBy('unidad_ejecutora_id')
                    ->pluck('total_comprometido', 'unidad_ejecutora_id');

                $porUnidad = UnidadEjecutora::activas()
                    ->when($filtroUnidad, fn($q) => $q->where('id', $filtroUnidad))
                    ->get()
                    ->map(function ($u) use ($ejercicioActivo, $comprometidoPorUnidad) {
                        $comprometido = (float) ($comprometidoPorUnidad[$u->id] ?? 0);
                        if ($comprometido === 0.0) return null;

                        $vigente = $comprometido; // aproximación sin créditos
                        return [
                            'nombre'       => $u->nombre,
                            'codigo'       => $u->codigo,
                            'vigente'      => $vigente,
                            'saldo_real'   => 0,
                            'comprometido' => $comprometido,
                            'disponible'   => 0,
                            'num_partidas' => 0,
                        ];
                    })
                    ->filter(fn($u) => $u !== null)
                    ->sortByDesc('comprometido')
                    ->values();
            }

            // ── Por partida — una sola query agrupada (sin N+1) ────────────
            $comprometidoPorPartida = Compromiso::where('ejercicio_fiscal_id', $ejercicioActivo->id)
                ->whereIn('estado', ['borrador', 'aprobado'])
                ->when($filtroPartida, fn($q) => $q->where('partida_presupuestaria_id', $filtroPartida))
                ->select('partida_presupuestaria_id', DB::raw('SUM(monto) as total_comprometido'))
                ->groupBy('partida_presupuestaria_id')
                ->pluck('total_comprometido', 'partida_presupuestaria_id');

            $partidasParaListar = $creditos->filter(fn($c) => $c->partida !== null)
                ->pluck('partida')->unique('id');

            if ($partidasParaListar->isEmpty()) {
                $qBase = PartidaPresupuestaria::activas()->where('saldo_actual', '>', 0);
                if ($filtroPartida) $qBase->where('id', $filtroPartida);
                $partidasParaListar = $qBase->get();
            }

            $porPartida = $partidasParaListar->map(function ($p) use ($ejercicioActivo, $comprometidoPorPartida) {
                $saldoReal    = (float)($p->saldo_actual ?? 0);
                $aprobado     = (float)($p->monto_aprobado ?? 0);
                $vigente      = (float)($p->monto_vigente ?? 0);
                if ($vigente === 0.0) $vigente = $aprobado > 0 ? $aprobado : $saldoReal;

                $comprometido = (float) ($comprometidoPorPartida[$p->id] ?? 0);

                return [
                    'codigo'       => $p->codigo,
                    'descripcion'  => $p->descripcion,
                    'aprobado'     => $aprobado,
                    'vigente'      => $vigente,
                    'saldo_real'   => $saldoReal,
                    'comprometido' => $comprometido,
                    'disponible'   => $saldoReal,
                    'credito_id'   => null,
                    'partida_id'   => $p->id,
                ];
            })
            ->sortByDesc('vigente')
            ->values();

            // ── Movimientos recientes ──────────────────────────────
            $movQuery = MovimientoPartida::with('partida')
                ->where('ejercicio_fiscal_id', $ejercicioActivo->id)
                ->where('estado', 'confirmado');
            if ($filtroPartida) $movQuery->where('partida_presupuestaria_id', $filtroPartida);
            if ($filtroTipo)    $movQuery->where('tipo', $filtroTipo);

            $ultimosMovimientos = $movQuery
                ->orderByDesc('fecha_movimiento')
                ->orderByDesc('id')
                ->take(20)
                ->get();

            // ── Causaciones recientes ──────────────────────────────
            $cauQ = Causacion::with(['partida', 'unidadEjecutora'])
                ->where('ejercicio_fiscal_id', $ejercicioActivo->id);
            if ($filtroPartida) $cauQ->where('partida_presupuestaria_id', $filtroPartida);
            $ultimasCausaciones = $cauQ->latest()->take(6)->get();

            // ── Pagos recientes ────────────────────────────────────
            $pagQ = Pago::with(['causacion', 'unidadEjecutora'])
                ->where('ejercicio_fiscal_id', $ejercicioActivo->id);
            if ($filtroPartida) {
                $pagQ->whereHas('causacion',
                    fn($q) => $q->where('partida_presupuestaria_id', $filtroPartida)
                );
            }
            $ultimosPagos = $pagQ->latest()->take(6)->get();
        }

        return view('presupuesto.reportes.ejecucion', compact(
            'ejercicioActivo', 'totales', 'porUnidad', 'porPartida',
            'ultimasCausaciones', 'ultimosPagos', 'ultimosMovimientos',
            'todasPartidas', 'partidaSeleccionada',
            'filtroPartida', 'filtroTipo'
        ));
    }

    /**
     * Despacha el reporte de ejecución a la queue para generarlo en background.
     * El usuario es redirigido a sus exportaciones donde podrá descargar el PDF.
     */
    public function exportar(Request $request)
    {
        $ejercicioActivo = EjercicioFiscal::where('estado', 'activo')->first();

        abort_unless($ejercicioActivo, 404, 'No hay un ejercicio fiscal activo.');

        ExportarReporteEjecucionJob::dispatch(
            userId:      auth()->id(),
            ejercicioId: $ejercicioActivo->id,
            filtros:     $request->only(['partida_id', 'unidad_id']),
        );

        return redirect()->route('pdf-exports.index')
            ->with('info', 'El reporte de ejecución presupuestaria se está generando. Aparecerá en esta página en unos segundos — recarga para verlo.');
    }
}
