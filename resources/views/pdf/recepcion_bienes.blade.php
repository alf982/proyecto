@extends('pdf.layout')

@section('watermark'){{ strtoupper($recepcion->estado) }}@endsection
@section('doc-tipo')Acta de Recepción de Bienes@endsection
@section('doc-numero'){{ $recepcion->numero }}@endsection

@section('content')

<table class="meta-grid">
  <tr>
    <td class="meta-label">Estado</td>
    <td><span class="estado-badge estado-{{ $recepcion->estado }}">{{ ucfirst($recepcion->estado) }}</span></td>
    <td class="meta-label">Fecha de Recepción</td>
    <td>{{ \Carbon\Carbon::parse($recepcion->fecha_recepcion)->translatedFormat('d/m/Y') }}</td>
  </tr>
  <tr>
    <td class="meta-label">Orden de Compra</td>
    <td>{{ $recepcion->orden?->numero ?? '—' }}</td>
    <td class="meta-label">Proveedor</td>
    <td>{{ $recepcion->orden?->proveedor_nombre ?? $recepcion->orden?->beneficiario?->razon_social ?? '—' }}</td>
  </tr>
  <tr>
    <td class="meta-label">Recibido Por</td>
    <td>{{ $recepcion->recibido_por }}</td>
    <td class="meta-label">Entregado Por</td>
    <td>{{ $recepcion->entregado_por ?? '—' }}</td>
  </tr>
  <tr>
    <td class="meta-label">N° Factura</td>
    <td>{{ $recepcion->numero_factura ?? '—' }}</td>
    <td class="meta-label">N° Guía</td>
    <td>{{ $recepcion->numero_guia ?? '—' }}</td>
  </tr>
  @if($recepcion->observaciones)
  <tr>
    <td class="meta-label">Observaciones</td>
    <td colspan="3">{{ $recepcion->observaciones }}</td>
  </tr>
  @endif
</table>

<div class="section-title">Artículos Recibidos</div>
<table class="detail-table">
  <thead>
    <tr>
      <th style="width:30px">#</th>
      <th>Código</th>
      <th>Descripción</th>
      <th class="text-center" style="width:50px">U/M</th>
      <th class="text-center" style="width:60px">Cant.</th>
      <th class="text-right" style="width:90px">P. Unitario</th>
      <th class="text-right" style="width:100px">Subtotal (Bs.)</th>
      <th class="text-center" style="width:70px">Condición</th>
    </tr>
  </thead>
  <tbody>
    @foreach($recepcion->detalles as $i => $det)
    <tr>
      <td class="text-center">{{ $i + 1 }}</td>
      <td class="text-center">{{ $det->articulo?->codigo ?? '—' }}</td>
      <td>{{ $det->ordenDetalle?->descripcion ?? $det->articulo?->nombre ?? '—' }}</td>
      <td class="text-center">{{ $det->articulo?->unidad_medida ?? '—' }}</td>
      <td class="text-center">{{ number_format($det->cantidad_recibida, 2) }}</td>
      <td class="text-right">{{ number_format($det->precio_unitario, 2) }}</td>
      <td class="text-right">{{ number_format($det->getSubtotal(), 2) }}</td>
      <td class="text-center">{{ ucfirst($det->condicion) }}</td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="totales clearfix">
  <div class="totales-inner">
    <div class="totales-row totales-total">
      <span class="totales-label">TOTAL RECIBIDO</span>
      <span class="totales-val">Bs. {{ number_format($recepcion->total_recibido, 2) }}</span>
    </div>
  </div>
</div>

<div class="section-title" style="margin-top:50px">Firmas de Conformidad</div>
<div class="firmas">
  <div class="firma-celda">
    <div class="firma-linea">Elaborado por<br><strong>{{ $recepcion->creadoPor?->name ?? '—' }}</strong></div>
  </div>
  <div class="firma-celda">
    <div class="firma-linea">Jefe de Almacén<br><strong>______________________</strong></div>
  </div>
  <div class="firma-celda">
    <div class="firma-linea">Representante del Proveedor<br><strong>______________________</strong></div>
  </div>
</div>

@endsection
