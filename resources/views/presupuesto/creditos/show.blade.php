@extends('layouts.app')
@section('title', 'Detalle de Crédito Presupuestario')
@section('breadcrumb')
    <a href="{{ route('presupuesto.creditos.index') }}" style="color:var(--text-secondary);text-decoration:none;">Créditos</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">{{ $credito->partida?->codigo }}</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;gap:12px;">
    <a href="{{ route('presupuesto.creditos.index') }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <h1 class="page-title">Crédito Presupuestario</h1>
        <p class="page-subtitle">{{ $credito->partida?->codigo }} — {{ $credito->unidadEjecutora?->nombre }}</p>
    </div>
    @can('creditos.editar')
    <a href="{{ route('presupuesto.creditos.edit', $credito) }}" class="btn btn-outline btn-sm" style="margin-left:auto;">
        <i class="fa-solid fa-pen-to-square"></i> Editar
    </a>
    @endcan
</div>

@php
    $saldoPartida  = (float) ($credito->partida?->saldo_actual ?? 0);
    $montoVigente  = $credito->monto_vigente;
    $comprometido  = (float) $credito->monto_comprometido;
    $disponible    = $montoVigente - $comprometido;
    $pct           = $montoVigente > 0 ? ($comprometido / $montoVigente) * 100 : 0;
    $hayDiscrepancia = abs($saldoPartida - (float)$credito->monto_aprobado) > 0.01 && $saldoPartida > 0;
@endphp

{{-- ── Tarjetas KPI ───────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px;" class="fade-up">
    <div style="background:var(--bg-card);border:1px solid rgba(79,142,247,0.25);border-left:3px solid var(--accent);border-radius:14px;padding:18px;">
        <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;font-weight:600;margin-bottom:8px;">Monto Aprobado</div>
        <div style="font-size:20px;font-weight:800;font-family:monospace;">Bs. {{ number_format($credito->monto_aprobado, 2) }}</div>
        <div style="font-size:10px;color:var(--text-secondary);margin-top:4px;">Asignación original aprobada</div>
    </div>
    <div style="background:var(--bg-card);border:1px solid rgba(34,211,166,0.25);border-left:3px solid var(--accent-3);border-radius:14px;padding:18px;">
        <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;font-weight:600;margin-bottom:8px;">Presupuesto Vigente</div>
        <div style="font-size:20px;font-weight:800;font-family:monospace;color:var(--accent-3);">Bs. {{ number_format($montoVigente, 2) }}</div>
        <div style="font-size:10px;color:var(--text-secondary);margin-top:4px;">
            {{ $saldoPartida > 0 ? 'Desde saldo real de la partida' : 'Desde monto aprobado (sin movimientos)' }}
        </div>
    </div>
    <div style="background:var(--bg-card);border:1px solid rgba(247,185,79,0.25);border-left:3px solid var(--accent-warn);border-radius:14px;padding:18px;">
        <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;font-weight:600;margin-bottom:8px;">Comprometido</div>
        <div style="font-size:20px;font-weight:800;font-family:monospace;color:var(--accent-warn);">Bs. {{ number_format($comprometido, 2) }}</div>
        <div style="font-size:10px;color:var(--text-secondary);margin-top:4px;">Compromisos activos vinculados</div>
    </div>
    <div style="background:var(--bg-card);border:1px solid rgba({{ $disponible >= 0 ? '34,211,166' : '239,68,68' }},0.25);border-left:3px solid {{ $disponible >= 0 ? 'var(--accent-3)' : 'var(--accent-danger)' }};border-radius:14px;padding:18px;">
        <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;font-weight:600;margin-bottom:8px;">Disponible</div>
        <div style="font-size:20px;font-weight:800;font-family:monospace;color:{{ $disponible >= 0 ? 'var(--accent-3)' : 'var(--accent-danger)' }};">Bs. {{ number_format($disponible, 2) }}</div>
        <div style="font-size:10px;color:var(--text-secondary);margin-top:4px;">Vigente menos comprometido</div>
    </div>
</div>

{{-- Barra de ejecución --}}
<div class="card fade-up" style="padding:16px 22px;margin-bottom:20px;">
    <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--text-secondary);margin-bottom:8px;">
        <span><i class="fa-solid fa-chart-bar" style="color:var(--accent);margin-right:5px;"></i>Ejecución presupuestaria</span>
        <span style="font-weight:700;color:{{ $pct > 90 ? 'var(--accent-danger)' : ($pct > 70 ? 'var(--accent-warn)' : 'var(--accent)') }};">{{ number_format($pct, 1) }}%</span>
    </div>
    <div style="height:10px;background:rgba(255,255,255,0.08);border-radius:5px;overflow:hidden;">
        <div style="height:100%;width:{{ min($pct, 100) }}%;background:{{ $pct > 90 ? 'var(--accent-danger)' : ($pct > 70 ? 'var(--accent-warn)' : 'linear-gradient(90deg, var(--accent), var(--accent-3))') }};border-radius:5px;transition:width .6s;"></div>
    </div>
    @if($hayDiscrepancia)
    <div style="margin-top:10px;padding:8px 12px;background:rgba(247,185,79,0.08);border:1px solid rgba(247,185,79,0.2);border-radius:8px;font-size:12px;color:var(--accent-warn);display:flex;align-items:center;gap:8px;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        El saldo de la partida (Bs. {{ number_format($saldoPartida, 2) }}) difiere del monto aprobado original (Bs. {{ number_format($credito->monto_aprobado, 2) }}) — refleja movimientos realizados.
    </div>
    @endif
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;">
    {{-- Movimientos de Partida vinculados --}}
    <div class="card fade-up">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
            <div class="card-title">
                <i class="fa-solid fa-arrows-up-down" style="color:var(--accent);margin-right:8px;"></i>
                Movimientos de Partida Vinculados
            </div>
            <a href="{{ route('presupuesto.movimientos-partidas.index') }}?partida={{ $credito->partida_presupuestaria_id }}"
               class="btn btn-outline btn-sm">Ver todos</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>Número</th><th>Fecha</th><th>Tipo</th><th>Concepto</th>
                    <th style="text-align:right;">Monto</th>
                    <th style="text-align:right;">Saldo Posterior</th>
                    <th>Estado</th>
                </tr></thead>
                <tbody>
                @forelse($credito->partida?->movimientos ?? [] as $m)
                <tr>
                    <td><code style="font-size:11px;color:var(--accent);">{{ $m->numero }}</code></td>
                    <td style="font-size:12px;color:var(--text-secondary);">{{ $m->fecha_movimiento->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge {{ in_array($m->tipo, \App\Models\MovimientoPartida::TIPOS_INGRESO) ? 'badge-active' : 'badge-danger' }}" style="font-size:10px;">
                            {{ \App\Models\MovimientoPartida::etiquetaTipo($m->tipo) }}
                        </span>
                    </td>
                    <td style="font-size:12px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $m->concepto }}</td>
                    <td style="text-align:right;font-family:monospace;font-weight:700;font-size:13px;color:{{ in_array($m->tipo, \App\Models\MovimientoPartida::TIPOS_INGRESO) ? 'var(--accent-3)' : 'var(--accent-danger)' }};">
                        {{ in_array($m->tipo, \App\Models\MovimientoPartida::TIPOS_INGRESO) ? '+' : '-' }}{{ number_format($m->monto, 2) }}
                    </td>
                    <td style="text-align:right;font-family:monospace;font-size:13px;color:var(--text-secondary);">
                        {{ number_format($m->saldo_posterior, 2) }}
                    </td>
                    <td>
                        <span class="badge {{ $m->getBadgeEstadoClass() }}" style="font-size:10px;">{{ ucfirst($m->estado) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7">
                    <div class="empty-state" style="padding:30px;">
                        <div class="empty-icon">📋</div>
                        <div class="empty-title">Sin movimientos de partida</div>
                        <div class="empty-desc">Al crear este crédito se generará la asignación inicial automáticamente.</div>
                    </div>
                </td></tr>
                @endforelse
                </tbody>
                @if(($credito->partida?->movimientos ?? collect())->count() > 0)
                <tfoot>
                    <tr style="background:rgba(34,211,166,0.05);">
                        <td colspan="5" style="text-align:right;font-size:12px;font-weight:600;color:var(--text-secondary);padding:10px 16px;">Saldo actual de la partida</td>
                        <td style="text-align:right;font-family:monospace;font-weight:800;font-size:15px;color:var(--accent-3);padding:10px 16px;">
                            Bs. {{ number_format($saldoPartida, 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Info lateral --}}
    <div class="card fade-up" style="height:fit-content;">
        <div class="card-header"><div class="card-title">Información del Crédito</div></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:16px;padding:20px;">
            <div>
                <div class="form-label">Partida</div>
                <code style="font-size:12px;background:rgba(79,142,247,0.1);color:var(--accent);padding:4px 8px;border-radius:6px;">{{ $credito->partida?->codigo }}</code>
                <div style="font-size:12px;color:var(--text-secondary);margin-top:5px;">{{ $credito->partida?->descripcion }}</div>
            </div>
            <div style="background:rgba(34,211,166,0.07);border:1px solid rgba(34,211,166,0.15);border-radius:10px;padding:12px;">
                <div style="font-size:11px;color:var(--text-secondary);font-weight:600;text-transform:uppercase;margin-bottom:4px;">Saldo Actual de la Partida</div>
                <div style="font-size:18px;font-weight:800;font-family:monospace;color:var(--accent-3);">Bs. {{ number_format($saldoPartida, 2) }}</div>
                <div style="font-size:10px;color:var(--text-secondary);margin-top:3px;">Fuente de verdad del presupuesto vigente</div>
            </div>
            <div><div class="form-label">Unidad Ejecutora</div><div style="font-size:13px;font-weight:500;">{{ $credito->unidadEjecutora?->nombre }}</div></div>
            <div><div class="form-label">Ejercicio Fiscal</div><div style="font-size:13px;">{{ $credito->ejercicioFiscal?->anio }}</div></div>
            <div><div class="form-label">Fuente de Financiamiento</div><div style="font-size:13px;">{{ $credito->fuenteFinanciamiento?->nombre ?? '—' }}</div></div>
            @if($credito->observaciones)
            <div><div class="form-label">Observaciones</div><div style="font-size:12px;color:var(--text-secondary);">{{ $credito->observaciones }}</div></div>
            @endif
            <div style="border-top:1px solid var(--border);padding-top:12px;">
                <a href="{{ route('presupuesto.movimientos-partidas.create') }}?partida={{ $credito->partida_presupuestaria_id }}"
                   class="btn btn-primary" style="width:100%;justify-content:center;">
                    <i class="fa-solid fa-plus"></i> Nuevo Movimiento de Partida
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
