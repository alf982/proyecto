@extends('layouts.app')
@section('title', 'Causación ' . $causacion->numero)
@section('breadcrumb')
    <a href="{{ route('presupuesto.causaciones.index') }}" style="color:var(--text-secondary);text-decoration:none;">Causaciones</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">{{ $causacion->numero }}</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 class="page-title">{{ $causacion->numero }}</h1>
        <p class="page-subtitle">{{ $causacion->fecha_causacion?->format('d/m/Y') ?? '—' }} · {{ $causacion->beneficiario }}</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('presupuesto.causaciones.index') }}" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
        <a href="{{ route('pdf.causacion', $causacion) }}" target="_blank" class="btn btn-sm"
           style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);color:#ef4444;">
            <i class="fa-solid fa-file-pdf"></i> Exportar PDF
        </a>
        @if($causacion->esBorrador())
        @can('causaciones.crear')
        <a href="{{ route('presupuesto.causaciones.edit', $causacion) }}" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-pen-to-square"></i> Editar
        </a>
        @endcan
        @can('causaciones.aprobar')
        <form method="POST" action="{{ route('presupuesto.causaciones.aprobar', $causacion) }}" style="margin:0">
            @csrf
            <button type="submit" class="btn btn-sm" style="background:rgba(34,211,166,0.15);border:1px solid rgba(34,211,166,0.3);color:var(--accent-3);"
                onclick="return confirm('¿Aprobar esta causación?')">
                <i class="fa-solid fa-circle-check"></i> Aprobar
            </button>
        </form>
        @endcan
        @endif
        @if($causacion->esAprobada())
        @can('pagos.crear')
        <a href="{{ route('presupuesto.pagos.create', ['causacion_id' => $causacion->id]) }}"
           class="btn btn-sm" style="background:rgba(34,211,166,0.15);border:1px solid rgba(34,211,166,0.35);color:var(--accent-3);">
            <i class="fa-solid fa-money-bill-wave"></i> Registrar Pago
        </a>
        @endcan
        @endif
        @can('causaciones.anular')
        @if(!$causacion->esPagada() && !$causacion->esAnulada())
        <button type="button" class="btn btn-danger btn-sm" onclick="document.getElementById('modal-anular').style.display='flex'">
            <i class="fa-solid fa-ban"></i> Anular
        </button>
        @endif
        @endcan
    </div>
</div>

<!-- Badge de estado grande -->
<div class="fade-up" style="margin-bottom:20px;animation-delay:.03s;">
    <span class="badge {{ $causacion->getEstadoBadgeClass() }}" style="font-size:13px;padding:6px 16px;">
        {{ $causacion->getEstadoLabel() }}
    </span>
    @if($causacion->esAnulada())
        <span style="font-size:12px;color:var(--accent-danger);margin-left:10px;">{{ $causacion->motivo_anulacion }}</span>
    @endif
</div>

<!-- Info general -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" class="fade-up" style="animation-delay:.05s;">

    <!-- Imputación -->
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fa-solid fa-coins" style="color:var(--accent);margin-right:8px;"></i>Imputación Presupuestaria</div></div>
        <div class="card-body" style="display:grid;gap:12px;">
            <div>
                <div class="form-label">Ejercicio Fiscal</div>
                <div style="font-weight:600;">{{ $causacion->ejercicioFiscal?->anio ?? '—' }}</div>
            </div>
            <div>
                <div class="form-label">Unidad Ejecutora</div>
                <div style="font-weight:600;">{{ $causacion->unidadEjecutora?->nombre ?? '—' }}</div>
            </div>
            <div>
                <div class="form-label">Partida Presupuestaria</div>
                <code style="background:rgba(79,142,247,0.1);color:var(--accent);padding:3px 8px;border-radius:5px;">
                    {{ $causacion->partida?->codigo ?? 'Sin código' }}
                </code>
                <span style="font-size:12px;color:var(--text-secondary);margin-left:8px;">{{ $causacion->partida?->descripcion ?? '' }}</span>
            </div>
            @if($causacion->proyecto)
            <div>
                <div class="form-label">Proyecto</div>
                <div style="font-size:13px;">{{ $causacion->proyecto->nombre }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Beneficiario y documento -->
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fa-solid fa-file-invoice" style="color:var(--accent-3);margin-right:8px;"></i>Documento Soporte</div></div>
        <div class="card-body" style="display:grid;gap:12px;">
            <div>
                <div class="form-label">Beneficiario</div>
                <div style="font-weight:600;">{{ $causacion->beneficiario }}</div>
                @if($causacion->rif_beneficiario)
                    <div style="font-size:12px;color:var(--text-secondary);">{{ $causacion->rif_beneficiario }}</div>
                @endif
            </div>
            <div>
                <div class="form-label">Tipo de Documento</div>
                <div style="font-weight:600;text-transform:capitalize;">{{ $causacion->tipo_documento }}</div>
            </div>
            @if($causacion->numero_documento)
            <div>
                <div class="form-label">N° Documento</div>
                <div style="font-weight:600;font-family:monospace;">{{ $causacion->numero_documento }}</div>
            </div>
            @endif
            @if($causacion->fecha_documento)
            <div>
                <div class="form-label">Fecha Documento</div>
                <div>{{ $causacion->fecha_documento->format('d/m/Y') }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Montos -->
<div class="card fade-up" style="margin-top:20px;animation-delay:.08s;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-calculator" style="color:var(--accent-warn);margin-right:8px;"></i>Montos</div></div>
    <div class="card-body">
        <div class="form-row" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;">
            <div style="text-align:center;padding:18px;background:rgba(79,142,247,0.06);border-radius:10px;border:1px solid rgba(79,142,247,0.15);">
                <div style="font-size:11px;font-weight:600;color:var(--text-secondary);margin-bottom:6px;text-transform:uppercase;">Monto Causado</div>
                <div style="font-size:22px;font-weight:800;color:var(--accent);font-family:monospace;">{{ number_format($causacion->monto_causado, 2) }}</div>
            </div>
            @if($causacion->monto_retencion > 0)
            <div style="text-align:center;padding:18px;background:rgba(247,95,95,0.06);border-radius:10px;border:1px solid rgba(247,95,95,0.15);">
                <div style="font-size:11px;font-weight:600;color:var(--text-secondary);margin-bottom:6px;text-transform:uppercase;">Retenciones</div>
                <div style="font-size:22px;font-weight:800;color:var(--accent-danger);font-family:monospace;">{{ number_format($causacion->monto_retencion, 2) }}</div>
            </div>
            <div style="text-align:center;padding:18px;background:rgba(34,211,166,0.06);border-radius:10px;border:1px solid rgba(34,211,166,0.15);">
                <div style="font-size:11px;font-weight:600;color:var(--text-secondary);margin-bottom:6px;text-transform:uppercase;">Neto a Pagar</div>
                <div style="font-size:22px;font-weight:800;color:var(--accent-3);font-family:monospace;">{{ number_format($causacion->monto_neto, 2) }}</div>
            </div>
            @endif
        </div>
        @if($causacion->concepto)
        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
            <div class="form-label">Concepto</div>
            <div style="font-size:13px;line-height:1.6;">{{ $causacion->concepto }}</div>
        </div>
        @endif
        @if($causacion->observaciones)
        <div style="margin-top:12px;">
            <div class="form-label">Observaciones</div>
            <div style="font-size:13px;color:var(--text-secondary);">{{ $causacion->observaciones }}</div>
        </div>
        @endif
    </div>
</div>

<!-- Historial de estados -->
<div class="card fade-up" style="margin-top:20px;animation-delay:.10s;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-timeline" style="color:var(--accent-2);margin-right:8px;"></i>Trazabilidad</div></div>
    <div class="card-body">
        <div style="display:flex;flex-direction:column;gap:12px;">
            <div style="display:flex;gap:12px;align-items:center;">
                <div style="width:28px;height:28px;border-radius:50%;background:rgba(79,142,247,0.2);display:grid;place-items:center;"><i class="fa-solid fa-plus" style="font-size:11px;color:var(--accent);"></i></div>
                <div>
                    <div style="font-size:13px;font-weight:600;">Registrada</div>
                    <div style="font-size:11px;color:var(--text-secondary);">{{ $causacion->created_at->format('d/m/Y H:i') }} · {{ $causacion->creadoPor?->name ?? '—' }}</div>
                </div>
            </div>
            @if($causacion->fecha_aprobacion)
            <div style="display:flex;gap:12px;align-items:center;">
                <div style="width:28px;height:28px;border-radius:50%;background:rgba(34,211,166,0.2);display:grid;place-items:center;"><i class="fa-solid fa-check" style="font-size:11px;color:var(--accent-3);"></i></div>
                <div>
                    <div style="font-size:13px;font-weight:600;">Aprobada</div>
                    <div style="font-size:11px;color:var(--text-secondary);">{{ $causacion->fecha_aprobacion->format('d/m/Y') }} · {{ $causacion->aprobadoPor?->name ?? '—' }}</div>
                </div>
            </div>
            @endif
            @if($causacion->fecha_pago)
            <div style="display:flex;gap:12px;align-items:center;">
                <div style="width:28px;height:28px;border-radius:50%;background:rgba(79,142,247,0.2);display:grid;place-items:center;"><i class="fa-solid fa-money-bill-wave" style="font-size:11px;color:var(--accent);"></i></div>
                <div>
                    <div style="font-size:13px;font-weight:600;">Pagada</div>
                    <div style="font-size:11px;color:var(--text-secondary);">{{ $causacion->fecha_pago->format('d/m/Y') }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal anular — solo se renderiza si el usuario tiene permiso --}}
@can('causaciones.anular')
<div id="modal-anular" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:999;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:28px;width:min(480px,90vw);">
        <h3 style="font-size:16px;margin-bottom:8px;color:var(--accent-danger);"><i class="fa-solid fa-ban"></i> Anular Causación</h3>
        <p style="font-size:13px;color:var(--text-secondary);margin-bottom:20px;">Esta acción liberará el presupuesto comprometido y no podrá revertirse.</p>
        <form method="POST" action="{{ route('presupuesto.causaciones.anular', $causacion) }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="motivo_anulacion">Motivo de anulación *</label>
                <textarea id="motivo_anulacion" name="motivo_anulacion" class="form-control" rows="3" required
                    placeholder="Describa el motivo (mínimo 10 caracteres)..."></textarea>
                @error('motivo_anulacion')<div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-anular').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-ban"></i> Confirmar Anulación</button>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection
