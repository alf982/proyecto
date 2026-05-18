<?php

namespace App\Jobs;

use App\Models\Causacion;
use App\Models\Compromiso;
use App\Models\CreditoPresupuestario;
use App\Models\EjercicioFiscal;
use App\Models\MovimientoPartida;
use App\Models\Pago;
use App\Models\PartidaPresupuestaria;
use App\Models\UnidadEjecutora;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Job para exportar el Reporte de Ejecución Presupuestaria a PDF en segundo plano.
 *
 * El reporte puede involucrar muchas queries agrupadas sobre grandes volúmenes
 * de datos — no debe bloquear el HTTP request principal.
 *
 * Uso:
 *   ExportarReporteEjecucionJob::dispatch($userId, $ejercicioId, $filtros);
 */
class ExportarReporteEjecucionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 180; // 3 minutos

    public function __construct(
        private readonly int   $userId,
        private readonly int   $ejercicioId,
        private readonly array $filtros = [],
    ) {}

    public function handle(): void
    {
        Log::info("ExportarReporteEjecucionJob: Iniciando para usuario #{$this->userId}, ejercicio #{$this->ejercicioId}");

        $ejercicio      = EjercicioFiscal::find($this->ejercicioId);
        $filtroPartida  = $this->filtros['partida_id'] ?? null;
        $filtroUnidad   = $this->filtros['unidad_id'] ?? null;

        // ── Créditos y partidas ──────────────────────────────────────
        $creditos = CreditoPresupuestario::with('partida')
            ->where('ejercicio_fiscal_id', $this->ejercicioId)
            ->when($filtroPartida, fn($q) => $q->where('partida_presupuestaria_id', $filtroPartida))
            ->get();

        $partidaIds = $creditos->pluck('partida_presupuestaria_id')->unique()->filter()->values();

        $qPartidas = $partidaIds->isNotEmpty()
            ? PartidaPresupuestaria::whereIn('id', $partidaIds)
            : PartidaPresupuestaria::activas()->where('saldo_actual', '>', 0);

        if ($filtroPartida) {
            $qPartidas->where('id', $filtroPartida);
        }

        // ── Totales ──────────────────────────────────────────────────
        $totalAprobado   = (float) (clone $qPartidas)->sum('monto_aprobado') ?: (float) (clone $qPartidas)->sum('saldo_actual');
        $totalVigente    = (float) (clone $qPartidas)->sum('monto_vigente') ?: $totalAprobado;
        $totalDisponible = (float) (clone $qPartidas)->sum('saldo_actual');

        $totalComprometido = (float) Compromiso::where('ejercicio_fiscal_id', $this->ejercicioId)
            ->whereIn('estado', ['borrador', 'aprobado'])
            ->when($filtroPartida, fn($q) => $q->where('partida_presupuestaria_id', $filtroPartida))
            ->sum('monto');

        $totalCausado = (float) Causacion::where('ejercicio_fiscal_id', $this->ejercicioId)
            ->where('estado', 'aprobada')
            ->when($filtroPartida, fn($q) => $q->where('partida_presupuestaria_id', $filtroPartida))
            ->sum('monto_causado');

        $totalPagado = (float) Pago::where('ejercicio_fiscal_id', $this->ejercicioId)
            ->where('estado', 'procesado')
            ->when($filtroPartida, fn($q) => $q->whereHas('causacion', fn($q) => $q->where('partida_presupuestaria_id', $filtroPartida)))
            ->sum('monto_pagado');

        $totales = [
            'aprobado'         => $totalAprobado,
            'vigente'          => $totalVigente,
            'disponible'       => $totalDisponible,
            'comprometido'     => $totalComprometido,
            'causado'          => $totalCausado,
            'pagado'           => $totalPagado,
            'pct_ejec'         => $totalVigente > 0 ? round(($totalCausado      / $totalVigente) * 100, 1) : 0,
            'pct_comprometido' => $totalVigente > 0 ? round(($totalComprometido / $totalVigente) * 100, 1) : 0,
            'pct_disponible'   => $totalVigente > 0 ? round(($totalDisponible   / $totalVigente) * 100, 1) : 0,
        ];

        // ── Por partida (resumen) ────────────────────────────────────
        $comprometidoPorPartida = Compromiso::where('ejercicio_fiscal_id', $this->ejercicioId)
            ->whereIn('estado', ['borrador', 'aprobado'])
            ->when($filtroPartida, fn($q) => $q->where('partida_presupuestaria_id', $filtroPartida))
            ->select('partida_presupuestaria_id', DB::raw('SUM(monto) as total_comprometido'))
            ->groupBy('partida_presupuestaria_id')
            ->pluck('total_comprometido', 'partida_presupuestaria_id');

        $partidasParaListar = $creditos->filter(fn($c) => $c->partida !== null)->pluck('partida')->unique('id');
        if ($partidasParaListar->isEmpty()) {
            $qBase = PartidaPresupuestaria::activas()->where('saldo_actual', '>', 0);
            if ($filtroPartida) $qBase->where('id', $filtroPartida);
            $partidasParaListar = $qBase->get();
        }

        $porPartida = $partidasParaListar->map(function ($p) use ($comprometidoPorPartida) {
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
                'disponible'   => $saldoReal,
                'comprometido' => $comprometido,
            ];
        })->sortByDesc('vigente')->values();

        // ── Movimientos recientes ────────────────────────────────────
        $ultimosMovimientos = MovimientoPartida::with('partida')
            ->where('ejercicio_fiscal_id', $this->ejercicioId)
            ->where('estado', 'confirmado')
            ->when($filtroPartida, fn($q) => $q->where('partida_presupuestaria_id', $filtroPartida))
            ->orderByDesc('fecha_movimiento')
            ->take(30)
            ->get();

        $nombreArchivo = 'reporte-ejecucion-' . $ejercicio?->anio . '-' . now()->format('d-m-Y') . '.pdf';

        $pdf = Pdf::loadView('pdf.reporte_ejecucion', compact(
            'ejercicio', 'totales', 'porPartida', 'ultimosMovimientos'
        ))->setPaper('legal', 'landscape');

        $directorio = "pdf-exports/{$this->userId}";
        Storage::makeDirectory($directorio);
        Storage::put("{$directorio}/{$nombreArchivo}", $pdf->output());

        Log::info("ExportarReporteEjecucionJob: {$nombreArchivo} generado para usuario #{$this->userId}");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("ExportarReporteEjecucionJob: Falló para usuario #{$this->userId}: {$e->getMessage()}");
    }
}
