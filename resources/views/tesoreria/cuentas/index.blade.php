@extends('layouts.app')
@section('title', 'Cuentas Bancarias')
@section('breadcrumb')
    <span>Tesorería</span><i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Cuentas Bancarias</span>
@endsection
@section('content')

{{-- 
  VISTA INDEX DE CUENTAS BANCARIAS
  Muestra el catálogo de cuentas de la institución.
  Visualiza rápidamente el saldo activo agrupado, cantidad de cuentas y 
  la vinculación entre estas cuentas y las partidas presupuestarias.
--}}

    <div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
        <div>
            <h1 class="page-title">Cuentas Bancarias</h1>
            <p class="page-subtitle">Administración de cuentas institucionales</p>
        </div>
        @can('tesoreria.cuentas.crear')
        <a href="{{ route('tesoreria.cuentas.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nueva
            Cuenta</a>
        @endcan
    </div>

    {{-- Resumen --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:24px;">
        <div class="card fade-up" style="border-left:3px solid var(--accent-3);">
            <div class="card-body" style="padding:16px 20px;">
                <div
                    style="font-size:11px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;margin-bottom:6px;">
                    Total Saldo Activo</div>
                <div style="font-size:22px;font-weight:800;font-family:monospace;color:var(--accent-3);">Bs.
                    {{ number_format($totalSaldo, 2) }}
                </div>
                <div style="font-size:10px;color:var(--text-secondary);margin-top:3px;">Calculado desde partidas
                    presupuestarias</div>
            </div>
        </div>
        <div class="card fade-up" style="border-left:3px solid var(--accent);animation-delay:.05s;">
            <div class="card-body" style="padding:16px 20px;">
                <div
                    style="font-size:11px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;margin-bottom:6px;">
                    Cuentas Activas</div>
                <div style="font-size:22px;font-weight:800;color:var(--accent);">
                    {{ $cuentas->where('estado', 'activa')->count() }}
                </div>
            </div>
        </div>
        <div class="card fade-up" style="border-left:3px solid var(--accent-warn);animation-delay:.10s;">
            <div class="card-body" style="padding:16px 20px;">
                <div
                    style="font-size:11px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;margin-bottom:6px;">
                    Partidas Vinculadas</div>
                <div style="font-size:22px;font-weight:800;color:var(--accent-warn);">{{ $totalPartidas }}</div>
                <div style="font-size:10px;color:var(--text-secondary);margin-top:3px;">Alimentan los saldos de cuentas
                </div>
            </div>
        </div>
    </div>

    @include('components.alert')

    <div class="card fade-up">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre / Banco</th>
                        <th>N° Cuenta</th>
                        <th>Tipo</th>
                        <th style="text-align:center;">Partidas</th>
                        <th style="text-align:right;">Saldo Actual</th>
                        <th>Estado</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cuentas as $c)
                        <tr>
                            <td><code
                                    style="background:rgba(79,142,247,0.1);color:var(--accent);padding:3px 7px;border-radius:5px;font-size:12px;font-weight:600;">{{ $c->codigo }}</code>
                            </td>
                            <td>
                                <div style="font-weight:600;font-size:13px;">{{ $c->nombre }}</div>
                                <div style="font-size:11px;color:var(--text-secondary);">{{ $c->banco }}</div>
                            </td>
                            <td><code style="font-size:12px;">{{ $c->numero_cuenta }}</code></td>
                            <td style="font-size:12px;">{{ ucfirst($c->tipo) }} · {{ $c->moneda }}</td>
                            <td style="text-align:center;">
                                @if($c->partidas_presupuestarias_count > 0)
                                    <span class="badge badge-active" style="font-size:10px;"
                                        title="{{ $c->partidas_presupuestarias_count }} partida(s) vinculada(s)">
                                        {{ $c->partidas_presupuestarias_count }} partida(s)
                                    </span>
                                @else
                                    <span style="font-size:11px;color:var(--text-secondary);">—</span>
                                @endif
                            </td>
                            <td style="text-align:right;font-weight:700;font-family:monospace;">
                                <div
                                    style="color:{{ $c->saldo_actual < 0 ? 'var(--accent-danger)' : 'var(--accent-3)' }};font-size:14px;">
                                    {{ number_format($c->saldo_actual, 2) }}
                                </div>
                                @if($c->partidas_presupuestarias_count > 0)
                                    <div style="font-size:9px;color:var(--text-secondary);font-weight:400;margin-top:1px;">desde
                                        partidas</div>
                                @endif
                            </td>
                            <td><span class="badge {{ $c->getEstadoBadgeClass() }}">{{ ucfirst($c->estado) }}</span></td>
                            <td style="text-align:right;">
                                <div style="display:flex;gap:6px;justify-content:flex-end;">
                                    @can('tesoreria.cuentas.ver')
                                    <a href="{{ route('tesoreria.cuentas.show', $c) }}" class="btn btn-outline btn-sm"><i
                                            class="fa-solid fa-eye"></i></a>
                                    @endcan
                                    @can('tesoreria.cuentas.editar')
                                    <a href="{{ route('tesoreria.cuentas.edit', $c) }}" class="btn btn-outline btn-sm"><i
                                            class="fa-solid fa-pen-to-square"></i></a>
                                    <form method="POST" action="{{ route('tesoreria.cuentas.destroy', $c) }}" style="margin:0;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm"
                                            style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,.3);color:var(--accent-danger);"
                                            onclick="return confirm('¿Eliminar cuenta {{ $c->nombre }}?')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-icon">🏦</div>
                                    <div class="empty-title">Sin cuentas bancarias registradas</div>
                                    <div class="empty-desc">Registra la primera cuenta institucional.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Componente estándar de paginación --}}
        <x-pagination :paginator="$cuentas" />
    </div>
@endsection