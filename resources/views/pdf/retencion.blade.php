@extends('pdf.layout')

@section('watermark')COMPROBANTE@endsection
@section('doc-tipo')Comprobante de Retención@endsection
@section('doc-numero'){{ str_pad($retencion->id, 8, '0', STR_PAD_LEFT) }}@endsection

@section('content')

<div class="info-box">
    Este documento certifica que se ha efectuado la retención de impuesto correspondiente a la transacción descrita a continuación, de acuerdo con la normativa legal vigente de la República Bolivariana de Venezuela.
</div>

<div class="section-title">Datos del Agente de Retención</div>
<table class="meta-grid">
    <tr>
        <td class="meta-label">Razón Social</td>
        <td>Contraloría del Estado Portuguesa</td>
        <td class="meta-label">RIF</td>
        <td>G-20000101-0</td>
    </tr>
    <tr>
        <td class="meta-label">Dirección</td>
        <td colspan="3">Av. Circunvalación, Edif. Contraloría, Guanare, Estado Portuguesa.</td>
    </tr>
</table>

<div class="section-title">Datos del Sujeto Retenido (Beneficiario)</div>
<table class="meta-grid">
    <tr>
        <td class="meta-label">Nombre / Razón Social</td>
        <td colspan="3">{{ $retencion->retencionable->beneficiario ?? '—' }}</td>
    </tr>
    <tr>
        <td class="meta-label">RIF / C.I.</td>
        <td>{{ $retencion->retencionable->rif_beneficiario ?? '—' }}</td>
        <td class="meta-label">Fecha Emisión</td>
        <td>{{ $retencion->created_at->format('d/m/Y') }}</td>
    </tr>
</table>

<div class="section-title">Detalle de la Retención</div>
<table class="detail-table">
    <thead>
        <tr>
            <th>Concepto / Descripción</th>
            <th class="text-right">Base Imponible (Bs.)</th>
            <th class="text-center">Alícuota (%)</th>
            <th class="text-right">Monto Retenido (Bs.)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <strong>{{ $retencion->retencion->nombre }}</strong><br>
                <span style="font-size: 8px; color: #666;">
                    Documento Origen: {{ $retencion->retencionable->numero ?? 'N/A' }}<br>
                    {{ $retencion->retencionable->concepto ?? '' }}
                </span>
            </td>
            <td class="text-right">{{ number_format($retencion->monto_base, 2) }}</td>
            <td class="text-center">
                {{ $retencion->porcentaje_aplicado > 0 ? number_format($retencion->porcentaje_aplicado, 2) . '%' : 'Monto Fijo' }}
            </td>
            <td class="text-right" style="font-weight: bold;">{{ number_format($retencion->monto_retenido, 2) }}</td>
        </tr>
    </tbody>
</table>

<div class="totales clearfix">
    <div class="totales-inner">
        <div class="totales-row totales-total">
            <div class="totales-label">TOTAL RETENIDO:</div>
            <div class="totales-val">Bs. {{ number_format($retencion->monto_retenido, 2) }}</div>
        </div>
    </div>
</div>

<div style="margin-top: 50px; font-size: 9px; color: #555; text-align: justify;">
    <strong>Nota Legales:</strong> El presente comprobante se emite de conformidad con lo establecido en la Ley de Impuesto sobre la Renta (ISLR) y/o la Ley del Impuesto al Valor Agregado (IVA), según corresponda al concepto de la retención. El monto retenido será enterado al Fisco Nacional dentro de los plazos legales establecidos.
</div>

<div class="firmas">
    <div class="firma-celda">
        <div class="firma-linea">Agente de Retención<br>(Sello y Firma)</div>
    </div>
    <div class="firma-celda" style="width: 38%;">
        <!-- Espacio central -->
    </div>
    <div class="firma-celda">
        <div class="firma-linea">Sujeto Retenido<br>(Recibido conforme)</div>
    </div>
</div>

@endsection
