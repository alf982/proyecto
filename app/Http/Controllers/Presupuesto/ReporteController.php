<?php
namespace App\Http\Controllers\Presupuesto;

use App\Http\Controllers\Controller;
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

class ReporteController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:presupuesto.reportes'),
        ];
    }

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

            // ── Por unidad ejecutora (solo vista global) ───────────
            if (! $filtroPartida) {
                $porUnidad = UnidadEjecutora::activas()
                    ->when($filtroUnidad, fn($q) => $q->where('id', $filtroUnidad))
                    ->get()
                    ->map(function ($u) use ($ejercicioActivo) {
                        // IDs de partidas con saldo asociadas a esta unidad via créditos
                        $pIds = PartidaPresupuestaria::activas()
                            ->where('saldo_actual', '>', 0)
                            ->whereHas('creditosPresupuestarios', fn($q) => $q
                                ->where('ejercicio_fiscal_id', $ejercicioActivo->id)
                                ->where('unidad_ejecutora_id', $u->id)
                            )->pluck('id');

                        // Fallback: partidas via compromisos si no hay créditos
                        if ($pIds->isEmpty()) {
                            $pIds = Compromiso::where('ejercicio_fiscal_id', $ejercicioActivo->id)
                                ->where('unidad_ejecutora_id', $u->id)
                                ->whereIn('estado', ['borrador', 'aprobado'])
                                ->pluck('partida_presupuestaria_id')
                                ->unique()->filter()->values();
                        }

                        if ($pIds->isEmpty()) return null;

                        $saldoReal    = (float) PartidaPresupuestaria::whereIn('id', $pIds)->sum('saldo_actual');
                        $aprobado     = (float) PartidaPresupuestaria::whereIn('id', $pIds)->sum('monto_aprobado');
                        $comprometido = (float) Compromiso::where('ejercicio_fiscal_id', $ejercicioActivo->id)
                            ->where('unidad_ejecutora_id', $u->id)
                            ->whereIn('estado', ['borrador', 'aprobado'])
                            ->sum('monto');

                        $vigente = $saldoReal > 0 ? $saldoReal : $aprobado;

                        return [
                            'nombre'       => $u->nombre,
                            'codigo'       => $u->codigo,
                            'vigente'      => $vigente,
                            'saldo_real'   => $saldoReal,
                            'comprometido' => $comprometido,
                            'disponible'   => $vigente - $comprometido,
                            'num_partidas' => $pIds->count(),
                        ];
                    })
                    ->filter(fn($u) => $u && $u['vigente'] > 0)
                    ->sortByDesc('vigente')
                    ->values();
            }

            // ── Por partida ────────────────────────────────────────
            // Usa créditos si existen; si no, partidas con saldo directamente
            $partidasParaListar = $creditos->filter(fn($c) => $c->partida !== null)
                ->pluck('partida')->unique('id');

            if ($partidasParaListar->isEmpty()) {
                $qBase = PartidaPresupuestaria::activas()->where('saldo_actual', '>', 0);
                if ($filtroPartida) $qBase->where('id', $filtroPartida);
                $partidasParaListar = $qBase->get();
            }

            $porPartida = $partidasParaListar->map(function ($p) use ($ejercicioActivo) {
                $saldoReal    = (float)($p->saldo_actual ?? 0);
                $aprobado     = (float)($p->monto_aprobado ?? 0);
                $vigente      = (float)($p->monto_vigente ?? 0);
                // Si monto_vigente no fue calculado aun, usar aprobado como fallback
                if ($vigente === 0.0) $vigente = $aprobado > 0 ? $aprobado : $saldoReal;

                $comprometido = (float) Compromiso::where('partida_presupuestaria_id', $p->id)
                    ->where('ejercicio_fiscal_id', $ejercicioActivo->id)
                    ->whereIn('estado', ['borrador', 'aprobado'])
                    ->sum('monto');

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
}
