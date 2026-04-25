@extends('pdf.layout')

@section('watermark'){{ strtoupper($orden->estado) }}@endsection
@section('doc-tipo')Orden de Compra@endsection
@section('doc-numero'){{ $orden->numero }}@endsection

@section('content')

<table class="meta-grid">
  <tr>
    <td class="meta-label">Estado</td>
    <td><span class="estado-badge estado-{{ $orden->estado }}">{{ ucfirst($orden->estado) }}</span></td>
    <td class="meta-label">Fecha de Emisión</td>
    <td>{{ \Carbon\Carbon::parse($orden->fecha_emision)->translatedFormat('d/m/Y') }}</td>
  </tr>
  <tr>
    <td class="meta-label">Proveedor</td>
    <td colspan="3">
      {{ $orden->proveedor_nombre ?? '—' }}
      @if($orden->proveedor_rif) &mdash; RIF: {{ $orden->proveedor_rif }} @endif
    </td>
  </tr>
  <tr>
    <td class="meta-label">Condición de Pago</td>
    <td>{{ $orden->condicion_pago ?? 'Contado' }}</td>
    <td class="meta-label">Forma de entrega</td>
    <td>{{ $orden->forma_entrega ?? 'En almacén' }}</td>
  </tr>
  @if($orden->observaciones)
  <tr>
    <td class="meta-label">Observaciones</td>
    <td colspan="3">{{ $orden->observaciones }}</td>
  </tr>
  @endif
</table>

<div class="section-title">Artículos Solicitados</div>
<table class="detail-table">
  <thead>
    <tr>
      <th style="width:30px">#</th>
      <th>Código</th>
      <th>Descripción</th>
      <th class="text-center" style="width:50px">U/M</th>
      <th class="text-center" style="width:50px">Cant.</th>
      <th class="text-right" style="width:90px">P. Unitario</th>
      <th class="text-right" style="width:100px">Total (Bs.)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($orden->detalles as $i => $det)
    <tr>
      <td class="text-center">{{ $i + 1 }}</td>
      <td class="text-center">{{ $det->articulo->codigo ?? '—' }}</td>
      <td>{{ $det->articulo->descripcion ?? $det->descripcion }}</td>
      <td class="text-center">{{ $det->articulo->unidad_medida ?? '—' }}</td>
      <td class="text-center">{{ number_format($det->cantidad, 2) }}</td>
      <td class="text-right">{{ number_format($det->precio_unitario, 2) }}</td>
      <td class="text-right">{{ number_format($det->cantidad * $det->precio_unitario, 2) }}</td>
    </tr>
    @endforeach
  </tbody>
</table>

@php
  $subtotal = $orden->subtotal ?? $orden->detalles->sum(fn($d) => $d->cantidad * $d->precio_unitario);
  $total    = $orden->total ?? $subtotal;
@endphp

<div class="totales clearfix">
  <div class="totales-inner">
    <div class="totales-row">
      <span class="totales-label">Subtotal</span>
      <span class="totales-val">Bs. {{ number_format($subtotal, 2) }}</span>
    </div>
    <div class="totales-row totales-total">
      <span class="totales-label">TOTAL</span>
      <span class="totales-val">Bs. {{ number_format($total, 2) }}</span>
    </div>
  </div>
</div>

<div class="section-title" style="margin-top:50px">Autorización</div>
<div class="firmas">
  <div class="firma-celda">
    <div class="firma-linea">Elaborado por<br><strong>{{ $orden->creadoPor->name ?? 'Jefe de Compras' }}</strong></div>
  </div>
  <div class="firma-celda">
    <div class="firma-linea">Jefe de Compras<br><strong>______________________</strong></div>
  </div>
  <div class="firma-celda">
    <div class="firma-linea">Recibido por (Proveedor)<br><strong>______________________</strong></div>
  </div>
</div>

@endsection
