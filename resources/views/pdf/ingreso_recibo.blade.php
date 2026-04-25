<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0;padding:0;box-sizing:border-box; }
    body { font-family:'DejaVu Sans',sans-serif;font-size:9px;color:#1a1a2e; }
    .header { background:#1e3a5f;color:#fff;padding:12px 20px; }
    .header h1 { font-size:13px;font-weight:700; }
    .code-box { font-family:'DejaVu Sans Mono',monospace;font-size:22px;font-weight:800;color:#7dd3fc;text-align:right;float:right;margin-top:-24px; }
    .body { padding:14px 20px; }
    .info-box { display:grid;grid-template-columns:1fr 1fr;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;margin-bottom:12px; }
    .fl { font-size:7.5px;color:#94a3b8;text-transform:uppercase;margin-bottom:1px; }
    .fv { font-size:9.5px;font-weight:600; }
    .monto-box { background:#f0fdf4;border:2px solid #86efac;border-radius:8px;padding:12px 20px;text-align:center;margin:12px 0; }
    .m-label { font-size:8px;color:#6b7280;margin-bottom:3px; }
    .m-value { font-size:24px;font-weight:800;color:#15803d;font-family:monospace; }
    .detail { display:flex;justify-content:space-between;font-size:8px;color:#6b7280;margin-top:4px; }
    .firmas { display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:22px; }
    .firma-box { text-align:center; }
    .footer { text-align:center;font-size:7px;color:#9ca3af;margin-top:12px;padding-top:6px;border-top:1px solid #e5e7eb; }
    .qr-area { text-align:center;margin:8px 0;font-size:8px;color:#94a3b8; }
    .cut { border-top:1px dashed #d1d5db;margin:14px 0; }
</style>
</head>
<body>
<div class="header">
    <div style="font-size:7px;opacity:.7;margin-bottom:2px;">COMPROBANTE DE INGRESO</div>
    <h1>RECIBO OFICIAL DE INGRESO</h1>
    <div class="code-box">{{ $ingreso->numero_recibo }}</div>
    <div style="font-size:8px;opacity:.7;">{{ config('app.name') }}</div>
</div>

<div class="body">
    <div class="info-box">
        <div>
            <div class="fl">Fecha</div>
            <div class="fv">{{ $ingreso->fecha?->format('d/m/Y') }}</div>
        </div>
        <div>
            <div class="fl">Forma de Pago</div>
            <div class="fv">{{ ucfirst($ingreso->forma_pago) }}</div>
        </div>
        <div>
            <div class="fl">Caja</div>
            <div class="fv">{{ $ingreso->caja?->nombre }}</div>
        </div>
        <div>
            <div class="fl">Concepto</div>
            <div class="fv">{{ $ingreso->conceptoIngreso?->nombre }}</div>
        </div>
        @if($ingreso->pagador_nombre)
        <div>
            <div class="fl">Pagador / Contribuyente</div>
            <div class="fv">{{ $ingreso->pagador_nombre }}</div>
        </div>
        @endif
        @if($ingreso->pagador_rif)
        <div>
            <div class="fl">RIF / Cédula</div>
            <div class="fv" style="font-family:monospace;">{{ $ingreso->pagador_rif }}</div>
        </div>
        @endif
        @if($ingreso->referencia_bancaria)
        <div style="grid-column:span 2;">
            <div class="fl">Referencia Bancaria</div>
            <div class="fv" style="font-family:monospace;">{{ $ingreso->referencia_bancaria }}</div>
        </div>
        @endif
    </div>

    <div class="monto-box">
        <div class="m-label">MONTO RECIBIDO</div>
        <div class="m-value">Bs. {{ number_format($ingreso->monto, 2) }}</div>
        <div class="detail">
            <span>Recibido por: <strong>{{ $ingreso->creadoPor?->name }}</strong></span>
            <span>N° Recibo: <strong>{{ $ingreso->numero_recibo }}</strong></span>
        </div>
    </div>

    <div class="firmas">
        <div class="firma-box">
            <div style="height:28px;"></div>
            <div style="border-top:1px solid #94a3b8;padding-top:3px;">
                <div style="font-size:8px;font-weight:700;">{{ $ingreso->creadoPor?->name ?? '_______________' }}</div>
                <div style="font-size:7px;color:#6b7280;">Cajero / Receptor</div>
            </div>
        </div>
        <div class="firma-box">
            <div style="height:28px;"></div>
            <div style="border-top:1px solid #94a3b8;padding-top:3px;">
                <div style="font-size:8px;font-weight:700;">{{ $ingreso->pagador_nombre ?? '_______________' }}</div>
                <div style="font-size:7px;color:#6b7280;">Firma del Contribuyente</div>
            </div>
        </div>
    </div>

    <div class="cut"></div>
    <div style="font-size:8px;color:#6b7280;text-align:center;font-style:italic;">
        Conserve este comprobante. Es el único documento válido para reclamos.
    </div>

    <div class="footer">{{ config('app.name') }} · {{ $ingreso->numero_recibo }} · {{ $ingreso->fecha?->format('d/m/Y') }}</div>
</div>
</body>
</html>
