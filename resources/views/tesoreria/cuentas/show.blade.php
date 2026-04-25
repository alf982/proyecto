@extends('layouts.app')
@section('title','Cuenta: '.$cuenta->nombre)
@section('breadcrumb')
    <span>Tesorería</span><i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <a href="{{ route('tesoreria.cuentas.index') }}">Cuentas</a><i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">{{ $cuenta->codigo }}</span>
@endsection
@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">{{ $cuenta->nombre }}</h1>
        <p class="page-subtitle">{{ $cuenta->banco }} · <code>{{ $cuenta->numero_cuenta }}</code></p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('tesoreria.cuentas.edit', $cuenta) }}" class="btn btn-outline"><i class="fa-solid fa-pen-to-square"></i> Editar</a>
    </div>
</div>

{{-- Stats --}}
@php
    $diferencia = round((float)$saldoDesdePartidas - (float)$cuenta->saldo_actual, 2);
    $sincronizado = abs($diferencia) < 0.01;
@endphp
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:24px;">
    {{-- Saldo total --}}
    <div class="card fade-up" style="border-left:3px solid var(--accent-3);">
        <div class="card-body" style="padding:14px 18px;">
            <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;font-weight:600;margin-bottom:4px;">Saldo Total Disponible</div>
            <div style="font-size:22px;font-weight:800;font-family:monospace;color:var(--accent-3);">
                Bs. {{ number_format($saldoDesdePartidas, 2) }}
            </div>
            <div style="font-size:10px;color:var(--text-secondary);margin-top:3px;">Suma de {{ $totalPartidas }} partida(s)</div>
        </div>
    </div>

    {{-- Saldo por cada partida vinculada --}}
    @foreach($cuenta->partidasPresupuestarias as $pStat)
    <div class="card fade-up" style="border-left:3px solid var(--accent);animation-delay:.03s;">
        <div class="card-body" style="padding:14px 18px;">
            <div style="font-size:10px;color:var(--text-secondary);text-transform:uppercase;font-weight:600;margin-bottom:3px;">
                <code style="background:rgba(79,142,247,0.12);padding:1px 5px;border-radius:4px;font-size:10px;">{{ $pStat->codigo }}</code>
            </div>
            <div style="font-size:11px;color:var(--text-secondary);margin-bottom:5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $pStat->descripcion }}">
                {{ Str::limit($pStat->descripcion, 28) }}
            </div>
            <div style="font-size:18px;font-weight:800;font-family:monospace;color:{{ $pStat->saldo_actual < 0 ? 'var(--accent-danger)' : 'var(--accent)' }};">
                Bs. {{ number_format($pStat->saldo_actual, 2) }}
            </div>
            <div style="font-size:10px;color:var(--text-secondary);margin-top:2px;">Saldo de esta partida</div>
        </div>
    </div>
    @endforeach

    {{-- Movimientos pendientes --}}
    <div class="card fade-up" style="border-left:3px solid var(--accent-warn);animation-delay:.06s;">
        <div class="card-body" style="padding:14px 18px;">
            <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;font-weight:600;margin-bottom:4px;">Movimientos Pendientes</div>
            <div style="font-size:20px;font-weight:800;color:var(--accent-warn);">{{ $movimientosPendientes }}</div>
        </div>
    </div>

    {{-- Sincronización --}}
    <div class="card fade-up" style="border-left:3px solid {{ $sincronizado ? 'var(--accent-3)' : 'var(--accent-danger)' }};animation-delay:.09s;">
        <div class="card-body" style="padding:14px 18px;">
            <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;font-weight:600;margin-bottom:4px;">Sincronización</div>
            @if($sincronizado)
                <div style="display:flex;align-items:center;gap:6px;margin-top:4px;">
                    <i class="fa-solid fa-circle-check" style="color:var(--accent-3);font-size:18px;"></i>
                    <span style="font-size:13px;font-weight:700;color:var(--accent-3);">Al día</span>
                </div>
                <div style="font-size:10px;color:var(--text-secondary);margin-top:3px;">Cuenta = suma de partidas</div>
            @else
                <div style="display:flex;align-items:center;gap:6px;margin-top:4px;">
                    <i class="fa-solid fa-triangle-exclamation" style="color:var(--accent-danger);font-size:16px;"></i>
                    <span style="font-size:12px;font-weight:700;color:var(--accent-danger);">Bs. {{ number_format(abs($diferencia), 2) }} diferencia</span>
                </div>
                <div style="font-size:10px;color:var(--text-secondary);margin-top:2px;">Corra: <code>cuentas:sincronizar-saldos</code></div>
            @endif
        </div>
    </div>
</div>

{{-- Info + Movimientos --}}
<div style="display:grid;grid-template-columns:300px 1fr;gap:20px;">
    <div class="card fade-up">
        <div class="card-header"><h3 class="card-title">Información</h3></div>
        <div class="card-body">
            <dl style="display:grid;gap:10px;font-size:13px;">
                <div><dt style="color:var(--text-secondary);font-size:11px;font-weight:600;">CÓDIGO</dt><dd><code style="color:var(--accent);">{{ $cuenta->codigo }}</code></dd></div>
                <div><dt style="color:var(--text-secondary);font-size:11px;font-weight:600;">TIPO</dt><dd>{{ ucfirst($cuenta->tipo) }}</dd></div>
                <div><dt style="color:var(--text-secondary);font-size:11px;font-weight:600;">MONEDA</dt><dd>{{ $cuenta->moneda }}</dd></div>
                @if($cuenta->fecha_apertura)
                <div><dt style="color:var(--text-secondary);font-size:11px;font-weight:600;">APERTURA</dt><dd>{{ $cuenta->fecha_apertura->format('d/m/Y') }}</dd></div>
                @endif
                @if($cuenta->firmante_1)
                <div><dt style="color:var(--text-secondary);font-size:11px;font-weight:600;">FIRMANTE 1</dt><dd>{{ $cuenta->firmante_1 }}</dd></div>
                @endif
                @if($cuenta->firmante_2)
                <div><dt style="color:var(--text-secondary);font-size:11px;font-weight:600;">FIRMANTE 2</dt><dd>{{ $cuenta->firmante_2 }}</dd></div>
                @endif
            </dl>
        </div>
    </div>

    <div class="card fade-up" style="animation-delay:.05s;">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
            <h3 class="card-title">Movimientos Presupuestarios</h3>
            <a href="{{ route('presupuesto.movimientos-partidas.index') }}" class="btn btn-outline btn-sm">Ver movimientos de partidas</a>
        </div>
        <div style="padding:24px;text-align:center;color:var(--text-secondary);">
            <i class="fa-solid fa-arrow-right-arrow-left" style="font-size:2rem;opacity:.3;margin-bottom:10px;"></i>
            <div style="font-size:13px;">Los movimientos de esta cuenta se registran automáticamente<br>a través del ciclo presupuestario: <strong>Causación → Pago</strong>.</div>
            <a href="{{ route('presupuesto.pagos.index') }}" class="btn btn-outline btn-sm" style="margin-top:14px;">
                <i class="fa-solid fa-coins"></i> Ver Pagos Registrados
            </a>
        </div>
    </div>
</div>

{{-- Partidas Presupuestarias Vinculadas --}}
<div class="card fade-up" style="margin-top:20px;">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
        <h3 class="card-title">
            <i class="fa-solid fa-layer-group" style="color:var(--accent);margin-right:6px;"></i>
            Partidas Presupuestarias Vinculadas
            <span style="font-size:12px;font-weight:400;color:var(--text-secondary);margin-left:8px;">El saldo de la cuenta se calcula a partir de estas partidas</span>
        </h3>
        <a href="{{ route('presupuesto.partidas.index') }}" class="btn btn-outline btn-sm">Ver catálogo</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr>
                <th>Código</th><th>Descripción</th><th>Genérica</th><th>Específica</th>
                <th style="text-align:right;">Saldo Actual</th><th>Estado</th>
            </tr></thead>
            <tbody>
            @forelse($cuenta->partidasPresupuestarias as $p)
            <tr>
                <td><code style="background:rgba(79,142,247,0.1);color:var(--accent);padding:3px 8px;border-radius:5px;font-size:11px;font-weight:700;">{{ $p->codigo }}</code></td>
                <td style="font-size:13px;max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $p->descripcion }}</td>
                <td style="font-size:12px;color:var(--text-secondary);">{{ $p->generica ?? '—' }}</td>
                <td style="font-size:12px;color:var(--text-secondary);">{{ $p->especifica ?? '—' }}</td>
                <td style="text-align:right;font-family:monospace;font-weight:700;font-size:14px;color:{{ $p->saldo_actual < 0 ? 'var(--accent-danger)' : 'var(--accent-3)' }};">
                    Bs. {{ number_format($p->saldo_actual, 2) }}
                </td>
                <td>
                    <span class="badge {{ $p->activo ? 'badge-active' : 'badge-danger' }}">
                        {{ $p->activo ? 'Activa' : 'Inactiva' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr><td colspan="6">
                <div class="empty-state">
                    <div class="empty-icon">📋</div>
                    <div class="empty-title">Sin partidas vinculadas</div>
                    <div class="empty-desc">Asigna esta cuenta a una o más partidas presupuestarias para que su saldo se calcule automáticamente.</div>
                </div>
            </td></tr>
            @endforelse
            </tbody>
            @if($cuenta->partidasPresupuestarias->count() > 0)
            <tfoot>
                <tr style="background:rgba(79,142,247,0.05);">
                    <td colspan="4" style="text-align:right;font-weight:700;font-size:12px;color:var(--text-secondary);padding:10px 12px;">TOTAL (suma de partidas)</td>
                    <td style="text-align:right;font-family:monospace;font-weight:800;font-size:15px;color:var(--accent-3);padding:10px 12px;">
                        Bs. {{ number_format($saldoDesdePartidas, 2) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
