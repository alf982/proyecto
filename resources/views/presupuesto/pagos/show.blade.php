@extends('layouts.app')
@section('title','Pago ' . $pago->numero)
@section('breadcrumb')
    <a href="{{ route('presupuesto.pagos.index') }}" style="color:var(--text-secondary);text-decoration:none;">Pagos</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">{{ $pago->numero }}</span>
@endsection
@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 class="page-title">{{ $pago->numero }}</h1>
        <p class="page-subtitle">{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }} · {{ $pago->beneficiario }}</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <a href="{{ route('presupuesto.pagos.index') }}" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
        @if($pago->esPendiente())
        <button type="button" class="btn btn-primary btn-sm"
            onclick="document.getElementById('modal-procesar').style.display='flex'">
            <i class="fa-solid fa-money-bill-wave"></i> Procesar Pago
        </button>
        @endif
        @if(!$pago->esAnulado())
        <button type="button" class="btn btn-danger btn-sm"
            onclick="document.getElementById('modal-anular').style.display='flex'">
            <i class="fa-solid fa-ban"></i> Anular
        </button>
        @endif
    </div>
</div>

@if(session('success'))
<div class="alert alert-success fade-up"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger fade-up"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
@endif

<div class="fade-up" style="margin-bottom:18px;">
    <span class="badge {{ $pago->getEstadoBadgeClass() }}" style="font-size:13px;padding:6px 16px;">{{ ucfirst($pago->estado) }}</span>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
<div>

{{-- Causación origen --}}
<div class="card fade-up">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-file-invoice" style="color:var(--accent);margin-right:6px;"></i>Causación Origen
        </div>
        <a href="{{ route('presupuesto.causaciones.show', $pago->causacion) }}" class="btn btn-outline btn-sm">Ver Causación</a>
    </div>
    <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;font-size:13px;">
        <div>
            <div class="form-label">N° Causación</div>
            <div style="font-weight:700;color:var(--accent);font-family:monospace;">{{ $pago->causacion->numero }}</div>
        </div>
        <div>
            <div class="form-label">Beneficiario</div>
            <div style="font-weight:600;">{{ $pago->causacion->beneficiario }}</div>
            @if($pago->causacion->rif_beneficiario)
            <div style="font-size:11px;color:var(--text-secondary);">{{ $pago->causacion->rif_beneficiario }}</div>
            @endif
        </div>
        @if($pago->causacion->partida)
        <div>
            <div class="form-label">Partida Presupuestaria</div>
            <code style="background:rgba(79,142,247,0.1);color:var(--accent);padding:2px 8px;border-radius:5px;">
                {{ $pago->causacion->partida->codigo }}
            </code>
            <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">{{ $pago->causacion->partida->descripcion }}</div>
        </div>
        <div>
            <div class="form-label">Saldo Partida (actual)</div>
            @php $saldo = $pago->causacion->partida->saldo_actual; @endphp
            <div style="font-weight:800;font-size:16px;color:{{ $saldo < 0 ? 'var(--accent-danger)' : 'var(--accent-3)' }};">
                Bs. {{ number_format($saldo, 2) }}
            </div>
        </div>
        @endif
        <div style="grid-column:span 2;">
            <div class="form-label">Concepto</div>
            <div style="font-size:13px;color:var(--text-secondary);word-break:break-word;overflow-wrap:anywhere;white-space:pre-wrap;">{{ $pago->causacion->concepto }}</div>
        </div>
    </div>
</div>

{{-- Detalle del Pago --}}
<div class="card fade-up" style="margin-top:18px;animation-delay:.05s;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-money-bill-wave" style="color:var(--accent-3);margin-right:6px;"></i>Detalle del Pago
        </div>
    </div>
    <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div>
            <div class="form-label">Tipo de Pago</div>
            <div style="font-weight:600;">{{ $pago->getTipoPagoLabel() }}</div>
        </div>
        <div>
            <div class="form-label">Fecha de Pago</div>
            <div style="font-weight:600;">{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</div>
        </div>
        @if($pago->numero_referencia)
        <div>
            <div class="form-label">N° Referencia</div>
            <code style="font-family:monospace;font-weight:700;color:var(--accent);">{{ $pago->numero_referencia }}</code>
        </div>
        @endif
        @if($pago->banco)
        <div>
            <div class="form-label">Banco</div>
            <div>{{ $pago->banco }}</div>
        </div>
        @endif
        @if($pago->cuenta_bancaria)
        <div>
            <div class="form-label">Cuenta Bancaria</div>
            <div style="font-family:monospace;">{{ $pago->cuenta_bancaria }}</div>
        </div>
        @endif
        <div style="grid-column:span 2;text-align:center;padding:20px;background:rgba(34,211,166,0.06);border-radius:12px;border:1px solid rgba(34,211,166,0.15);">
            <div style="font-size:11px;font-weight:600;color:var(--text-secondary);margin-bottom:4px;text-transform:uppercase;">Monto Pagado</div>
            <div style="font-size:30px;font-weight:800;color:var(--accent-3);font-family:monospace;">Bs. {{ number_format($pago->monto_pagado,2) }}</div>
        </div>
        @if($pago->concepto)
        <div style="grid-column:span 2;">
            <div class="form-label">Concepto</div>
            <div style="font-size:13px;word-break:break-word;overflow-wrap:anywhere;white-space:pre-wrap;">{{ $pago->concepto }}</div>
        </div>
        @endif
        @if($pago->observaciones)
        <div style="grid-column:span 2;">
            <div class="form-label">Observaciones</div>
            <div style="font-size:13px;color:var(--text-secondary);word-break:break-word;overflow-wrap:anywhere;white-space:pre-wrap;">{{ $pago->observaciones }}</div>
        </div>
        @endif
    </div>
</div>

</div>

{{-- Panel lateral --}}
<div>
<div class="card fade-up" style="animation-delay:.08s;">
    <div class="card-header"><div class="card-title">Trazabilidad</div></div>
    <div class="card-body" style="font-size:13px;display:grid;gap:10px;">
        <div>
            <span style="color:var(--text-secondary);">Registrado por:</span><br>
            <strong>{{ $pago->creadoPor->name ?? '—' }}</strong>
        </div>
        <div>
            <span style="color:var(--text-secondary);">Creado el:</span><br>
            <strong>{{ $pago->created_at->format('d/m/Y H:i') }}</strong>
        </div>
        @if($pago->esAnulado() && $pago->motivo_anulacion)
        <div style="padding:10px;background:rgba(247,95,95,0.08);border-radius:8px;border:1px solid rgba(247,95,95,0.2);">
            <div style="font-size:10px;font-weight:700;color:var(--accent-danger);text-transform:uppercase;margin-bottom:4px;">Motivo de Anulación</div>
            <div style="font-size:12px;word-break:break-word;overflow-wrap:anywhere;white-space:pre-wrap;">{{ $pago->motivo_anulacion }}</div>
        </div>
        @endif
    </div>
</div>

@if($pago->esPendiente())
<div class="card fade-up" style="margin-top:14px;animation-delay:.1s;border:1px solid rgba(247,187,67,0.3);">
    <div class="card-body" style="padding:14px 16px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
            <i class="fa-solid fa-clock" style="color:var(--accent-warn);"></i>
            <span style="font-size:12px;font-weight:700;color:var(--accent-warn);">EN ESPERA</span>
        </div>
        <div style="font-size:12px;color:var(--text-secondary);">
            Este pago está pendiente de procesamiento. Use el botón "Procesar Pago" para ejecutarlo.
        </div>
    </div>
</div>
@endif

@if($pago->esProcesado())
<div class="card fade-up" style="margin-top:14px;animation-delay:.1s;border:1px solid rgba(34,211,166,0.3);">
    <div class="card-body" style="padding:14px 16px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
            <i class="fa-solid fa-circle-check" style="color:var(--accent-3);"></i>
            <span style="font-size:12px;font-weight:700;color:var(--accent-3);">PAGO EJECUTADO</span>
        </div>
        <div style="font-size:12px;color:var(--text-secondary);">
            El pago fue ejecutado y la causación está marcada como pagada.
            El saldo de la partida fue reservado desde el momento del compromiso.
        </div>
    </div>
</div>
@endif
</div>
</div>

{{-- Modal procesar --}}
@if($pago->esPendiente())
<div id="modal-procesar" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.65);z-index:999;align-items:center;justify-content:center;">
    <div style="background:var(--bg-card);border:1px solid rgba(34,211,166,0.3);border-radius:14px;padding:28px;width:480px;max-width:95vw;">
        <h3 style="margin-bottom:6px;font-size:16px;">
            <i class="fa-solid fa-money-bill-wave" style="color:var(--accent-3);"></i> Procesar Pago
        </h3>
        <p style="font-size:12px;color:var(--text-secondary);margin-bottom:20px;">
            Ingrese los datos bancarios y confirme la ejecución del pago.<br>
            El monto <strong style="color:var(--accent-3);">Bs. {{ number_format($pago->monto_pagado,2) }}</strong>
            será registrado como pagado en la causación
            <strong>{{ $pago->causacion?->numero ?? '&mdash;' }}</strong>.
        </p>
        <form method="POST" action="{{ route('presupuesto.pagos.procesar', $pago) }}">@csrf
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Fecha de Pago *</label>
                    <input type="date" name="fecha_pago" class="form-control"
                           value="{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">N° Referencia</label>
                    <input type="text" name="numero_referencia" class="form-control"
                           value="{{ $pago->numero_referencia }}" placeholder="Número de transacción">
                </div>
                <div class="form-group">
                    <label class="form-label">Banco</label>
                    <input type="text" name="banco" class="form-control"
                           value="{{ $pago->banco }}" placeholder="Ej: Banco de Venezuela">
                </div>
                <div class="form-group">
                    <label class="form-label">N° Cuenta</label>
                    <input type="text" name="cuenta_bancaria" class="form-control"
                           value="{{ $pago->cuenta_bancaria }}" placeholder="Ej: 0102-...">
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:8px;">
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg,var(--accent-3),#1aaa88);">
                    <i class="fa-solid fa-check-double"></i> Confirmar Pago
                </button>
                <button type="button" class="btn btn-outline"
                        onclick="document.getElementById('modal-procesar').style.display='none'">Cancelar</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Modal anular --}}
<div id="modal-anular" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:999;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:28px;width:min(480px,90vw);">
        <h3 style="font-size:16px;margin-bottom:8px;color:var(--accent-danger);">
            <i class="fa-solid fa-ban"></i> Anular Pago
        </h3>
        @if($pago->esProcesado())
        <p style="font-size:12px;color:var(--accent-warn);margin-bottom:16px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            La causación volverá a estado <strong>Aprobada</strong> y podrá volver a pagarse.
            El saldo presupuestario no se modifica (fue reservado al comprometer).
        </p>
        @else
        <p style="font-size:13px;color:var(--text-secondary);margin-bottom:16px;">La causación volverá a estado Aprobada.</p>
        @endif
        <form method="POST" action="{{ route('presupuesto.pagos.anular', $pago) }}">@csrf
            <div class="form-group">
                <label class="form-label">Motivo *</label>
                <textarea name="motivo_anulacion" class="form-control" rows="3" required placeholder="Mínimo 10 caracteres..."></textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" class="btn btn-outline"
                        onclick="document.getElementById('modal-anular').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-ban"></i> Anular</button>
            </div>
        </form>
    </div>
</div>
@endsection
