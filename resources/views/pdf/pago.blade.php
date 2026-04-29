@extends('pdf.layout')

@section('watermark'){{ strtoupper($pago->estado) }}@endsection
@section('doc-tipo')Orden de Pago Presupuestario@endsection
@section('doc-numero'){{ $pago->numero }}@endsection

@section('content')

<table class="meta-grid">
  <tr>
    <td class="meta-label">Ejercicio Fiscal</td>
    <td>{{ $pago->ejercicioFiscal->anio ?? '—' }}</td>
    <td class="meta-label">Estado</td>
    <td><span class="estado-badge estado-{{ $pago->estado }}">{{ ucfirst($pago->estado) }}</span></td>
  </tr>
  <tr>
    <td class="meta-label">Unidad Ejecutora</td>
    <td colspan="3">{{ $pago->unidadEjecutora->nombre ?? '—' }}
      @if($pago->unidadEjecutora->siglas) ({{ $pago->unidadEjecutora->siglas }}) @endif
    </td>
  </tr>
  <tr>
    <td class="meta-label">Beneficiario</td>
    <td>{{ $pago->beneficiario }}</td>
    <td class="meta-label">RIF / CI</td>
    <td>{{ $pago->rif_beneficiario ?: '—' }}</td>
  </tr>
  <tr>
    <td class="meta-label">Causación Origen</td>
    <td>{{ $pago->causacion->numero ?? '—' }}</td>
    <td class="meta-label">Partida Presupuestaria</td>
    <td>{{ $pago->causacion->partida->codigo ?? '—' }}</td>
  </tr>
  <tr>
    <td class="meta-label">Tipo de Pago</td>
    <td>{{ ucfirst($pago->tipo_pago) }}</td>
    <td class="meta-label">Fecha de Pago</td>
    <td>{{ $pago->fecha_pago ? \Carbon\Carbon::parse($pago->fecha_pago)->translatedFormat('d/m/Y') : '—' }}</td>
  </tr>
  @if($pago->numero_referencia || $pago->banco || $pago->cuenta_bancaria)
  <tr>
    <td class="meta-label">Banco</td>
    <td>{{ $pago->banco ?: '—' }}</td>
    <td class="meta-label">N° Referencia / Cuenta</td>
    <td>{{ $pago->numero_referencia ?: '—' }} / {{ $pago->cuenta_bancaria ?: '—' }}</td>
  </tr>
  @endif
  <tr>
    <td class="meta-label">Concepto</td>
    <td colspan="3">{{ $pago->concepto }}</td>
  </tr>
</table>

<div class="section-title">Detalle Financiero</div>
<table class="detail-table">
  <thead>
    <tr>
      <th>Descripción</th>
      <th class="text-right" style="width:150px">Monto (Bs.)</th>
    </tr>
  </thead>
  <tbody>
    @php
      $totalRetenido = $pago->retenciones->sum('monto_retenido');
      $montoBruto = (float)$pago->monto_pagado + (float)$totalRetenido;
    @endphp
    <tr>
      <td>Monto Bruto a Pagar</td>
      <td class="text-right">{{ number_format($montoBruto, 2) }}</td>
    </tr>
    @if($pago->retenciones->count() > 0)
      @foreach($pago->retenciones as $ret)
      <tr>
        <td style="padding-left: 20px;">Retención: {{ $ret->retencion->nombre }} ({{ $ret->porcentaje_aplicado ? number_format($ret->porcentaje_aplicado, 2).'%' : 'Fijo' }})</td>
        <td class="text-right" style="color:#c62828">– {{ number_format($ret->monto_retenido, 2) }}</td>
      </tr>
      @endforeach
      <tr style="background:#fff3e0; font-weight:bold;">
        <td>Total Retenciones</td>
        <td class="text-right" style="color:#c62828">– {{ number_format($totalRetenido, 2) }}</td>
      </tr>
    @endif
    <tr style="background:#e8f5e9; font-weight:bold; font-size:14px;">
      <td>Monto Neto Pagado al Beneficiario</td>
      <td class="text-right">Bs. {{ number_format($pago->monto_pagado, 2) }}</td>
    </tr>
  </tbody>
</table>

@if($pago->observaciones)
<div class="info-box">
  <strong>Observaciones:</strong><br>{{ $pago->observaciones }}
</div>
@endif

@if($pago->motivo_anulacion)
<div class="info-box" style="border-left-color:#e53935; background:#fff5f5;">
  <strong>Motivo de Anulación:</strong><br>{{ $pago->motivo_anulacion }}
</div>
@endif

<div class="section-title">Trazabilidad</div>
<table class="meta-grid">
  <tr>
    <td class="meta-label">Registrado por</td>
    <td>{{ $pago->creadoPor->name ?? '—' }}</td>
    <td class="meta-label">Fecha de registro</td>
    <td>{{ $pago->created_at?->translatedFormat('d/m/Y H:i') }}</td>
  </tr>
</table>

<!-- FIRMAS -->
<div class="firmas">
  <div class="firma-celda">
    <div class="firma-linea">Elaborado por<br><strong>{{ $pago->creadoPor->name ?? 'Analista de Tesorería' }}</strong></div>
  </div>
  <div class="firma-celda">
    <div class="firma-linea">Revisado por<br><strong>Jefe de Tesorería</strong></div>
  </div>
  <div class="firma-celda">
    <div class="firma-linea">Aprobado por<br><strong>Director(a) de Administración</strong></div>
  </div>
</div>

@endsection
