@extends('pdf.layout')

@section('watermark'){{ strtoupper($orden->estado) }}@endsection
@section('doc-tipo')Orden de Pago@endsection
@section('doc-numero'){{ $orden->numero }}@endsection

@section('content')

<table class="meta-grid">
  <tr>
    <td class="meta-label">Estado</td>
    <td><span class="estado-badge estado-{{ $orden->estado }}">{{ ucfirst($orden->estado) }}</span></td>
    <td class="meta-label">Tipo de Pago</td>
    <td>{{ ucfirst($orden->tipo_pago) }}</td>
  </tr>
  <tr>
    <td class="meta-label">Beneficiario</td>
    <td colspan="3">
      {{ $orden->beneficiario?->razon_social ?? '—' }}
      @if($orden->beneficiario?->rif) — {{ $orden->beneficiario->rif }} @endif
    </td>
  </tr>
  <tr>
    <td class="meta-label">Unidad Ejecutora</td>
    <td colspan="3">{{ $orden->unidadEjecutora->nombre ?? '—' }}</td>
  </tr>
  <tr>
    <td class="meta-label">Concepto General</td>
    <td colspan="3">{{ $orden->concepto }}</td>
  </tr>
  @if($orden->causacion)
  <tr>
    <td class="meta-label">Causación Asociada</td>
    <td colspan="3">{{ $orden->causacion->numero }} — {{ $orden->causacion->beneficiario }}</td>
  </tr>
  @endif
  @if($orden->observaciones)
  <tr>
    <td class="meta-label">Observaciones</td>
    <td colspan="3">{{ $orden->observaciones }}</td>
  </tr>
  @endif
</table>

<div class="section-title">Detalle de la Orden</div>
<table class="detail-table">
  <thead>
    <tr>
      <th style="width:30px">#</th>
      <th>Descripción</th>
      <th class="text-right" style="width:140px">Monto (Bs.)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($orden->detalles as $i => $detalle)
    <tr>
      <td class="text-center">{{ $i + 1 }}</td>
      <td>{{ $detalle->descripcion }}</td>
      <td class="text-right">{{ number_format($detalle->monto, 2) }}</td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="totales clearfix">
  <div class="totales-inner">
    <div class="totales-row totales-total">
      <span class="totales-label">TOTAL A PAGAR</span>
      <span class="totales-val">Bs. {{ number_format($orden->monto_total, 2) }}</span>
    </div>
  </div>
</div>

<div class="section-title" style="margin-top: 50px">Trazabilidad</div>
<table class="meta-grid">
  <tr>
    <td class="meta-label">Elaborado por</td>
    <td>{{ $orden->creadoPor->name ?? '—' }}</td>
    <td class="meta-label">Fecha</td>
    <td>{{ $orden->created_at?->translatedFormat('d/m/Y') }}</td>
  </tr>
  @if($orden->revisadoPor)
  <tr>
    <td class="meta-label">Revisado por</td>
    <td>{{ $orden->revisadoPor->name }}</td>
    <td class="meta-label">Fecha revisión</td>
    <td>{{ $orden->fecha_revision?->translatedFormat('d/m/Y') }}</td>
  </tr>
  @endif
  @if($orden->aprobadoPor)
  <tr>
    <td class="meta-label">Aprobado por</td>
    <td>{{ $orden->aprobadoPor->name }}</td>
    <td class="meta-label">Fecha aprobación</td>
    <td>{{ $orden->fecha_aprobacion?->translatedFormat('d/m/Y') }}</td>
  </tr>
  @endif
</table>

@if($orden->motivo_anulacion)
<div class="info-box" style="border-left-color:#e53935; background:#fff5f5; margin-top:10px;">
  <strong>Motivo de Anulación:</strong><br>{{ $orden->motivo_anulacion }}
</div>
@endif

<div class="firmas">
  <div class="firma-celda">
    <div class="firma-linea">Elaborado por<br><strong>{{ $orden->creadoPor->name ?? 'Analista de Tesorería' }}</strong></div>
  </div>
  <div class="firma-celda">
    <div class="firma-linea">Revisado por<br><strong>{{ $orden->revisadoPor->name ?? 'Jefe de Tesorería' }}</strong></div>
  </div>
  <div class="firma-celda">
    <div class="firma-linea">Aprobado por<br><strong>{{ $orden->aprobadoPor->name ?? 'Director(a) de Administración' }}</strong></div>
  </div>
</div>

@endsection
