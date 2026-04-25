@extends('layouts.app')
@section('title', 'Créditos Presupuestarios')
@section('breadcrumb')
    <span>Presupuesto</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Créditos Presupuestarios</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Créditos Presupuestarios</h1>
        <p class="page-subtitle">Asignación de montos por partida y unidad ejecutora</p>
    </div>
    <a href="{{ route('presupuesto.creditos.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nuevo Crédito</a>
</div>

<!-- Filtro por ejercicio -->
<div class="card fade-up" style="margin-bottom:20px;">
    <div class="card-body" style="padding:16px 22px;">
        <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <div style="min-width:200px;">
                <label class="form-label">Ejercicio Fiscal</label>
                <select name="ejercicio_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    @foreach($ejercicios as $ej)
                        <option value="{{ $ej->id }}" {{ request('ejercicio_id')==$ej->id || ($ejercicioSeleccionado && $ejercicioSeleccionado->id==$ej->id) ? 'selected' : '' }}>
                            {{ $ej->anio }} ({{ ucfirst($ej->estado) }})
                        </option>
                    @endforeach
                </select>
            </div>
            @if($ejercicioSeleccionado)
            <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;background:rgba(34,211,166,0.08);border:1px solid rgba(34,211,166,0.2);border-radius:9px;">
                <i class="fa-solid fa-calendar-check" style="color:var(--accent-3);"></i>
                <div style="font-size:13px;">
                    <div style="font-weight:600;">Ejercicio {{ $ejercicioSeleccionado->anio }}</div>
                    <div style="font-size:11px;color:var(--text-secondary);">
                        Total aprobado: <strong style="color:var(--text-primary);">{{ number_format($creditos->sum('monto_aprobado'), 2) }} Bs.</strong>
                    </div>
                </div>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="card fade-up" style="animation-delay:.05s">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Partida</th>
                    <th>Unidad Ejecutora</th>
                    <th>Fuente</th>
                    <th style="text-align:right;">Aprobado</th>
                    <th style="text-align:right;">Saldo Partida</th>
                    <th style="text-align:right;">Comprometido</th>
                    <th style="text-align:right;">Disponible</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($creditos as $credito)
                <tr>
                    <td>
                        <code style="background:rgba(79,142,247,0.1);color:var(--accent);padding:3px 7px;border-radius:5px;font-size:12px;font-weight:600;">{{ $credito->partida?->codigo ?? '—' }}</code>
                        <div style="font-size:11px;color:var(--text-secondary);margin-top:3px;max-width:200px;">{{ Str::limit($credito->partida?->descripcion ?? '', 40) }}</div>
                    </td>
                    <td style="font-size:12px;">{{ Str::limit($credito->unidadEjecutora?->nombre ?? '—', 30) }}</td>
                    <td style="font-size:12px;color:var(--text-secondary);">{{ $credito->fuenteFinanciamiento?->nombre ?? '—' }}</td>
                    <td style="text-align:right;font-weight:600;font-size:13px;">{{ number_format($credito->monto_aprobado, 2) }}</td>
                    @php
                        $saldoP    = (float)($credito->partida?->saldo_actual ?? 0);
                        $vigente   = $credito->monto_vigente;
                        $disponible = $vigente - $credito->monto_comprometido;
                    @endphp
                    <td style="text-align:right;font-size:13px;font-weight:700;">
                        @if($saldoP > 0)
                            <span style="color:var(--accent-3);font-family:monospace;">{{ number_format($saldoP, 2) }}</span>
                            <div style="font-size:9px;color:var(--text-secondary);margin-top:1px;">↑ saldo real partida</div>
                        @else
                            <span style="color:var(--text-secondary);font-size:11px;">Sin movimientos</span>
                        @endif
                    </td>
                    <td style="text-align:right;font-size:13px;color:var(--accent-warn);">{{ number_format($credito->monto_comprometido, 2) }}</td>
                    <td style="text-align:right;font-size:13px;">
                        <span style="color:{{ $disponible < 0 ? 'var(--accent-danger)' : 'var(--accent-3)' }};font-weight:700;">
                            {{ number_format($disponible, 2) }}
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;align-items:center;">
                            <a href="{{ route('presupuesto.creditos.show', $credito) }}" class="btn btn-outline btn-sm">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ route('presupuesto.creditos.edit', $credito) }}" class="btn btn-outline btn-sm">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <form method="POST" action="{{ route('presupuesto.creditos.destroy', $credito) }}" style="margin:0;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8">
                    <div class="empty-state">
                        <div class="empty-icon">💰</div>
                        <div class="empty-title">No hay créditos registrados</div>
                        <div class="empty-desc">Selecciona un ejercicio fiscal y comienza a cargar los créditos presupuestarios.</div>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
