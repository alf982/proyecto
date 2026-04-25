@extends('layouts.app')
@section('title','Compromiso ' . $compromiso->numero)
@section('breadcrumb')
    <a href="{{ route('presupuesto.compromisos.index') }}" style="color:var(--text-secondary);text-decoration:none;">Compromisos</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">{{ $compromiso->numero }}</span>
@endsection
@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 class="page-title">{{ $compromiso->numero }}</h1>
        <p class="page-subtitle">{{ $compromiso->fecha_compromiso->format('d/m/Y') }} · {{ $compromiso->beneficiario }}</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('presupuesto.compromisos.index') }}" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
        @if($compromiso->esBorrador())
            <a href="{{ route('presupuesto.compromisos.edit', $compromiso) }}" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-pen-to-square"></i> Editar
            </a>
            <button type="button" class="btn btn-sm" style="background:rgba(34,211,166,0.15);border:1px solid rgba(34,211,166,.35);color:var(--accent-3);"
                onclick="accionForm('{{ route('presupuesto.compromisos.aprobar', $compromiso) }}','¿Aprobar este compromiso?')">
                <i class="fa-solid fa-check"></i> Aprobar
            </button>
        @endif
        @if($compromiso->esAprobado())
            @php
                $causacionActiva = $compromiso->causaciones
                    ->whereNotIn('estado', ['anulada'])->first();
            @endphp
            @if($causacionActiva)
                <a href="{{ route('presupuesto.causaciones.show', $causacionActiva) }}"
                   class="btn btn-sm" style="background:rgba(34,211,166,0.12);border:1px solid rgba(34,211,166,.3);color:var(--accent-3);">
                    <i class="fa-solid fa-eye"></i> Ver Causación
                </a>
            @else
                <a href="{{ route('presupuesto.causaciones.create', ['compromiso_id' => $compromiso->id]) }}"
                   class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-file-invoice-dollar"></i> Crear Causación
                </a>
            @endif
        @endif
        @if(!$compromiso->esAnulado())
            <button type="button" class="btn btn-danger btn-sm" onclick="document.getElementById('modal-anular').style.display='flex'">
                <i class="fa-solid fa-ban"></i> Anular
            </button>
        @endif
    </div>
</div>

<div class="fade-up" style="margin-bottom:18px;">
    <span class="badge {{ $compromiso->getEstadoBadgeClass() }}" style="font-size:13px;padding:6px 16px;">{{ ucfirst($compromiso->estado) }}</span>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;" class="fade-up">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fa-solid fa-coins" style="color:var(--accent);margin-right:8px;"></i>Imputación</div></div>
        <div class="card-body" style="display:grid;gap:12px;">
            <div><div class="form-label">Ejercicio</div><strong>{{ $compromiso->ejercicioFiscal?->anio ?? '—' }}</strong></div>
            <div><div class="form-label">Unidad Ejecutora</div><strong>{{ $compromiso->unidadEjecutora?->nombre ?? '—' }}</strong></div>
            <div><div class="form-label">Partida</div><code style="color:var(--accent);">{{ $compromiso->partida?->codigo }}</code> <span style="font-size:12px;color:var(--text-secondary);">{{ $compromiso->partida?->descripcion }}</span></div>
            @if($compromiso->proyecto)<div><div class="form-label">Proyecto</div><span>{{ $compromiso->proyecto->nombre }}</span></div>@endif
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fa-solid fa-building" style="color:var(--accent-2);margin-right:8px;"></i>Beneficiario y Monto</div></div>
        <div class="card-body" style="display:grid;gap:12px;">
            <div><div class="form-label">Beneficiario</div><strong>{{ $compromiso->beneficiario }}</strong>
                @if($compromiso->rif_beneficiario)<div style="font-size:12px;color:var(--text-secondary);">{{ $compromiso->rif_beneficiario }}</div>@endif
            </div>
            <div style="text-align:center;padding:16px;background:rgba(79,142,247,0.06);border-radius:10px;border:1px solid rgba(79,142,247,0.15);">
                <div style="font-size:11px;font-weight:600;color:var(--text-secondary);margin-bottom:4px;text-transform:uppercase;">Monto Comprometido</div>
                <div style="font-size:26px;font-weight:800;color:var(--accent);font-family:monospace;">Bs. {{ number_format((float)$compromiso->monto, 2) }}</div>
            </div>
            @if($compromiso->fecha_vencimiento)<div><div class="form-label">Vence</div><span>{{ $compromiso->fecha_vencimiento->format('d/m/Y') }}</span></div>@endif
        </div>
    </div>
</div>

@if($compromiso->concepto)
<div class="card fade-up" style="margin-top:18px;">
    <div class="card-header"><div class="card-title">Concepto</div></div>
    <div class="card-body"><p style="margin:0;font-size:13px;line-height:1.6;">{{ $compromiso->concepto }}</p></div>
</div>
@endif

{{-- Documento Soporte --}}
@if($compromiso->tipo_documento || $compromiso->numero_documento)
<div class="card fade-up" style="margin-top:18px;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-file-invoice" style="color:var(--accent-3);margin-right:8px;"></i>Documento Soporte
        </div>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;">
            @if($compromiso->tipo_documento)
            <div>
                <div class="form-label">Tipo</div>
                <strong>{{ ucfirst($compromiso->tipo_documento) }}</strong>
            </div>
            @endif
            @if($compromiso->numero_documento)
            <div>
                <div class="form-label">N° Documento</div>
                <code style="color:var(--accent);font-size:14px;">{{ $compromiso->numero_documento }}</code>
            </div>
            @endif
            @if($compromiso->fecha_documento)
            <div>
                <div class="form-label">Fecha del Documento</div>
                <strong>{{ $compromiso->fecha_documento->format('d/m/Y') }}</strong>
            </div>
            @endif
            @if($compromiso->descripcion_documento)
            <div style="grid-column:span 2;">
                <div class="form-label">Descripción</div>
                <span style="font-size:13px;">{{ $compromiso->descripcion_documento }}</span>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ── CAUSACIONES VINCULADAS ────────────────────────────── --}}
@if($compromiso->causaciones->count())
<div class="card fade-up" style="margin-top:18px;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-file-invoice-dollar" style="color:var(--accent-3);margin-right:8px;"></i>
            Causaciones ({{ $compromiso->causaciones->count() }})
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        <table class="table" style="margin:0;">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Fecha</th>
                    <th>Concepto</th>
                    <th>Monto</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($compromiso->causaciones as $cau)
                <tr>
                    <td><code style="color:var(--accent);">{{ $cau->numero }}</code></td>
                    <td>{{ $cau->fecha_causacion->format('d/m/Y') }}</td>
                    <td style="font-size:12px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $cau->concepto }}</td>
                    <td style="font-family:monospace;font-weight:700;">Bs. {{ number_format((float)$cau->monto_causado, 2) }}</td>
                    <td><span class="badge {{ $cau->getEstadoBadgeClass() }}">{{ $cau->getEstadoLabel() }}</span></td>
                    <td><a href="{{ route('presupuesto.causaciones.show', $cau) }}" class="btn btn-outline btn-sm">Ver</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Modal anular -->
<div id="modal-anular" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:999;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:28px;width:min(480px,90vw);">
        <h3 style="font-size:16px;margin-bottom:8px;color:var(--accent-danger);"><i class="fa-solid fa-ban"></i> Anular Compromiso</h3>
        <p style="font-size:13px;color:var(--text-secondary);margin-bottom:20px;">Se liberará el presupuesto comprometido.</p>
        <form method="POST" action="{{ route('presupuesto.compromisos.anular', $compromiso) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Motivo *</label>
                <textarea name="motivo_anulacion" class="form-control" rows="3" required placeholder="Mínimo 10 caracteres..."></textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-anular').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-ban"></i> Anular</button>
            </div>
        </form>
    </div>
</div>
<form id="form-accion" method="POST" action="" style="display:none;">@csrf</form>
@endsection
@push('scripts')
<script>function accionForm(url,msg){if(!confirm(msg))return;document.getElementById('form-accion').action=url;document.getElementById('form-accion').submit();}</script>
@endpush
