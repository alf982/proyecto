<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0;padding:0;box-sizing:border-box; }
    body { font-family:'DejaVu Sans',sans-serif;font-size:8.5px;color:#1a1a2e; }
    .header { background:#1e3a5f;color:#fff;padding:12px 24px;display:flex;justify-content:space-between;align-items:center; }
    .header h1 { font-size:14px;font-weight:700; }
    .body { padding:14px 24px; }
    .resumen { display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:14px; }
    .kpi { background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:8px 12px;text-align:center; }
    .kpi-label { font-size:7px;color:#94a3b8;text-transform:uppercase;margin-bottom:3px; }
    .kpi-value { font-size:14px;font-weight:800;font-family:monospace; }
    table { width:100%;border-collapse:collapse; }
    th { background:#1e3a5f;color:#fff;font-size:7.5px;padding:5px 6px;text-align:left;font-weight:600; }
    td { padding:4px 6px;font-size:8px;border-bottom:1px solid #f1f5f9; }
    tr:nth-child(even) td { background:#f8fafc; }
    .tr { text-align:right; }
    .total-row td { font-weight:700;background:#eff6ff !important;border-top:2px solid #bfdbfe;font-size:9px; }
    .footer { text-align:center;font-size:7px;color:#9ca3af;margin-top:12px;padding-top:6px;border-top:1px solid #e5e7eb; }
</style>
</head>
<body>
<div class="header">
    <div>
        <div style="font-size:7px;opacity:.7;margin-bottom:2px;">RESUMEN DE NÓMINA</div>
        <h1>{{ strtoupper($nomina->tipo_nomina) }} — {{ $nomina->numero }}</h1>
        <div style="font-size:8px;opacity:.7;">Período: {{ $nomina->periodo_inicio?->format('d/m/Y') }} al {{ $nomina->periodo_fin?->format('d/m/Y') }}</div>
    </div>
    <div style="text-align:right;font-size:8px;opacity:.8;">
        Ejercicio {{ $nomina->ejercicioFiscal?->anio }}<br>
        Aprobada: {{ $nomina->fecha_aprobacion?->format('d/m/Y') ?? 'Pendiente' }}
    </div>
</div>

<div class="body">
    <div class="resumen">
        <div class="kpi"><div class="kpi-label">Empleados</div><div class="kpi-value">{{ $nomina->detalles->count() }}</div></div>
        <div class="kpi"><div class="kpi-label">Total Asignaciones</div><div class="kpi-value" style="font-size:11px;color:#065f46;">Bs. {{ number_format($nomina->total_asignaciones, 2) }}</div></div>
        <div class="kpi"><div class="kpi-label">Total Deducciones</div><div class="kpi-value" style="font-size:11px;color:#991b1b;">Bs. {{ number_format($nomina->total_deducciones, 2) }}</div></div>
        <div class="kpi"><div class="kpi-label">NETO A PAGAR</div><div class="kpi-value" style="font-size:11px;color:#1e40af;">Bs. {{ number_format($nomina->total_neto, 2) }}</div></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th><th>Empleado</th><th>Cédula</th><th>Cargo</th>
                <th class="tr">Salario</th><th class="tr">Asignaciones</th><th class="tr">Deducciones</th><th class="tr">Neto</th>
            </tr>
        </thead>
        <tbody>
        @foreach($nomina->detalles as $i => $d)
        <tr>
            <td style="color:#94a3b8;">{{ $i+1 }}</td>
            <td style="font-weight:600;">{{ $d->empleado?->nombre_completo }}</td>
            <td style="font-family:monospace;">{{ $d->empleado?->cedula }}</td>
            <td>{{ Str::limit($d->empleado?->cargo?->nombre, 20) }}</td>
            <td class="tr" style="font-family:monospace;">{{ number_format($d->salario_base, 2) }}</td>
            <td class="tr" style="font-family:monospace;color:#065f46;">{{ number_format($d->total_asignaciones, 2) }}</td>
            <td class="tr" style="font-family:monospace;color:#991b1b;">{{ number_format($d->total_deducciones, 2) }}</td>
            <td class="tr" style="font-family:monospace;font-weight:700;color:#1e40af;">{{ number_format($d->neto, 2) }}</td>
        </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="tr">TOTALES:</td>
                <td class="tr">{{ number_format($nomina->total_asignaciones, 2) }}</td>
                <td class="tr">{{ number_format($nomina->total_deducciones, 2) }}</td>
                <td class="tr">{{ number_format($nomina->total_neto, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:30px;margin-top:24px;">
        <div style="text-align:center;"><div style="height:25px;"></div>
            <div style="border-top:1px solid #94a3b8;padding-top:3px;font-size:8px;font-weight:700;">{{ $nomina->creadoPor?->name ?? '_______________' }}</div>
            <div style="font-size:7px;color:#6b7280;">Elaborado por</div>
        </div>
        <div style="text-align:center;"><div style="height:25px;"></div>
            <div style="border-top:1px solid #94a3b8;padding-top:3px;font-size:8px;font-weight:700;">{{ $nomina->aprobadoPor?->name ?? '_______________' }}</div>
            <div style="font-size:7px;color:#6b7280;">Aprobado por</div>
        </div>
    </div>

    <div class="footer">{{ config('app.name') }} · {{ $nomina->numero }} · Generado: {{ now()->format('d/m/Y H:i') }}</div>
</div>
</body>
</html>
