@extends('pdf.layout')

@section('watermark')RETENCIÓN@endsection
@section('doc-tipo')Comprobante de Retención@endsection
@section('doc-numero')RET-{{ str_pad($retencionAplicada->id, 6, '0', STR_PAD_LEFT) }}@endsection

@section('content')

@php
    $origen = $retencionAplicada->retencionable;
    $beneficiario = $origen->beneficiario ?? '—';
    $rif = $origen->rif_beneficiario ?? '—';
    $fecha = $origen->fecha_pago ?? $origen->created_at;
    $numero_origen = $origen->numero ?? '—';
    $tipo_origen = class_basename($origen);
@endphp

<table class="meta-grid">
  <tr>
    <td class="meta-label">Beneficiario</td>
    <td colspan="3">{{ $beneficiario }}</td>
  </tr>
  <tr>
    <td class="meta-label">RIF / C.I.</td>
    <td>{{ $rif }}</td>
    <td class="meta-label">Fecha del Comprobante</td>
    <td>{{ $fecha ? \Carbon\Carbon::parse($fecha)->translatedFormat('d/m/Y') : '—' }}</td>
  </tr>
  <tr>
    <td class="meta-label">Documento Origen</td>
    <td>{{ $tipo_origen }} N° {{ $numero_origen }}</td>
    <td class="meta-label">Tipo de Retención</td>
    <td>{{ $retencionAplicada->retencion->nombre }}</td>
  </tr>
</table>

<div class="section-title">Detalle del Cálculo</div>
<table class="detail-table">
  <thead>
    <tr>
      <th>Concepto de Retención</th>
      <th class="text-center" style="width:100px">Base (Bs.)</th>
      <th class="text-center" style="width:80px">% Aplicado</th>
      <th class="text-right" style="width:130px">Monto Retenido (Bs.)</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>
          {{ $retencionAplicada->retencion->nombre }}
          @if($retencionAplicada->retencion->descripcion)
          <br><small style="color:#555;">{{ $retencionAplicada->retencion->descripcion }}</small>
          @endif
      </td>
      <td class="text-center">{{ number_format($retencionAplicada->monto_base, 2) }}</td>
      <td class="text-center">{{ $retencionAplicada->porcentaje_aplicado ? number_format($retencionAplicada->porcentaje_aplicado, 2).'%' : '—' }}</td>
      <td class="text-right" style="font-weight:bold;">{{ number_format($retencionAplicada->monto_retenido, 2) }}</td>
    </tr>
  </tbody>
</table>

<div style="margin-top: 40px; font-size: 12px; text-align: center; color: #555;">
    Este comprobante certifica la retención efectuada según las normativas vigentes correspondientes a 
    <strong>{{ $retencionAplicada->retencion->nombre }}</strong>.
</div>

<!-- FIRMAS -->
<div class="firmas" style="margin-top: 80px;">
  <div class="firma-celda">
    <div class="firma-linea">Beneficiario<br><strong>Recibí Conforme</strong></div>
  </div>
  <div class="firma-celda">
    <div class="firma-linea">Agente de Retención<br><strong>Firma y Sello Autorizado</strong></div>
  </div>
</div>

@endsection
