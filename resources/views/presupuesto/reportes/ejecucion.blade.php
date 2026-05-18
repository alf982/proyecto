@extends('layouts.app')
@section('title', $partidaSeleccionada ? 'Ejecución · '.$partidaSeleccionada->codigo : 'Ejecución Presupuestaria')
@section('breadcrumb')
    <span>Presupuesto</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    @if($partidaSeleccionada)
        <a href="{{ route('presupuesto.reportes.ejecucion') }}" style="color:var(--text-secondary);text-decoration:none;">Ejecución Presupuestaria</a>
        <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
        <span class="current">{{ $partidaSeleccionada->codigo }}</span>
    @else
        <span class="current">Ejecución Presupuestaria</span>
    @endif
@endsection

@section('content')

{{-- ── Header ─────────────────────────────────────────────────────────── --}}
<div class="page-header fade-up" style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;">
    <div>
        @if($partidaSeleccionada)
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                <a href="{{ route('presupuesto.reportes.ejecucion') }}" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <code style="background:rgba(79,142,247,0.12);color:var(--accent);padding:4px 12px;border-radius:8px;font-size:14px;font-weight:700;">
                    {{ $partidaSeleccionada->codigo }}
                </code>
            </div>
            <h1 class="page-title">{{ $partidaSeleccionada->descripcion }}</h1>
            <p class="page-subtitle">Ejecución individual · Ejercicio {{ $ejercicioActivo?->anio }}</p>
        @else
            <h1 class="page-title">Ejecución Presupuestaria</h1>
            <p class="page-subtitle">
                @if($ejercicioActivo)
                    Ejercicio Fiscal <strong>{{ $ejercicioActivo->anio }}</strong>
                    &nbsp;<span class="badge badge-active" style="font-size:11px;">Activo</span>
                    <span style="font-size:11px;color:var(--text-secondary);margin-left:8px;">{{ now()->format('d/m/Y H:i') }}</span>
                @else
                    <span style="color:var(--accent-danger);">No hay ejercicio fiscal activo</span>
                @endif
            </p>
        @endif
    </div>

    {{-- Selector de partida + Exportar PDF --}}
    @if($ejercicioActivo)
    <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">

        @if($todasPartidas->count())
        <div style="min-width:300px;">
            <form method="GET" action="{{ route('presupuesto.reportes.ejecucion') }}" id="form-filtro-partida">
                <div style="display:flex;gap:8px;align-items:center;">
                    <div style="flex:1;">
                        <label style="font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;display:block;margin-bottom:4px;">
                            <i class="fa-solid fa-layer-group" style="margin-right:3px;"></i>Filtrar por Partida
                        </label>
                        <select name="partida_id" class="form-control" style="font-size:12px;" onchange="this.form.submit()" id="select-partida">
                            <option value="">— Todas las partidas —</option>
                            @foreach($todasPartidas as $p)
                            <option value="{{ $p->id }}"
                                {{ $filtroPartida == $p->id ? 'selected' : '' }}>
                                {{ $p->codigo }} · {{ Str::limit($p->descripcion, 30) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @if($filtroPartida)
                    <a href="{{ route('presupuesto.reportes.ejecucion') }}" class="btn btn-outline btn-sm" style="margin-top:18px;" title="Ver todas">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
        @endif

        {{-- Botón Exportar PDF (asíncrono vía queue) --}}
        <form method="POST" action="{{ route('presupuesto.reportes.ejecucion.exportar') }}" style="margin-top:18px;">
            @csrf
            @if($filtroPartida)
            <input type="hidden" name="partida_id" value="{{ $filtroPartida }}">
            @endif
            <button type="submit" class="btn btn-outline btn-sm"
                    title="Genera el PDF en segundo plano. Disponible en Mis Exportaciones.">
                <i class="fa-solid fa-file-pdf" style="color:#e53e3e;margin-right:5px;"></i>
                Exportar PDF
            </button>
        </form>

    </div>
    @endif
</div>

@if($ejercicioActivo)

{{-- ── Banner de partida seleccionada ─────────────────────────────────── --}}
@if($partidaSeleccionada)
<div class="fade-up" style="background:rgba(79,142,247,0.06);border:1px solid rgba(79,142,247,0.2);border-radius:14px;padding:16px 22px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:16px;">
    <div style="display:flex;align-items:center;gap:16px;">
        <div style="width:44px;height:44px;border-radius:12px;background:rgba(79,142,247,0.12);display:grid;place-items:center;">
            <i class="fa-solid fa-layer-group" style="color:var(--accent);font-size:18px;"></i>
        </div>
        <div>
            <div style="font-size:13px;font-weight:700;">Partida {{ $partidaSeleccionada->codigo }}</div>
            <div style="font-size:12px;color:var(--text-secondary);">{{ $partidaSeleccionada->descripcion }}</div>
        </div>
    </div>
    <a href="{{ route('presupuesto.movimientos-partidas.create') }}?partida={{ $partidaSeleccionada->id }}"
       class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus"></i> Nuevo Movimiento
    </a>
</div>
@endif

{{-- ── KPIs ─────────────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;margin-bottom:24px;" class="fade-up">
        {{-- KPI: Aprobado — oculto por configuración del administrador --}}
    {{-- <div class="card" style="padding:20px;border-left:3px solid var(--accent);">
        <div style="font-size:11px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:8px;">
            <i class="fa-solid fa-file-check" style="color:var(--accent);margin-right:4px;"></i>Aprobado
        </div>
        <div style="font-size:19px;font-weight:800;font-family:monospace;color:var(--accent);">
            {{ number_format($totales['aprobado'], 2) }}
        </div>
        <div style="font-size:10px;color:var(--text-secondary);margin-top:4px;">Bs. — monto total aprobado</div>
    </div> --}}

    {{-- KPI: Vigente --}}
    <div class="card" style="padding:20px;border-left:3px solid #818cf8;">
        <div style="font-size:11px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:8px;">
            <i class="fa-solid fa-scale-balanced" style="color:#818cf8;margin-right:4px;"></i>Vigente
        </div>
        <div style="font-size:19px;font-weight:800;font-family:monospace;color:#818cf8;">
            {{ number_format($totales['vigente'], 2) }}
        </div>
        <div style="font-size:10px;color:var(--text-secondary);margin-top:4px;">Bs. — crédito vigente autorizado</div>
    </div>

    {{-- KPI: Comprometido --}}
    <div class="card" style="padding:20px;border-left:3px solid var(--accent-warn);">
        <div style="font-size:11px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:8px;">
            <i class="fa-solid fa-handshake" style="color:var(--accent-warn);margin-right:4px;"></i>Comprometido
        </div>
        <div style="font-size:19px;font-weight:800;font-family:monospace;color:var(--accent-warn);">
            {{ number_format($totales['comprometido'], 2) }}
        </div>
        <div style="font-size:10px;color:var(--text-secondary);margin-top:4px;">Bs. — {{ $totales['pct_comprometido'] }}% del vigente</div>
    </div>

    {{-- KPI: Causado --}}
    <div class="card" style="padding:20px;border-left:3px solid var(--accent-2);">
        <div style="font-size:11px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:8px;">
            <i class="fa-solid fa-file-invoice-dollar" style="color:var(--accent-2);margin-right:4px;"></i>Causado
        </div>
        <div style="font-size:19px;font-weight:800;font-family:monospace;color:var(--accent-2);">
            {{ number_format($totales['causado'], 2) }}
        </div>
        <div style="font-size:10px;color:var(--text-secondary);margin-top:4px;">Bs. — {{ $totales['pct_ejec'] }}% del vigente</div>
    </div>

    {{-- KPI: Pagado --}}
    <div class="card" style="padding:20px;border-left:3px solid #10b981;">
        <div style="font-size:11px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:8px;">
            <i class="fa-solid fa-money-bill-wave" style="color:#10b981;margin-right:4px;"></i>Pagado
        </div>
        <div style="font-size:19px;font-weight:800;font-family:monospace;color:#10b981;">
            {{ number_format($totales['pagado'], 2) }}
        </div>
        <div style="font-size:10px;color:var(--text-secondary);margin-top:4px;">Bs. — pagos procesados</div>
    </div>

    {{-- KPI: Disponible --}}
    <div class="card" style="padding:20px;border-left:3px solid {{ $totales['disponible'] < 0 ? 'var(--accent-danger)' : 'var(--accent-3)' }};">
        <div style="font-size:11px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:8px;">
            <i class="fa-solid fa-wallet" style="color:{{ $totales['disponible'] < 0 ? 'var(--accent-danger)' : 'var(--accent-3)' }};margin-right:4px;"></i>Disponible
        </div>
        <div style="font-size:19px;font-weight:800;font-family:monospace;color:{{ $totales['disponible'] < 0 ? 'var(--accent-danger)' : 'var(--accent-3)' }};">
            {{ number_format($totales['disponible'], 2) }}
        </div>
        <div style="font-size:10px;color:var(--text-secondary);margin-top:4px;">Bs. — {{ $totales['pct_disponible'] }}% del vigente · saldo libre real</div>
    </div>
</div>

{{-- ── Barra de ejecución ───────────────────────────────────────────────── --}}
    <div class="card fade-up" style="margin-bottom:24px;animation-delay:.04s;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-chart-bar" style="color:var(--accent);margin-right:8px;"></i>
            Ejecución {{ $partidaSeleccionada ? 'de la Partida' : 'Global' }}
        </div>
        <span style="font-size:12px;color:var(--text-secondary);">Causado / Vigente</span>
    </div>
    <div class="card-body" style="padding:20px 24px;">
        <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
            <div style="font-size:52px;font-weight:900;color:{{ $totales['pct_ejec'] > 90 ? 'var(--accent-danger)' : ($totales['pct_ejec'] > 70 ? 'var(--accent-warn)' : 'var(--accent)') }};min-width:100px;line-height:1;">
                {{ $totales['pct_ejec'] }}<span style="font-size:24px;">%</span>
            </div>
            <div style="flex:1;min-width:220px;">
                <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-secondary);margin-bottom:4px;">
                    <span>Causado ({{ $totales['pct_ejec'] }}%)</span>
                    <span>Bs. {{ number_format($totales['causado'],2) }}</span>
                </div>
                <div style="background:rgba(255,255,255,0.08);border-radius:100px;height:12px;overflow:hidden;margin-bottom:10px;">
                    <div style="width:{{ min($totales['pct_ejec'],100) }}%;height:100%;border-radius:100px;
                        background:linear-gradient(90deg,var(--accent),var(--accent-2));transition:width .8s;"></div>
                </div>

                <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-secondary);margin-bottom:4px;">
                    <span>Comprometido ({{ $totales['pct_comprometido'] }}%)</span>
                    <span>Bs. {{ number_format($totales['comprometido'],2) }}</span>
                </div>
                <div style="background:rgba(255,255,255,0.08);border-radius:100px;height:7px;overflow:hidden;margin-bottom:10px;">
                    <div style="width:{{ min($totales['pct_comprometido'],100) }}%;height:100%;border-radius:100px;
                        background:linear-gradient(90deg,var(--accent-warn),#f59e0b);transition:width .8s;"></div>
                </div>

                <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-secondary);margin-bottom:4px;">
                    <span style="color:var(--accent-3);">Disponible ({{ $totales['pct_disponible'] }}%)</span>
                    <span style="color:var(--accent-3);font-weight:700;">Bs. {{ number_format($totales['disponible'],2) }}</span>
                </div>
                <div style="background:rgba(255,255,255,0.08);border-radius:100px;height:7px;overflow:hidden;margin-bottom:14px;">
                    <div style="width:{{ min($totales['pct_disponible'],100) }}%;height:100%;border-radius:100px;
                        background:linear-gradient(90deg,var(--accent-3),#34d399);transition:width .8s;"></div>
                </div>

                <div style="font-size:11px;color:var(--text-secondary);display:flex;gap:16px;flex-wrap:wrap;">
                    <span>Vigente: <strong style="color:#818cf8;">Bs. {{ number_format($totales['vigente'],2) }}</strong></span>
                    @if(!$partidaSeleccionada)
                    <span>Partidas: <strong>{{ $porPartida->count() }}</strong></span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── VISTA INDIVIDUAL: detalle de partida seleccionada ───────────────── --}}
@if($partidaSeleccionada)

    {{-- Movimientos de partida con filtro de tipo --}}
    <div class="card fade-up" style="margin-bottom:24px;animation-delay:.07s;">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <div class="card-title">
                <i class="fa-solid fa-arrows-up-down" style="color:var(--accent);margin-right:8px;"></i>
                Movimientos de la Partida {{ $partidaSeleccionada->codigo }}
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <form method="GET" action="{{ route('presupuesto.reportes.ejecucion') }}" style="display:flex;gap:8px;align-items:center;">
                    <input type="hidden" name="partida_id" value="{{ $filtroPartida }}">
                    <select name="tipo_movimiento" class="form-control" style="font-size:12px;min-width:160px;" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        @foreach(['asignacion','credito_adicional','modificacion_entrada','modificacion_salida','ejecucion','reintegro','nota_credito','nota_debito'] as $tipo)
                        <option value="{{ $tipo }}" {{ $filtroTipo === $tipo ? 'selected' : '' }}>
                            {{ \App\Models\MovimientoPartida::etiquetaTipo($tipo) }}
                        </option>
                        @endforeach
                    </select>
                    @if($filtroTipo)
                    <a href="{{ route('presupuesto.reportes.ejecucion') }}?partida_id={{ $filtroPartida }}" class="btn btn-outline btn-sm" title="Quitar filtro">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                    @endif
                </form>
                <a href="{{ route('presupuesto.movimientos-partidas.index') }}?partida={{ $filtroPartida }}" class="btn btn-outline btn-sm">
                    Ver todos <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>Número</th><th>Fecha</th><th>Tipo</th><th>Concepto</th>
                    <th style="text-align:right;">Monto</th>
                    <th style="text-align:right;">Saldo Anterior</th>
                    <th style="text-align:right;">Saldo Posterior</th>
                </tr></thead>
                <tbody>
                @forelse($ultimosMovimientos as $m)
                @php $esIngreso = in_array($m->tipo, \App\Models\MovimientoPartida::TIPOS_INGRESO); @endphp
                <tr>
                    <td><code style="font-size:11px;color:var(--accent);">{{ $m->numero }}</code></td>
                    <td style="font-size:12px;color:var(--text-secondary);">{{ $m->fecha_movimiento->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge {{ $esIngreso ? 'badge-active' : 'badge-danger' }}" style="font-size:10px;">
                            {{ \App\Models\MovimientoPartida::etiquetaTipo($m->tipo) }}
                        </span>
                    </td>
                    <td style="font-size:12px;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $m->concepto }}</td>
                    <td style="text-align:right;font-family:monospace;font-size:13px;font-weight:700;color:{{ $esIngreso ? 'var(--accent-3)' : 'var(--accent-danger)' }};">
                        {{ $esIngreso ? '+' : '-' }}{{ number_format($m->monto, 2) }}
                    </td>
                    <td style="text-align:right;font-family:monospace;font-size:12px;color:var(--text-secondary);">{{ number_format($m->saldo_anterior, 2) }}</td>
                    <td style="text-align:right;font-family:monospace;font-size:13px;font-weight:700;color:var(--accent-3);">{{ number_format($m->saldo_posterior, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="7">
                    <div class="empty-state" style="padding:30px;">
                        <div class="empty-icon">📋</div>
                        <div class="empty-title">Sin movimientos</div>
                        <div class="empty-desc">No hay movimientos registrados para esta partida{{ $filtroTipo ? ' con el tipo seleccionado' : '' }}.</div>
                    </div>
                </td></tr>
                @endforelse
                </tbody>
                @if($ultimosMovimientos->count())
                <tfoot>
                    <tr style="background:rgba(34,211,166,0.05);">
                        <td colspan="6" style="text-align:right;font-size:12px;font-weight:700;color:var(--text-secondary);padding:10px 16px;">
                            Saldo disponible de la partida
                        </td>
                        <td style="text-align:right;font-family:monospace;font-weight:800;font-size:16px;color:var(--accent-3);padding:10px 16px;">
                            Bs. {{ number_format($totales['disponible'], 2) }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Causaciones y pagos de la partida --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" class="fade-up">
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-file-invoice-dollar" style="color:var(--accent-2);margin-right:8px;"></i>Causaciones de esta Partida</div>
                <a href="{{ route('presupuesto.causaciones.index') }}" style="font-size:12px;color:var(--accent);">Ver todas →</a>
            </div>
            <div class="table-wrap">
                <table><tbody>
                @forelse($ultimasCausaciones as $c)
                <tr>
                    <td><code style="font-size:11px;color:var(--accent-2);">{{ $c->numero }}</code></td>
                    <td style="font-size:12px;max-width:110px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $c->beneficiario }}</td>
                    <td style="text-align:right;font-family:monospace;font-size:12px;font-weight:700;">{{ number_format($c->monto_causado,2) }}</td>
                    <td><span class="badge {{ $c->getEstadoBadgeClass() }}" style="font-size:10px;">{{ $c->getEstadoLabel() }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:var(--text-secondary);font-size:13px;padding:20px;">Sin causaciones en esta partida</td></tr>
                @endforelse
                </tbody></table>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-money-bill-wave" style="color:#10b981;margin-right:8px;"></i>Pagos de esta Partida</div>
                <a href="{{ route('presupuesto.pagos.index') }}" style="font-size:12px;color:var(--accent);">Ver todos →</a>
            </div>
            <div class="table-wrap">
                <table><tbody>
                @forelse($ultimosPagos as $p)
                <tr>
                    <td><code style="font-size:11px;color:#10b981;">{{ $p->numero }}</code></td>
                    <td style="font-size:12px;max-width:110px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $p->beneficiario }}</td>
                    <td style="text-align:right;font-family:monospace;font-size:12px;font-weight:700;">{{ number_format($p->monto_pagado,2) }}</td>
                    <td><span class="badge {{ $p->getEstadoBadgeClass() }}" style="font-size:10px;">{{ $p->getEstadoLabel() }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:var(--text-secondary);font-size:13px;padding:20px;">Sin pagos en esta partida</td></tr>
                @endforelse
                </tbody></table>
            </div>
        </div>
    </div>

@else
{{-- ── VISTA GLOBAL ─────────────────────────────────────────────────────── --}}

    {{-- Selector rápido de partidas (tarjetas clickeables) --}}
    @if($porPartida->count())
    <div class="card fade-up" style="margin-bottom:24px;animation-delay:.06s;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-layer-group" style="color:var(--accent-3);margin-right:8px;"></i>
                Partidas Presupuestarias — Selecciona para ver detalle
            </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px;padding:16px;">
            @foreach($porPartida as $p)
            @php
                $pctP = $p['vigente'] > 0 ? round(($p['comprometido']/$p['vigente'])*100,1) : 0;
                $colorBarra = $pctP > 90 ? 'var(--accent-danger)' : ($pctP > 70 ? 'var(--accent-warn)' : 'var(--accent-3)');
            @endphp
            <a href="{{ route('presupuesto.reportes.ejecucion') }}?partida_id={{ $p['partida_id'] }}"
               style="display:block;background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:12px;padding:16px;text-decoration:none;color:inherit;transition:all .2s;"
               onmouseover="this.style.borderColor='var(--accent)';this.style.background='rgba(79,142,247,0.06)'"
               onmouseout="this.style.borderColor='var(--border)';this.style.background='rgba(255,255,255,0.03)'">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px;">
                    <div>
                        <code style="font-size:12px;background:rgba(79,142,247,0.1);color:var(--accent);padding:3px 8px;border-radius:5px;font-weight:700;">
                            {{ $p['codigo'] }}
                        </code>
                        <div style="font-size:11px;color:var(--text-secondary);margin-top:6px;line-height:1.4;">
                            {{ Str::limit($p['descripcion'], 40) }}
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right" style="color:var(--text-secondary);font-size:11px;margin-top:2px;"></i>
                </div>
                <div style="font-size:16px;font-weight:800;font-family:monospace;color:var(--accent-3);margin-bottom:4px;">
                    Bs. {{ number_format($p['saldo_real'], 2) }}
                </div>
                <div style="font-size:10px;color:var(--text-secondary);margin-bottom:8px;">
                    Saldo real · Disponible: <span style="color:{{ $p['disponible'] < 0 ? 'var(--accent-danger)' : 'var(--text-primary)' }};font-weight:600;">{{ number_format($p['disponible'],2) }}</span>
                </div>
                <div style="background:rgba(255,255,255,0.08);border-radius:100px;height:5px;overflow:hidden;">
                    <div style="width:{{ min($pctP,100) }}%;height:100%;border-radius:100px;background:{{ $colorBarra }};"></div>
                </div>
                <div style="font-size:10px;color:var(--text-secondary);margin-top:4px;text-align:right;">{{ $pctP }}% comprometido</div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Por Unidad Ejecutora --}}
    @if($porUnidad->count())
    <div class="card fade-up" style="margin-bottom:24px;animation-delay:.09s;">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-sitemap" style="color:var(--accent-2);margin-right:8px;"></i>Ejecución por Unidad Ejecutora
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>Unidad</th>
                    <th style="text-align:right;">Saldo Real Partidas</th>
                    <th style="text-align:right;">Comprometido</th>
                    <th style="text-align:right;">Disponible</th>
                    <th>% Comprometido</th>
                </tr></thead>
                <tbody>
                @foreach($porUnidad as $u)
                @php $pct = $u['vigente'] > 0 ? round(($u['comprometido']/$u['vigente'])*100,1) : 0; @endphp
                <tr>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $u['nombre'] }}</div>
                        <div style="font-size:11px;color:var(--text-secondary);">{{ $u['codigo'] }} · {{ $u['num_partidas'] }} partida(s)</div>
                    </td>
                    <td style="text-align:right;font-family:monospace;font-size:13px;font-weight:700;color:var(--accent-3);">{{ number_format($u['saldo_real'],2) }}</td>
                    <td style="text-align:right;font-family:monospace;font-size:13px;color:var(--accent-warn);">{{ number_format($u['comprometido'],2) }}</td>
                    <td style="text-align:right;font-family:monospace;font-size:13px;font-weight:700;color:{{ $u['disponible'] < 0 ? 'var(--accent-danger)' : 'var(--accent-3)' }};">
                        {{ number_format($u['disponible'],2) }}
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;background:rgba(255,255,255,0.08);border-radius:100px;height:8px;overflow:hidden;">
                                <div style="width:{{ min($pct,100) }}%;height:100%;border-radius:100px;
                                    background:{{ $pct > 90 ? 'var(--accent-danger)' : ($pct > 70 ? 'var(--accent-warn)' : 'linear-gradient(90deg,var(--accent),var(--accent-warn))') }};"></div>
                            </div>
                            <span style="font-size:11px;font-weight:700;min-width:36px;">{{ $pct }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Últimos movimientos globales --}}
    @if($ultimosMovimientos->count())
    <div class="card fade-up" style="margin-bottom:24px;animation-delay:.11s;">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
            <div class="card-title">
                <i class="fa-solid fa-arrows-up-down" style="color:var(--accent);margin-right:8px;"></i>
                Últimos Movimientos de Partida
            </div>
            <a href="{{ route('presupuesto.movimientos-partidas.index') }}" style="font-size:12px;color:var(--accent);">Ver todos →</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>Número</th><th>Fecha</th><th>Partida</th><th>Tipo</th><th>Concepto</th>
                    <th style="text-align:right;">Monto</th>
                    <th style="text-align:right;">Saldo Post.</th>
                </tr></thead>
                <tbody>
                @foreach($ultimosMovimientos as $m)
                @php $esIngreso = in_array($m->tipo, \App\Models\MovimientoPartida::TIPOS_INGRESO); @endphp
                <tr>
                    <td><code style="font-size:11px;color:var(--accent);">{{ $m->numero }}</code></td>
                    <td style="font-size:12px;color:var(--text-secondary);">{{ $m->fecha_movimiento->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('presupuesto.reportes.ejecucion') }}?partida_id={{ $m->partida_presupuestaria_id }}"
                           style="text-decoration:none;">
                            <code style="font-size:11px;background:rgba(79,142,247,0.1);color:var(--accent);padding:2px 6px;border-radius:4px;">
                                {{ $m->partida?->codigo ?? '—' }}
                            </code>
                        </a>
                    </td>
                    <td>
                        <span class="badge {{ $esIngreso ? 'badge-active' : 'badge-danger' }}" style="font-size:10px;">
                            {{ \App\Models\MovimientoPartida::etiquetaTipo($m->tipo) }}
                        </span>
                    </td>
                    <td style="font-size:12px;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $m->concepto }}</td>
                    <td style="text-align:right;font-family:monospace;font-size:13px;font-weight:700;color:{{ $esIngreso ? 'var(--accent-3)' : 'var(--accent-danger)' }};">
                        {{ $esIngreso ? '+' : '-' }}{{ number_format($m->monto,2) }}
                    </td>
                    <td style="text-align:right;font-family:monospace;font-size:12px;color:var(--text-secondary);">
                        {{ number_format($m->saldo_posterior,2) }}
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Causaciones y pagos globales --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" class="fade-up">
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-file-invoice-dollar" style="color:var(--accent-2);margin-right:8px;"></i>Últimas Causaciones</div>
                <a href="{{ route('presupuesto.causaciones.index') }}" style="font-size:12px;color:var(--accent);">Ver todas →</a>
            </div>
            <div class="table-wrap">
                <table><tbody>
                @forelse($ultimasCausaciones as $c)
                <tr>
                    <td><code style="font-size:11px;color:var(--accent-2);">{{ $c->numero }}</code></td>
                    <td style="font-size:12px;max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $c->beneficiario }}</td>
                    <td style="text-align:right;font-family:monospace;font-size:12px;font-weight:700;">{{ number_format($c->monto_causado,2) }}</td>
                    <td><span class="badge {{ $c->getEstadoBadgeClass() }}" style="font-size:10px;">{{ $c->getEstadoLabel() }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:var(--text-secondary);font-size:13px;padding:20px;">Sin causaciones</td></tr>
                @endforelse
                </tbody></table>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-money-bill-wave" style="color:#10b981;margin-right:8px;"></i>Últimos Pagos</div>
                <a href="{{ route('presupuesto.pagos.index') }}" style="font-size:12px;color:var(--accent);">Ver todos →</a>
            </div>
            <div class="table-wrap">
                <table><tbody>
                @forelse($ultimosPagos as $p)
                <tr>
                    <td><code style="font-size:11px;color:#10b981;">{{ $p->numero }}</code></td>
                    <td style="font-size:12px;max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $p->beneficiario }}</td>
                    <td style="text-align:right;font-family:monospace;font-size:12px;font-weight:700;">{{ number_format($p->monto_pagado,2) }}</td>
                    <td><span class="badge {{ $p->getEstadoBadgeClass() }}" style="font-size:10px;">{{ ucfirst($p->estado) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:var(--text-secondary);font-size:13px;padding:20px;">Sin pagos</td></tr>
                @endforelse
                </tbody></table>
            </div>
        </div>
    </div>

@endif {{-- fin if/else partidaSeleccionada --}}

@else
{{-- Sin ejercicio activo --}}
<div class="card fade-up">
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-icon">📊</div>
            <div class="empty-title">Sin ejercicio fiscal activo</div>
            <div class="empty-desc">Activa un ejercicio fiscal para ver los reportes de ejecución.</div>
            <a href="{{ route('presupuesto.ejercicios.index') }}" class="btn btn-primary" style="margin-top:16px;">
                Ir a Ejercicios Fiscales
            </a>
        </div>
    </div>
</div>
@endif

@endsection
