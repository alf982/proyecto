@extends('layouts.app')
@section('title','Pagos')
@section('breadcrumb')
    <span>Presupuesto</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Pagos</span>
@endsection
@section('content')

{{-- 
  VISTA INDEX DE PAGOS
  Muestra el listado de pagos ejecutados o en proceso.
  Incluye una "Bandeja de Entrada" en la parte superior para que los usuarios
  puedan registrar rápidamente los pagos de las Causaciones que ya están Aprobadas.
--}}

<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 class="page-title">Registro de Pagos</h1>
        <p class="page-subtitle">Pagos efectuados sobre causaciones presupuestarias aprobadas</p>
    </div>
    @can('pagos.crear')
    <a href="{{ route('presupuesto.pagos.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Nuevo Pago
    </a>
    @endcan
</div>

@if(session('success'))
<div class="alert alert-success fade-up"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger fade-up"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
@endif

{{-- ── CAUSACIONES PENDIENTES DE PAGO (Bandeja de entrada) ─────────────────────────── --}}
@if($causacionesPendientes->count())
<div class="card fade-up" style="margin-bottom:20px;border:1px solid rgba(247,187,67,0.35);">
    <div class="card-header" style="background:rgba(247,187,67,0.07);">
        <div class="card-title" style="color:var(--accent-warn);">
            <i class="fa-solid fa-clock" style="margin-right:8px;"></i>
            Causaciones pendientes de pago ({{ $causacionesPendientes->count() }})
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        <table class="table" style="margin:0;">
            <thead>
                <tr>
                    <th>Causación</th>
                    <th>Fecha</th>
                    <th>Beneficiario</th>
                    <th>Concepto</th>
                    <th style="text-align:right;">Monto</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($causacionesPendientes as $cau)
                <tr>
                    <td><code style="color:var(--accent);font-weight:700;">{{ $cau->numero }}</code></td>
                    <td style="font-size:12px;color:var(--text-secondary);">
                        {{ $cau->fecha_causacion?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td style="font-size:13px;font-weight:600;">{{ $cau->beneficiario }}</td>
                    <td style="font-size:12px;color:var(--text-secondary);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $cau->concepto }}
                    </td>
                    <td style="text-align:right;font-weight:700;font-family:monospace;font-size:14px;color:var(--accent-3);">
                        Bs. {{ number_format((float)$cau->monto_causado, 2) }}
                    </td>
                    <td style="text-align:right;">
                        @can('pagos.crear')
                        <a href="{{ route('presupuesto.pagos.create', ['causacion_id' => $cau->id]) }}"
                           class="btn btn-sm"
                           style="background:rgba(247,187,67,0.15);border:1px solid rgba(247,187,67,.4);color:var(--accent-warn);white-space:nowrap;">
                            <i class="fa-solid fa-money-bill-wave"></i> Registrar Pago
                        </a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Filtros --}}
<div class="card fade-up" style="margin-bottom:18px;animation-delay:.03s;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:1;min-width:200px;">
                <label class="form-label">Buscar</label>
                <input type="text" name="search" class="form-control" placeholder="N° pago, referencia, beneficiario..." value="{{ request('search') }}">
            </div>
            <div style="min-width:130px;">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-control">
                    <option value="">Todos</option>
                    <option value="cheque"        {{ request('tipo')==='cheque'?'selected':'' }}>Cheque</option>
                    <option value="transferencia" {{ request('tipo')==='transferencia'?'selected':'' }}>Transferencia</option>
                    <option value="efectivo"      {{ request('tipo')==='efectivo'?'selected':'' }}>Efectivo</option>
                </select>
            </div>
            <div style="min-width:130px;">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-control" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="pendiente"  {{ request('estado')==='pendiente'?'selected':'' }}>Pendiente</option>
                    <option value="procesado"  {{ request('estado')==='procesado'?'selected':'' }}>Procesado</option>
                    <option value="anulado"    {{ request('estado')==='anulado'?'selected':'' }}>Anulado</option>
                </select>
            </div>
            <div style="display:flex;gap:6px;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Filtrar</button>
                <a href="{{ route('presupuesto.pagos.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
            </div>
        </form>
    </div>
</div>

{{-- Tabla de pagos registrados --}}
<div class="card fade-up" style="animation-delay:.05s;">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-receipt" style="color:var(--accent-3);margin-right:8px;"></i>Pagos Registrados</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr>
                <th>N° Pago</th>
                <th>Causación</th>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Beneficiario</th>
                <th style="text-align:right;">Monto</th>
                <th>Estado</th>
                <th style="text-align:right;">Acciones</th>
            </tr></thead>
            <tbody>
            @forelse($q as $p)
            <tr style="{{ $p->estado === 'anulado' ? 'opacity:.6;' : '' }}">
                <td>
                    <code style="background:rgba(34,211,166,0.1);color:var(--accent-3);padding:3px 8px;border-radius:5px;font-size:12px;font-weight:600;">
                        {{ $p->numero }}
                    </code>
                </td>
                <td>
                    <code style="font-size:11px;color:var(--accent);">
                        {{ $p->causacion?->numero ?? '—' }}
                    </code>
                </td>
                <td style="font-size:12px;color:var(--text-secondary);">
                    {{ \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y') }}
                </td>
                <td style="font-size:12px;">{{ $p->getTipoPagoLabel() }}</td>
                <td style="font-size:13px;font-weight:600;">{{ $p->beneficiario }}</td>
                <td style="text-align:right;font-weight:700;font-family:monospace;color:var(--accent-3);">
                    Bs. {{ number_format($p->monto_pagado,2) }}
                </td>
                <td>
                    <span class="badge {{ $p->getEstadoBadgeClass() }}">{{ ucfirst($p->estado) }}</span>
                </td>
                <td style="text-align:right;">
                    <div style="display:flex;gap:6px;justify-content:flex-end;align-items:center;">
                        @can('pagos.ver')
                        <a href="{{ route('presupuesto.pagos.show', $p) }}" class="btn btn-outline btn-sm">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8">
                <div class="empty-state">
                    <div class="empty-icon">💳</div>
                    <div class="empty-title">No hay pagos registrados</div>
                    <div class="empty-subtitle">Los pagos se registran desde las causaciones aprobadas de arriba</div>
                </div>
            </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Componente estándar de paginación --}}
    <x-pagination :paginator="$q" />
</div>
@endsection
