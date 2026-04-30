@extends('pdf.layout')

@section('watermark'){{ strtoupper($compromiso->estado) }}@endsection
@section('doc-tipo')Compromiso Presupuestario@endsection
@section('doc-numero'){{ $compromiso->numero }}@endsection

@section('content')

<table class="meta-grid">
  <tr>
    <td class="meta-label">Ejercicio Fiscal</td>
    <td>{{ $compromiso->ejercicioFiscal->anio ?? '—' }}</td>
    <td class="meta-label">Estado</td>
    <td><span class="estado-badge estado-{{ $compromiso->estado }}">{{ ucfirst($compromiso->estado) }}</span></td>
  </tr>
  <tr>
    <td class="meta-label">Unidad Ejecutora</td>
    <td colspan="3">{{ $compromiso->unidadEjecutora->nombre ?? '—' }}
      @if($compromiso->unidadEjecutora->siglas) ({{ $compromiso->unidadEjecutora->siglas }}) @endif
    </td>
  </tr>
  <tr>
    <td class="meta-label">Beneficiario</td>
    <td>{{ $compromiso->beneficiario }}</td>
    <td class="meta-label">RIF / CI</td>
    <td>{{ $compromiso->rif_beneficiario ?: '—' }}</td>
  </tr>
  <tr>
    <td class="meta-label">Tipo de Documento</td>
    <td>{{ ucfirst($compromiso->tipo_documento) }}</td>
    <td class="meta-label">N° Documento</td>
    <td>{{ $compromiso->numero_documento ?: '—' }}</td>
  </tr>
  <tr>
    <td class="meta-label">Fecha del Documento</td>
    <td>{{ $compromiso->fecha_documento ? $compromiso->fecha_documento->translatedFormat('d/m/Y') : '—' }}</td>
    <td class="meta-label">Fecha de Compromiso</td>
    <td>{{ $compromiso->fecha_compromiso ? $compromiso->fecha_compromiso->translatedFormat('d/m/Y') : '—' }}</td>
  </tr>
  <tr>
    <td class="meta-label">Partida Presupuestaria</td>
    <td colspan="3">
      {{ $compromiso->partida->codigo ?? '' }} — {{ $compromiso->partida->descripcion ?? '—' }}
    </td>
  </tr>
  <tr>
    <td class="meta-label">Proyecto</td>
    <td colspan="3">{{ $compromiso->proyecto->nombre ?? '—' }}</td>
  </tr>
  <tr>
    <td class="meta-label">Concepto</td>
    <td colspan="3">{{ $compromiso->concepto }}</td>
  </tr>
</table>

<div class="section-title">Detalle Financiero</div>
<table class="detail-table">
  <thead>
    <tr>
      <th>Concepto</th>
      <th class="text-right" style="width:130px">Monto (Bs.)</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Base Imponible (Sujeto a IVA)</td>
      <td class="text-right">{{ number_format($compromiso->monto_sin_iva, 2) }}</td>
    </tr>
    @if($compromiso->alicuota_iva > 0)
    <tr>
      <td>IVA ({{ number_format($compromiso->alicuota_iva, 2) }}%)</td>
      <td class="text-right">{{ number_format($compromiso->montoIva(), 2) }}</td>
    </tr>
    @endif
    <tr style="background:#f1f3f5; font-weight:bold; font-size:1.1em">
      <td>Total Compromiso</td>
      <td class="text-right">{{ number_format($compromiso->monto, 2) }}</td>
    </tr>
  </tbody>
</table>

@if($compromiso->observaciones)
<div class="info-box">
  <strong>Observaciones:</strong><br>{{ $compromiso->observaciones }}
</div>
@endif

@if($compromiso->motivo_anulacion)
<div class="info-box" style="border-left-color:#e53935; background:#fff5f5;">
  <strong>Motivo de Anulación:</strong><br>{{ $compromiso->motivo_anulacion }}
</div>
@endif

<div class="section-title">Crédito Presupuestario (Situación)</div>
@if($compromiso->credito)
<table class="meta-grid">
  <tr>
    <td class="meta-label">Monto Vigente</td>
    <td>Bs. {{ number_format($compromiso->credito->monto_vigente, 2) }}</td>
    <td class="meta-label">Monto Disponible</td>
    <td>Bs. {{ number_format($compromiso->credito->monto_vigente - $compromiso->credito->monto_comprometido, 2) }}</td>
  </tr>
</table>
@endif

<div class="section-title">Trazabilidad</div>
<table class="meta-grid">
  <tr>
    <td class="meta-label">Registrado por</td>
    <td>{{ $compromiso->creadoPor->name ?? '—' }}</td>
    <td class="meta-label">Fecha de registro</td>
    <td>{{ $compromiso->created_at?->translatedFormat('d/m/Y H:i') }}</td>
  </tr>
  @if($compromiso->aprobadoPor)
  <tr>
    <td class="meta-label">Aprobado por</td>
    <td>{{ $compromiso->aprobadoPor->name }}</td>
    <td class="meta-label">Fecha de aprobación</td>
    <td>{{ $compromiso->fecha_aprobacion ? $compromiso->fecha_aprobacion->translatedFormat('d/m/Y') : '—' }}</td>
  </tr>
  @endif
</table>

<!-- FIRMAS -->
<div class="firmas">
  <div class="firma-celda">
    <div class="firma-linea">Elaborado por<br><strong>{{ $compromiso->creadoPor->name ?? 'Analista de Presupuesto' }}</strong></div>
  </div>
  <div class="firma-celda">
    <div class="firma-linea">Revisado por<br><strong>Jefe de la Unidad</strong></div>
  </div>
  <div class="firma-celda">
    <div class="firma-linea">Aprobado por<br><strong>{{ $compromiso->aprobadoPor->name ?? 'Director(a) de Administración' }}</strong></div>
  </div>
</div>

@endsection
