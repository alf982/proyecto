<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0;padding:0;box-sizing:border-box; }
    body { font-family:'DejaVu Sans',sans-serif;font-size:9px;color:#1a1a2e; }
    .header { background:#1e3a5f;color:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center; }
    .header h1 { font-size:13px;font-weight:700; }
    .header code { font-family:'DejaVu Sans Mono',monospace;font-size:14px;color:#7dd3fc; }
    .body { padding:14px 20px; }
    .row { display:flex;border-bottom:1px solid #f3f4f6;padding:4px 0; }
    .row label { width:130px;font-size:8px;color:#9ca3af;text-transform:uppercase;flex-shrink:0; }
    .row span { font-size:9px;font-weight:600; }
    .monto-box { background:#f0fdf4;border:2px solid #bbf7d0;border-radius:8px;padding:10px 16px;margin:12px 0;display:flex;justify-content:space-between;align-items:center; }
    table { width:100%;border-collapse:collapse;margin:8px 0; }
    th { background:#f9fafb;font-size:7.5px;padding:4px 6px;text-align:left;color:#6b7280;text-transform:uppercase; }
    td { padding:4px 6px;font-size:8.5px;border-bottom:1px solid #f3f4f6; }
    .tr { text-align:right; }
    .firmas-table { width:100%; margin-top:35px; border-top:1px solid #e5e7eb; border-collapse: collapse; padding-top: 25px; }
    .firmas-table td { width: 50%; text-align: center; border: none; padding: 0 30px; vertical-align: bottom; }
    .firma-linea { border-top: 1px solid #9ca3af; padding-top: 4px; font-size: 8px; font-weight: 700; margin-top: 40px; }
    .footer { text-align:center;font-size:7px;color:#9ca3af;margin-top:12px;padding-top:6px;border-top:1px solid #e5e7eb; }
</style>
</head>
<body>
<div class="header">
    <div>
        <div style="font-size:7px;opacity:.7;margin-bottom:2px;">RECIBO INDIVIDUAL DE NÓMINA</div>
        <h1>{{ $nomina->tipo_nomina ? strtoupper($nomina->tipo_nomina) : 'NÓMINA' }}</h1>
        <div style="font-size:8px;opacity:.7;">{{ $nomina->periodo_inicio?->format('d/m/Y') }} — {{ $nomina->periodo_fin?->format('d/m/Y') }}</div>
    </div>
    <code>{{ $nomina->numero }}</code>
</div>

<div class="body">
    <div style="background:#eff6ff;border-radius:8px;padding:10px 14px;margin-bottom:10px;">
        <div class="row"><label>Empleado</label><span style="font-size:12px;color:#1e40af;">{{ $detalle->empleado?->nombre_completo }}</span></div>
        <div class="row"><label>Cédula</label><span>{{ $detalle->empleado?->cedula }}</span></div>
        <div class="row"><label>Cargo</label><span>{{ $detalle->empleado?->cargo?->nombre }}</span></div>
        <div class="row"><label>Tipo</label><span>{{ ucfirst($detalle->empleado?->tipo) }}</span></div>
    </div>

    <table>
        <thead>
            <tr><th>Concepto</th><th>Tipo</th><th class="tr">Monto (Bs.)</th></tr>
        </thead>
        <tbody>
        @foreach($detalle->conceptos_aplicados as $c)
        <tr>
            <td>{{ $c['nombre'] }}</td>
            <td><span style="color:{{ $c['tipo'] === 'asignacion' ? '#065f46' : '#991b1b' }};font-weight:600;">{{ ucfirst($c['tipo']) }}</span></td>
            <td class="tr" style="font-family:monospace;">{{ number_format($c['monto'], 2) }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>

    <div class="monto-box">
        <div>
            <div style="font-size:8px;color:#6b7280;">Total Asignaciones</div>
            <div style="font-size:13px;font-weight:800;color:#065f46;font-family:monospace;">Bs. {{ number_format($detalle->total_asignaciones, 2) }}</div>
        </div>
        <div style="text-align:center;">
            <div style="font-size:8px;color:#6b7280;">Deducciones</div>
            <div style="font-size:13px;font-weight:800;color:#991b1b;font-family:monospace;">Bs. {{ number_format($detalle->total_deducciones, 2) }}</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:8px;color:#6b7280;font-weight:700;">SUB-TOTAL NETO</div>
            <div style="font-size:14px;font-weight:800;color:#1e40af;font-family:monospace;">Bs. {{ number_format($detalle->neto, 2) }}</div>
        </div>
    </div>

    @if($nomina->retenciones->count() > 0)
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:8px 14px;margin-bottom:12px;">
        <div style="font-size:7px;color:#92400e;text-transform:uppercase;font-weight:bold;margin-bottom:4px;">Retenciones Fiscales (Institucionales)</div>
        <table style="margin:0;">
            @php $totalRetencionInd = 0; @endphp
            @foreach($nomina->retenciones as $ret)
                @php 
                    $montoRetInd = $detalle->neto * ($ret->porcentaje_aplicado / 100); 
                    $totalRetencionInd += $montoRetInd;
                @endphp
                <tr>
                    <td style="padding:2px 0;font-size:8px;color:#92400e;border-bottom:none;">{{ $ret->retencion->nombre ?? 'Retención' }} ({{ number_format($ret->porcentaje_aplicado, 2) }}%)</td>
                    <td class="tr" style="padding:2px 0;font-size:9px;color:#991b1b;font-family:monospace;font-weight:bold;border-bottom:none;">- Bs. {{ number_format($montoRetInd, 2) }}</td>
                </tr>
            @endforeach
        </table>
        <div style="text-align:right;border-top:1px solid #fcd34d;margin-top:6px;padding-top:6px;">
            <div style="font-size:8px;color:#92400e;font-weight:700;">TOTAL A DEPOSITAR</div>
            <div style="font-size:16px;font-weight:800;color:#1e40af;font-family:monospace;">Bs. {{ number_format($detalle->neto - $totalRetencionInd, 2) }}</div>
        </div>
    </div>
    @endif

    <table class="firmas-table">
        <tr>
            <td>
                <div class="firma-linea">{{ $detalle->empleado?->nombre_completo }}</div>
                <div style="font-size:7px;color:#6b7280;">Firma del Empleado / Conforme</div>
            </td>
            <td>
                <div class="firma-linea">{{ $nomina->aprobadoPor?->name ?? '_______________' }}</div>
                <div style="font-size:7px;color:#6b7280;">Recursos Humanos</div>
            </td>
        </tr>
    </table>

    <div class="footer">{{ config('app.name') }} · {{ $nomina->numero }} · Generado: {{ now()->format('d/m/Y H:i') }}</div>
</div>
</body>
</html>
