@extends('pdf.layout')

@section('watermark'){{ strtoupper($nomina->estado) }}@endsection
@section('doc-tipo')Nómina de Personal@endsection
@section('doc-numero'){{ $nomina->numero }}@endsection

@section('content')

<table class="meta-grid">
  <tr>
    <td class="meta-label">Tipo de Nómina</td>
    <td>{{ ucfirst(str_replace('_',' ', $nomina->tipo_nomina)) }}</td>
    <td class="meta-label">Estado</td>
    <td><span class="estado-badge estado-{{ $nomina->estado }}">{{ ucfirst($nomina->estado) }}</span></td>
  </tr>
  <tr>
    <td class="meta-label">Período Inicio</td>
    <td>{{ \Carbon\Carbon::parse($nomina->periodo_inicio)->translatedFormat('d/m/Y') }}</td>
    <td class="meta-label">Período Fin</td>
    <td>{{ \Carbon\Carbon::parse($nomina->periodo_fin)->translatedFormat('d/m/Y') }}</td>
  </tr>
  <tr>
    <td class="meta-label">Ejercicio Fiscal</td>
    <td>{{ $nomina->ejercicioFiscal->anio ?? '—' }}</td>
    <td class="meta-label">Total empleados</td>
    <td>{{ $nomina->detalles->count() }}</td>
  </tr>
  @if($nomina->observaciones)
  <tr>
    <td class="meta-label">Observaciones</td>
    <td colspan="3">{{ $nomina->observaciones }}</td>
  </tr>
  @endif
</table>

<div class="section-title">Detalle por Empleado</div>
<table class="detail-table">
  <thead>
    <tr>
      <th>#</th>
      <th>Empleado</th>
      <th>Cédula</th>
      <th>Cargo</th>
      <th class="text-right">Salario Base</th>
      <th class="text-right">Asignaciones</th>
      <th class="text-right">Deducciones</th>
      <th class="text-right">Neto a Pagar</th>
    </tr>
  </thead>
  <tbody>
    @foreach($nomina->detalles as $i => $det)
    <tr>
      <td class="text-center">{{ $i + 1 }}</td>
      <td>{{ $det->empleado->nombre_completo ?? ($det->empleado->nombre . ' ' . $det->empleado->apellido) }}</td>
      <td class="text-center">{{ $det->empleado->cedula }}</td>
      <td>{{ $det->empleado->cargo->nombre ?? '—' }}</td>
      <td class="text-right">{{ number_format($det->salario_base, 2) }}</td>
      <td class="text-right">{{ number_format($det->total_asignaciones, 2) }}</td>
      <td class="text-right" style="color:#c62828">{{ number_format($det->total_deducciones, 2) }}</td>
      <td class="text-right" style="font-weight:bold">{{ number_format($det->neto, 2) }}</td>
    </tr>
    @endforeach
  </tbody>
  <tfoot>
    <tr style="background:#1F3864; color:white; font-weight:bold;">
      <td colspan="5" class="text-right" style="padding:6px 8px">TOTALES</td>
      <td class="text-right" style="padding:6px 8px">{{ number_format($nomina->total_asignaciones, 2) }}</td>
      <td class="text-right" style="padding:6px 8px">{{ number_format($nomina->total_deducciones, 2) }}</td>
      <td class="text-right" style="padding:6px 8px">{{ number_format($nomina->total_neto, 2) }}</td>
    </tr>
  </tfoot>
</table>

@if($nomina->retenciones->count() > 0)
<div class="section-title">Retenciones Fiscales / Institucionales</div>
<table class="detail-table" style="width: 60%;">
  <thead>
    <tr>
      <th>Retención</th>
      <th class="text-right">Porcentaje</th>
      <th class="text-right">Base de Cálculo</th>
      <th class="text-right">Monto Retenido</th>
    </tr>
  </thead>
  <tbody>
    @foreach($nomina->retenciones as $retencion)
    <tr>
      <td>{{ $retencion->retencion->nombre ?? 'Retención' }}</td>
      <td class="text-right">{{ number_format($retencion->porcentaje_aplicado, 2) }}%</td>
      <td class="text-right">{{ number_format($retencion->monto_base, 2) }}</td>
      <td class="text-right" style="color:#c62828">{{ number_format($retencion->monto_retenido, 2) }}</td>
    </tr>
    @endforeach
  </tbody>
  <tfoot>
    <tr style="background:#f3f4f6; font-weight:bold;">
      <td colspan="3" class="text-right">TOTAL RETENIDO</td>
      <td class="text-right" style="color:#c62828">{{ number_format($nomina->retenciones->sum('monto_retenido'), 2) }}</td>
    </tr>
    <tr style="background:#e0e7ff; font-weight:bold;">
      <td colspan="3" class="text-right">NETO FINAL (A DISPERSAR)</td>
      <td class="text-right" style="color:#1e40af">{{ number_format($nomina->total_neto - $nomina->retenciones->sum('monto_retenido'), 2) }}</td>
    </tr>
  </tfoot>
</table>
@endif

<div class="section-title">Trazabilidad</div>
<table class="meta-grid">
  <tr>
    <td class="meta-label">Elaborado por</td>
    <td>{{ $nomina->creadoPor->name ?? '—' }}</td>
    <td class="meta-label">Fecha</td>
    <td>{{ $nomina->created_at?->translatedFormat('d/m/Y') }}</td>
  </tr>
  @if($nomina->aprobadoPor)
  <tr>
    <td class="meta-label">Aprobado por</td>
    <td>{{ $nomina->aprobadoPor->name }}</td>
    <td class="meta-label">Fecha aprobación</td>
    <td>{{ $nomina->fecha_aprobacion?->translatedFormat('d/m/Y') }}</td>
  </tr>
  @endif
</table>

<div class="firmas">
  <div class="firma-celda">
    <div class="firma-linea">Jefe de Recursos Humanos<br><strong>______________________</strong></div>
  </div>
  <div class="firma-celda">
    <div class="firma-linea">Director(a) de Administración<br><strong>{{ $nomina->aprobadoPor->name ?? '______________________' }}</strong></div>
  </div>
</div>

@endsection
