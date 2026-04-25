@extends('pdf.layout')

@section('doc-tipo')Inventario de Bienes Nacionales@endsection
@section('doc-numero')Listado General@endsection

@section('content')

<table class="meta-grid" style="margin-bottom:14px">
  <tr>
    <td class="meta-label">Total de bienes</td>
    <td>{{ $bienes->count() }}</td>
    <td class="meta-label">Fecha del reporte</td>
    <td>{{ now()->translatedFormat('d \d\e F \d\e Y') }}</td>
  </tr>
</table>

<table class="detail-table">
  <thead>
    <tr>
      <th style="width:60px">N° Inv.</th>
      <th>Descripción</th>
      <th>Categoría</th>
      <th>Marca / Modelo</th>
      <th>Serial</th>
      <th>Unidad Ejecutora</th>
      <th class="text-right" style="width:90px">Valor (Bs.)</th>
      <th class="text-center" style="width:70px">Estado</th>
    </tr>
  </thead>
  <tbody>
    @forelse($bienes as $bien)
    <tr>
      <td class="text-center">{{ $bien->numero_inventario }}</td>
      <td>{{ $bien->descripcion }}</td>
      <td>{{ $bien->categoria->nombre ?? '—' }}</td>
      <td>{{ trim(($bien->marca ?? '') . ' ' . ($bien->modelo ?? '')) ?: '—' }}</td>
      <td>{{ $bien->serial ?: '—' }}</td>
      <td>{{ $bien->unidadEjecutora->siglas ?? $bien->unidadEjecutora->nombre ?? '—' }}</td>
      <td class="text-right">{{ $bien->valor_adquisicion ? number_format($bien->valor_adquisicion, 2) : '—' }}</td>
      <td class="text-center">
        <span class="estado-badge estado-{{ str_replace(' ', '_', $bien->estado) }}"
          style="font-size:7px">{{ ucfirst($bien->estado) }}</span>
      </td>
    </tr>
    @empty
    <tr><td colspan="8" class="text-center" style="padding:16px;color:#888">No hay bienes registrados</td></tr>
    @endforelse
  </tbody>
  <tfoot>
    <tr style="background:#1F3864; color:white; font-weight:bold;">
      <td colspan="6" class="text-right" style="padding:6px 8px">VALOR TOTAL DEL INVENTARIO</td>
      <td class="text-right" style="padding:6px 8px">
        Bs. {{ number_format($bienes->sum('valor_adquisicion'), 2) }}
      </td>
      <td></td>
    </tr>
  </tfoot>
</table>

<div class="firmas">
  <div class="firma-celda">
    <div class="firma-linea">Jefe de Bienes Nacionales<br><strong>______________________</strong></div>
  </div>
  <div class="firma-celda">
    <div class="firma-linea">Director(a) de Administración<br><strong>______________________</strong></div>
  </div>
  <div class="firma-celda">
    <div class="firma-linea">Contralor(a) del Estado<br><strong>______________________</strong></div>
  </div>
</div>

@endsection
