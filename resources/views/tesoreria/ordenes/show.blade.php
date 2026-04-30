@extends('layouts.app')
@section('title', 'Orden de Pago ' . $orden->numero)
@section('breadcrumb')
    <a href="{{ route('tesoreria.ordenes.index') }}">Órdenes de Pago</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">{{ $orden->numero }}</span>
@endsection
@section('content')
<div class="page-header fade-up">
    <h1 class="page-title">Orden de Pago: <span style="color:var(--accent);">{{ $orden->numero }}</span></h1>
    <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
        <span class="badge {{ $orden->estadoBadge() }}" style="font-size:13px;padding:5px 14px;">{{ ucfirst($orden->estado) }}</span>

        @if($orden->estado === 'borrador')
        <form method="POST" action="{{ route('tesoreria.ordenes.revisar', $orden) }}">@csrf
        <button type="submit" class="btn btn-outline btn-sm">✓ Marcar Revisada</button></form>
        @endif

        @if(in_array($orden->estado, ['borrador','revisada']))
        <form method="POST" action="{{ route('tesoreria.ordenes.aprobar', $orden) }}">@csrf
        <button type="submit" class="btn btn-primary btn-sm">✓ Aprobar</button></form>
        @endif

        @if($orden->estado === 'aprobada')
        <form method="POST" action="{{ route('tesoreria.ordenes.enviar', $orden) }}">@csrf
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-paper-plane"></i> Enviar a Tesorería
        </button></form>
        @endif

        @if($orden->estado === 'enviada' && $orden->pago)
        <a href="{{ route('presupuesto.pagos.show', $orden->pago) }}"
           class="btn btn-primary btn-sm" style="background:linear-gradient(135deg,var(--accent-3),#1aaa88);">
            <i class="fa-solid fa-money-bill-wave"></i> Ver Pago Pendiente
        </a>
        @endif

        @if(!in_array($orden->estado, ['pagada','anulada']))
        <button class="btn btn-danger btn-sm" onclick="document.getElementById('modal-anular').style.display='flex'">
            <i class="fa-solid fa-ban"></i> Anular
        </button>
        @endif

        <a href="{{ route('pdf.orden-pago', $orden) }}" target="_blank" class="btn btn-sm"
           style="background:rgba(229,57,53,0.12);border:1px solid rgba(229,57,53,.3);color:var(--accent-danger);">
            <i class="fa-solid fa-file-pdf"></i> Exportar PDF
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success fade-up"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
<div>

{{-- ── DATOS GENERALES ──────────────────────────────── --}}
<div class="card fade-up">
    <div class="card-header"><div class="card-title">Datos Generales</div></div>
    <div class="card-body">
        <div class="form-row form-row-2">
            <div><div style="font-size:11px;color:var(--text-secondary);">NÚMERO</div>
                 <div style="font-weight:700;font-size:15px;color:var(--accent);">{{ $orden->numero }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">TIPO DE PAGO</div>
                 <div style="font-weight:600;">{{ ucfirst($orden->tipo_pago) }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">MONTO TOTAL</div>
                 <div style="font-weight:800;font-size:18px;color:var(--accent-3);">Bs. {{ number_format($orden->monto_total,2) }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">UNIDAD EJECUTORA</div>
                 <div style="font-weight:600;font-size:13px;">{{ $orden->unidadEjecutora->nombre ?? '—' }}</div></div>
        </div>
        <div style="margin-top:16px;"><div style="font-size:11px;color:var(--text-secondary);">CONCEPTO</div>
             <div style="margin-top:4px;">{{ $orden->concepto }}</div></div>
        @if($orden->beneficiario)
        <div style="margin-top:12px;"><div style="font-size:11px;color:var(--text-secondary);">BENEFICIARIO</div>
             <div style="font-weight:600;">{{ $orden->beneficiario->razon_social }}</div></div>
        @endif
        @if($orden->observaciones)
        <div style="margin-top:12px;"><div style="font-size:11px;color:var(--text-secondary);">OBSERVACIONES</div>
             <div style="font-size:13px;color:var(--text-secondary);">{{ $orden->observaciones }}</div></div>
        @endif
        @if($orden->motivo_anulacion)
        <div class="alert alert-danger" style="margin-top:12px;"><strong>Motivo de anulación:</strong> {{ $orden->motivo_anulacion }}</div>
        @endif
    </div>
</div>

{{-- ── CAUSACIÓN PRESUPUESTARIA ─────────────────────── --}}
@if($orden->causacion)
<div class="card fade-up" style="margin-top:20px;border:1px solid rgba(79,142,247,0.25);animation-delay:.04s;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-file-invoice" style="color:var(--accent);margin-right:6px;"></i>
            Causación Presupuestaria Vinculada
        </div>
        <a href="{{ route('presupuesto.causaciones.show', $orden->causacion) }}" class="btn btn-outline btn-sm">Ver Causación</a>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;font-size:13px;">
            <div>
                <div style="font-size:10px;color:var(--text-secondary);font-weight:600;text-transform:uppercase;">Número</div>
                <div style="font-weight:700;color:var(--accent);font-family:monospace;">{{ $orden->causacion->numero }}</div>
            </div>
            <div>
                <div style="font-size:10px;color:var(--text-secondary);font-weight:600;text-transform:uppercase;">Beneficiario</div>
                <div style="font-weight:600;">{{ $orden->causacion->beneficiario }}</div>
                @if($orden->causacion->rif_beneficiario)
                <div style="font-size:11px;color:var(--text-secondary);">{{ $orden->causacion->rif_beneficiario }}</div>
                @endif
            </div>
            <div>
                <div style="font-size:10px;color:var(--text-secondary);font-weight:600;text-transform:uppercase;">Monto Causado</div>
                <div style="font-weight:800;color:var(--accent-3);">Bs. {{ number_format($orden->causacion->monto_causado,2) }}</div>
            </div>
            @if($orden->causacion->partida)
            <div style="grid-column:span 2;">
                <div style="font-size:10px;color:var(--text-secondary);font-weight:600;text-transform:uppercase;">Partida Presupuestaria</div>
                <div style="font-weight:700;font-family:monospace;color:var(--accent);">{{ $orden->causacion->partida->codigo }}</div>
                <div style="font-size:11px;color:var(--text-secondary);">{{ $orden->causacion->partida->descripcion }}</div>
            </div>
            <div>
                <div style="font-size:10px;color:var(--text-secondary);font-weight:600;text-transform:uppercase;">Saldo Partida</div>
                @php $saldo = $orden->causacion->partida->saldo_actual; @endphp
                <div style="font-weight:800;color:{{ $saldo < 0 ? 'var(--accent-danger)' : 'var(--accent-3)' }};">
                    Bs. {{ number_format($saldo,2) }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ── DETALLE DE LÍNEAS ────────────────────────────── --}}
<div class="card fade-up" style="margin-top:20px;animation-delay:.06s;">
    <div class="card-header"><div class="card-title">Detalle del Pago</div></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>#</th><th>Descripción</th><th style="text-align:right;">Monto</th></tr></thead>
            <tbody>
            @foreach($orden->detalles->sortBy('orden') as $d)
            <tr>
                <td style="color:var(--text-secondary);">{{ $d->orden }}</td>
                <td>{{ $d->descripcion }}</td>
                <td style="text-align:right;font-weight:600;">Bs. {{ number_format($d->monto,2) }}</td>
            </tr>
            @endforeach
            <tr style="border-top:2px solid var(--border);">
                <td colspan="2" style="text-align:right;font-weight:700;color:var(--text-secondary);">TOTAL</td>
                <td style="text-align:right;font-weight:800;color:var(--accent-3);">Bs. {{ number_format($orden->monto_total,2) }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- ── PAGO GENERADO ────────────────────────────────── --}}
@if($orden->pago)
<div class="card fade-up" style="margin-top:20px;border:1px solid rgba(34,211,166,0.3);animation-delay:.08s;">
    <div class="card-header">
        <div class="card-title" style="color:var(--accent-3);">
            <i class="fa-solid fa-circle-check"></i> Pago Presupuestario Generado
        </div>
        <a href="{{ route('presupuesto.pagos.show', $orden->pago) }}" class="btn btn-outline btn-sm">Ver Pago</a>
    </div>
    <div class="card-body" style="font-size:13px;">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
            <div>
                <span style="color:var(--text-secondary);">Número:</span>
                <strong style="color:var(--accent-3);">{{ $orden->pago->numero }}</strong>
            </div>
            <div><span style="color:var(--text-secondary);">Monto:</span> <strong>Bs. {{ number_format($orden->pago->monto_pagado,2) }}</strong></div>
            <div><span style="color:var(--text-secondary);">Fecha:</span> <strong>{{ $orden->pago->fecha_pago->format('d/m/Y') }}</strong></div>
            @if($orden->pago->numero_referencia)
            <div><span style="color:var(--text-secondary);">Referencia:</span> {{ $orden->pago->numero_referencia }}</div>
            @endif
            @if($orden->pago->banco)
            <div><span style="color:var(--text-secondary);">Banco:</span> {{ $orden->pago->banco }}</div>
            @endif
        </div>
    </div>
</div>
@endif

</div>

{{-- ── PANEL LATERAL ────────────────────────────────── --}}
<div>
<div class="card fade-up" style="animation-delay:.1s">
    <div class="card-header"><div class="card-title">Trazabilidad</div></div>
    <div class="card-body" style="font-size:13px;">
        <div style="margin-bottom:12px;">
            <span style="color:var(--text-secondary);">Creado por:</span><br>
            <strong>{{ $orden->creadoPor->name ?? '—' }}</strong>
            <div style="font-size:11px;color:var(--text-secondary);">{{ $orden->created_at->format('d/m/Y H:i') }}</div>
        </div>
        <div style="margin-bottom:12px;">
            <span style="color:var(--text-secondary);">Revisado por:</span><br>
            <strong>{{ $orden->revisadoPor->name ?? 'Pendiente' }}</strong>
            @if($orden->fecha_revision)
            <div style="font-size:11px;color:var(--text-secondary);">{{ $orden->fecha_revision->format('d/m/Y H:i') }}</div>
            @endif
        </div>
        <div style="margin-bottom:12px;">
            <span style="color:var(--text-secondary);">Aprobado por:</span><br>
            <strong>{{ $orden->aprobadoPor->name ?? 'Pendiente' }}</strong>
            @if($orden->fecha_aprobacion)
            <div style="font-size:11px;color:var(--text-secondary);">{{ $orden->fecha_aprobacion->format('d/m/Y H:i') }}</div>
            @endif
        </div>
        <div>
            <span style="color:var(--text-secondary);">Enviado a Tesorería:</span><br>
            <strong>{{ $orden->fecha_envio ? $orden->fecha_envio->format('d/m/Y H:i') : 'Pendiente' }}</strong>
        </div>
        @if($orden->fecha_pago)
        <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);">
            <span style="color:var(--text-secondary);">Fecha de Pago:</span><br>
            <strong style="color:var(--accent-3);">{{ \Carbon\Carbon::parse($orden->fecha_pago)->format('d/m/Y') }}</strong>
        </div>
        @endif
    </div>
</div>
<div style="margin-top:12px;">
    <a href="{{ route('tesoreria.ordenes.index') }}" class="btn btn-outline" style="width:100%;justify-content:center;">← Volver al listado</a>
</div>
</div>
</div>


{{-- ── MODAL ANULAR ─────────────────────────────────── --}}
<div id="modal-anular" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:999;align-items:center;justify-content:center;">
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:28px;width:420px;max-width:95vw;">
        <h3 style="margin-bottom:16px;font-size:16px;">Anular Orden de Pago</h3>
        <form method="POST" action="{{ route('tesoreria.ordenes.anular', $orden) }}">@csrf
            <div class="form-group">
                <label class="form-label">Motivo de anulación *</label>
                <textarea name="motivo_anulacion" class="form-control" rows="3" required minlength="10" placeholder="Describa el motivo de la anulación…"></textarea>
            </div>
            <div style="display:flex;gap:10px;margin-top:16px;">
                <button type="submit" class="btn btn-danger">Confirmar Anulación</button>
                <button type="button" class="btn btn-outline"
                        onclick="document.getElementById('modal-anular').style.display='none'">Cancelar</button>
            </div>
        </form>
    </div>
</div>
@endsection
