@extends('pdf.layout')

@section('watermark'){{ strtoupper($causacion->estado) }}@endsection
@section('doc-tipo')Causación Presupuestaria@endsection
@section('doc-numero'){{ $causacion->numero }}@endsection

@section('content')

<table class="meta-grid">
  <tr>
    <td class="meta-label">Ejercicio Fiscal</td>
    <td>{{ $causacion->ejercicioFiscal->anio ?? '—' }}</td>
    <td class="meta-label">Estado</td>
    <td><span class="estado-badge estado-{{ $causacion->estado }}">{{ ucfirst($causacion->estado) }}</span></td>
  </tr>
  <tr>
    <td class="meta-label">Unidad Ejecutora</td>
    <td colspan="3">{{ $causacion->unidadEjecutora->nombre ?? '—' }}
      @if($causacion->unidadEjecutora->siglas) ({{ $causacion->unidadEjecutora->siglas }}) @endif
    </td>
  </tr>
  <tr>
    <td class="meta-label">Beneficiario</td>
    <td>{{ $causacion->beneficiario }}</td>
    <td class="meta-label">RIF / CI</td>
    <td>{{ $causacion->rif_beneficiario ?: '—' }}</td>
  </tr>
  <tr>
    <td class="meta-label">Tipo de Documento</td>
    <td>{{ ucfirst($causacion->tipo_documento) }}</td>
    <td class="meta-label">N° Documento</td>
    <td>{{ $causacion->numero_documento ?: '—' }}</td>
  </tr>
  <tr>
    <td class="meta-label">Fecha del Documento</td>
    <td>{{ $causacion->fecha_documento ? \Carbon\Carbon::parse($causacion->fecha_documento)->translatedFormat('d/m/Y') : '—' }}</td>
    <td class="meta-label">Fecha de Causación</td>
    <td>{{ \Carbon\Carbon::parse($causacion->fecha_causacion)->translatedFormat('d/m/Y') }}</td>
  </tr>
  <tr>
    <td class="meta-label">Partida Presupuestaria</td>
    <td colspan="3">
      {{ $causacion->partida->codigo ?? '' }} — {{ $causacion->partida->descripcion ?? '—' }}
    </td>
  </tr>
  <tr>
    <td class="meta-label">Proyecto</td>
    <td colspan="3">{{ $causacion->proyecto->nombre ?? '—' }}</td>
  </tr>
  <tr>
    <td class="meta-label">Concepto</td>
    <td colspan="3">{{ $causacion->concepto }}</td>
  </tr>
  @if($causacion->descripcion_documento)
  <tr>
    <td class="meta-label">Descripción</td>
    <td colspan="3">{{ $causacion->descripcion_documento }}</td>
  </tr>
  @endif
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
      <td>Monto de la Causación</td>
      <td class="text-right">{{ number_format($causacion->monto_causado, 2) }}</td>
    </tr>
    @if($causacion->monto_retencion > 0)
    <tr>
      <td>Retención</td>
      <td class="text-right" style="color:#c62828">– {{ number_format($causacion->monto_retencion, 2) }}</td>
    </tr>
    <tr style="background:#e8f5e9; font-weight:bold;">
      <td>Monto Neto a Pagar</td>
      <td class="text-right">{{ number_format($causacion->monto_causado - $causacion->monto_retencion, 2) }}</td>
    </tr>
    @endif
  </tbody>
</table>

@if($causacion->observaciones)
<div class="info-box">
  <strong>Observaciones:</strong><br>{{ $causacion->observaciones }}
</div>
@endif

@if($causacion->motivo_anulacion)
<div class="info-box" style="border-left-color:#e53935; background:#fff5f5;">
  <strong>Motivo de Anulación:</strong><br>{{ $causacion->motivo_anulacion }}
</div>
@endif

<div class="section-title">Crédito Presupuestario</div>
@if($causacion->credito)
<table class="meta-grid">
  <tr>
    <td class="meta-label">Monto Inicial</td>
    <td>Bs. {{ number_format($causacion->credito->monto_inicial, 2) }}</td>
    <td class="meta-label">Monto Vigente</td>
    <td>Bs. {{ number_format($causacion->credito->monto_vigente, 2) }}</td>
  </tr>
  <tr>
    <td class="meta-label">Comprometido</td>
    <td>Bs. {{ number_format($causacion->credito->monto_comprometido, 2) }}</td>
    <td class="meta-label">Causado</td>
    <td>Bs. {{ number_format($causacion->credito->monto_causado, 2) }}</td>
  </tr>
</table>
@endif

<div class="section-title">Trazabilidad</div>
<table class="meta-grid">
  <tr>
    <td class="meta-label">Registrado por</td>
    <td>{{ $causacion->creadoPor->name ?? '—' }}</td>
    <td class="meta-label">Fecha de registro</td>
    <td>{{ $causacion->created_at?->translatedFormat('d/m/Y H:i') }}</td>
  </tr>
  @if($causacion->aprobadoPor)
  <tr>
    <td class="meta-label">Aprobado por</td>
    <td>{{ $causacion->aprobadoPor->name }}</td>
    <td class="meta-label">Fecha de aprobación</td>
    <td>{{ $causacion->fecha_aprobacion ? \Carbon\Carbon::parse($causacion->fecha_aprobacion)->translatedFormat('d/m/Y') : '—' }}</td>
  </tr>
  @endif
</table>

<!-- FIRMAS -->
<div class="firmas">
  <div class="firma-celda">
    <div class="firma-linea">Elaborado por<br><strong>{{ $causacion->creadoPor->name ?? 'Analista de Presupuesto' }}</strong></div>
  </div>
  <div class="firma-celda">
    <div class="firma-linea">Revisado por<br><strong>Jefe de la Unidad</strong></div>
  </div>
  <div class="firma-celda">
    <div class="firma-linea">Aprobado por<br><strong>{{ $causacion->aprobadoPor->name ?? 'Director(a) de Administración' }}</strong></div>
  </div>
</div>

@endsection
