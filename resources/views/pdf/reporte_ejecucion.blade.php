@extends('pdf.layout')

@section('doc-tipo')Reporte de Ejecución Presupuestaria@endsection
@section('doc-numero')Ejercicio Fiscal {{ $ejercicio?->anio ?? '—' }}@endsection

@section('content')

{{-- Meta info --}}
<table class="meta-grid" style="margin-bottom:14px">
  <tr>
    <td class="meta-label">Ejercicio Fiscal</td>
    <td>{{ $ejercicio?->anio ?? '—' }}</td>
    <td class="meta-label">Estado</td>
    <td>{{ ucfirst($ejercicio?->estado ?? '—') }}</td>
    <td class="meta-label">Fecha del Reporte</td>
    <td>{{ now()->translatedFormat('d \d\e F \d\e Y') }}</td>
  </tr>
</table>

{{-- KPIs principales --}}
<table class="detail-table" style="margin-bottom:16px">
  <thead>
    <tr>
      <th class="text-right">Presupuesto Aprobado</th>
      <th class="text-right">Monto Vigente</th>
      <th class="text-right">Comprometido</th>
      <th class="text-right">Causado</th>
      <th class="text-right">Pagado</th>
      <th class="text-right">Saldo Disponible</th>
      <th class="text-center">% Ejecución</th>
    </tr>
  </thead>
  <tbody>
    <tr style="font-weight:bold; background:#f0f4ff;">
      <td class="text-right">Bs. {{ number_format($totales['aprobado'], 2) }}</td>
      <td class="text-right">Bs. {{ number_format($totales['vigente'], 2) }}</td>
      <td class="text-right">Bs. {{ number_format($totales['comprometido'], 2) }}</td>
      <td class="text-right">Bs. {{ number_format($totales['causado'], 2) }}</td>
      <td class="text-right">Bs. {{ number_format($totales['pagado'], 2) }}</td>
      <td class="text-right">Bs. {{ number_format($totales['disponible'], 2) }}</td>
      <td class="text-center">{{ $totales['pct_ejec'] }}%</td>
    </tr>
  </tbody>
</table>

{{-- Detalle por partida --}}
<p style="font-weight:bold; font-size:9px; color:#1F3864; margin-bottom:4px; text-transform:uppercase; letter-spacing:.5px;">
  Detalle por Partida Presupuestaria
</p>
<table class="detail-table" style="margin-bottom:16px">
  <thead>
    <tr>
      <th style="width:80px">Código</th>
      <th>Descripción</th>
      <th class="text-right" style="width:100px">Aprobado</th>
      <th class="text-right" style="width:100px">Vigente</th>
      <th class="text-right" style="width:100px">Comprometido</th>
      <th class="text-right" style="width:100px">Disponible</th>
    </tr>
  </thead>
  <tbody>
    @forelse($porPartida as $p)
    <tr>
      <td style="font-family:monospace;font-size:8px">{{ $p['codigo'] }}</td>
      <td>{{ $p['descripcion'] }}</td>
      <td class="text-right">{{ number_format($p['aprobado'], 2) }}</td>
      <td class="text-right">{{ number_format($p['vigente'], 2) }}</td>
      <td class="text-right">{{ number_format($p['comprometido'], 2) }}</td>
      <td class="text-right">{{ number_format($p['disponible'], 2) }}</td>
    </tr>
    @empty
    <tr><td colspan="6" class="text-center" style="padding:12px;color:#888">Sin partidas con movimientos en este período</td></tr>
    @endforelse
  </tbody>
  <tfoot>
    <tr style="background:#1F3864; color:white; font-weight:bold;">
      <td colspan="2" class="text-right" style="padding:6px 8px">TOTALES</td>
      <td class="text-right" style="padding:6px 8px">{{ number_format($totales['aprobado'], 2) }}</td>
      <td class="text-right" style="padding:6px 8px">{{ number_format($totales['vigente'], 2) }}</td>
      <td class="text-right" style="padding:6px 8px">{{ number_format($totales['comprometido'], 2) }}</td>
      <td class="text-right" style="padding:6px 8px">{{ number_format($totales['disponible'], 2) }}</td>
    </tr>
  </tfoot>
</table>

{{-- Últimos movimientos --}}
@if($ultimosMovimientos->isNotEmpty())
<p style="font-weight:bold; font-size:9px; color:#1F3864; margin-bottom:4px; text-transform:uppercase; letter-spacing:.5px;">
  Últimos Movimientos de Partida
</p>
<table class="detail-table">
  <thead>
    <tr>
      <th>Número</th>
      <th>Tipo</th>
      <th>Partida</th>
      <th>Concepto</th>
      <th class="text-right">Monto</th>
      <th class="text-right">Saldo Post.</th>
      <th class="text-center">Fecha</th>
    </tr>
  </thead>
  <tbody>
    @foreach($ultimosMovimientos as $mov)
    <tr>
      <td style="font-size:8px">{{ $mov->numero }}</td>
      <td><span style="text-transform:uppercase;font-size:8px;font-weight:bold">{{ $mov->tipo }}</span></td>
      <td style="font-family:monospace;font-size:8px">{{ $mov->partida?->codigo ?? '—' }}</td>
      <td>{{ \Illuminate\Support\Str::limit($mov->concepto, 50) }}</td>
      <td class="text-right">{{ number_format($mov->monto, 2) }}</td>
      <td class="text-right">{{ number_format($mov->saldo_posterior, 2) }}</td>
      <td class="text-center">{{ \Carbon\Carbon::parse($mov->fecha_movimiento)->format('d/m/Y') }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif

<div class="firmas" style="margin-top:20px">
  <div class="firma-celda">
    <div class="firma-linea">Jefe de Presupuesto<br><strong>______________________</strong></div>
  </div>
  <div class="firma-celda">
    <div class="firma-linea">Director(a) de Administración<br><strong>______________________</strong></div>
  </div>
  <div class="firma-celda">
    <div class="firma-linea">Contralor(a) del Estado<br><strong>______________________</strong></div>
  </div>
</div>

@endsection
