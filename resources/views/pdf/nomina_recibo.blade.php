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
    .firmas { display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:22px;padding-top:10px;border-top:1px solid #e5e7eb; }
    .firma-box { text-align:center; }
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
            <div style="font-size:8px;color:#6b7280;font-weight:700;">NETO A PAGAR</div>
            <div style="font-size:18px;font-weight:800;color:#1e40af;font-family:monospace;">Bs. {{ number_format($detalle->neto, 2) }}</div>
        </div>
    </div>

    <div class="firmas">
        <div class="firma-box"><div style="height:25px;"></div>
            <div style="border-top:1px solid #9ca3af;padding-top:3px;font-size:8px;font-weight:700;">{{ $detalle->empleado?->nombre_completo }}</div>
            <div style="font-size:7px;color:#6b7280;">Firma del Empleado / Conforme</div>
        </div>
        <div class="firma-box"><div style="height:25px;"></div>
            <div style="border-top:1px solid #9ca3af;padding-top:3px;font-size:8px;font-weight:700;">{{ $nomina->aprobadoPor?->name ?? '_______________' }}</div>
            <div style="font-size:7px;color:#6b7280;">Recursos Humanos</div>
        </div>
    </div>

    <div class="footer">{{ config('app.name') }} · {{ $nomina->numero }} · Generado: {{ now()->format('d/m/Y H:i') }}</div>
</div>
</body>
</html>
