<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a2e; background: #fff; }

  /* ── Encabezado institucional ── */
  .header { width: 100%; border-bottom: 3px solid #1F3864; padding-bottom: 10px; margin-bottom: 18px; }
  .header-inner { display: table; width: 100%; }
  .header-logo  { display: table-cell; width: 80px; vertical-align: middle; }
  .header-logo img { width: 70px; }
  .header-info  { display: table-cell; vertical-align: middle; padding-left: 14px; }
  .inst-name    { font-size: 13px; font-weight: bold; color: #1F3864; text-transform: uppercase; }
  .inst-sub     { font-size: 9px; color: #555; margin-top: 2px; }
  .header-right { display: table-cell; width: 180px; text-align: right; vertical-align: middle; }
  .doc-tipo     { font-size: 11px; font-weight: bold; color: #2E75B6; text-transform: uppercase; }
  .doc-numero   { font-size: 16px; font-weight: bold; color: #1F3864; }
  .doc-fecha    { font-size: 9px; color: #666; margin-top: 3px; }

  /* ── Tarjeta de estado ── */
  .estado-badge { display: inline-block; padding: 3px 10px; border-radius: 4px; font-weight: bold; font-size: 9px; text-transform: uppercase; }
  .estado-borrador  { background: #f0f0f0; color: #555; }
  .estado-aprobada,
  .estado-aprobado,
  .estado-calculada,
  .estado-registrado{ background: #e8f5e9; color: #1b5e20; }
  .estado-pagada,
  .estado-pagado    { background: #e3f2fd; color: #0d47a1; }
  .estado-anulada,
  .estado-anulado   { background: #ffebee; color: #b71c1c; }
  .estado-enviada   { background: #fff3e0; color: #e65100; }

  /* ── Sección de metadatos ── */
  .meta-grid { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
  .meta-grid td { padding: 5px 8px; font-size: 9.5px; border: 1px solid #dde; vertical-align: top; }
  .meta-label { font-weight: bold; color: #1F3864; background: #f5f7ff; width: 130px; }

  /* ── Tabla de detalle ── */
  .detail-table { width: 100%; border-collapse: collapse; margin: 14px 0; }
  .detail-table thead tr { background: #1F3864; color: white; }
  .detail-table thead th { padding: 7px 8px; font-size: 9px; text-align: left; }
  .detail-table tbody tr:nth-child(even) { background: #f7f9ff; }
  .detail-table tbody td { padding: 5px 8px; font-size: 9px; border-bottom: 1px solid #e8e8f0; }
  .text-right { text-align: right; }
  .text-center { text-align: center; }

  /* ── Totales ── */
  .totales { width: 100%; margin-top: 8px; }
  .totales-inner { float: right; width: 280px; }
  .totales-row { display: table; width: 100%; margin-bottom: 3px; }
  .totales-label { display: table-cell; font-size: 9.5px; color: #555; }
  .totales-val { display: table-cell; text-align: right; font-size: 9.5px; font-weight: bold; }
  .totales-total { border-top: 2px solid #1F3864; margin-top: 4px; padding-top: 4px; }
  .totales-total .totales-label,
  .totales-total .totales-val { font-size: 11px; color: #1F3864; font-weight: bold; }

  /* ── Firmas ── */
  .firmas { width: 100%; margin-top: 40px; }
  .firma-celda { display: inline-block; width: 30%; text-align: center; margin: 0 1%; }
  .firma-linea { border-top: 1px solid #333; padding-top: 5px; margin-top: 30px; font-size: 8.5px; color: #444; }

  /* ── Pie de página ── */
  .footer { position: fixed; bottom: 0; left: 0; right: 0; border-top: 1px solid #ccd; font-size: 8px; color: #888; padding: 5px 0; text-align: center; }

  /* ── Sección de título interno ── */
  .section-title { font-size: 10px; font-weight: bold; color: #2E75B6; border-bottom: 1px solid #2E75B6; margin: 14px 0 8px; padding-bottom: 3px; text-transform: uppercase; letter-spacing: 0.5px; }

  /* ── Watermark estado ── */
  .watermark { position: fixed; top: 35%; left: 10%; width: 80%; text-align: center; font-size: 72px; font-weight: bold; opacity: 0.05; color: #1F3864; transform: rotate(-30deg); z-index: -1; }

  /* ── Cuadros de información ── */
  .info-box { background: #f5f7ff; border-left: 4px solid #2E75B6; padding: 8px 12px; margin: 10px 0; font-size: 9px; }
  .info-box strong { color: #1F3864; }

  .clearfix::after { content: ''; display: table; clear: both; }

  /* ── Recibo estilo ticket ──  */
  .recibo-header { text-align: center; border-bottom: 2px dashed #333; padding-bottom: 10px; margin-bottom: 10px; }
  .recibo-numero { font-size: 22px; font-weight: bold; color: #1F3864; }
  .recibo-row { display: table; width: 100%; padding: 4px 0; border-bottom: 1px dotted #ddd; }
  .recibo-key { display: table-cell; font-size: 9px; color: #555; width: 45%; }
  .recibo-val { display: table-cell; font-size: 9px; font-weight: bold; text-align: right; }
  .recibo-total { font-size: 16px; font-weight: bold; text-align: center; padding: 12px; margin-top: 10px; border: 2px solid #1F3864; color: #1F3864; }
</style>
</head>
<body>

<div class="watermark">@yield('watermark')</div>

<!-- ENCABEZADO INSTITUCIONAL -->
<div class="header">
  <div class="header-inner">
    <div class="header-info">
      <div class="inst-name">Contraloría del Estado Portuguesa</div>
      <div class="inst-sub">Sistema de Administración Integrado (SIA) · República Bolivariana de Venezuela</div>
    </div>
    <div class="header-right">
      <div class="doc-tipo">@yield('doc-tipo')</div>
      <div class="doc-numero">@yield('doc-numero')</div>
      <div class="doc-fecha">Emitido: {{ now()->translatedFormat('d/m/Y H:i') }}</div>
    </div>
  </div>
</div>

<!-- CONTENIDO -->
@yield('content')

<!-- PIE DE PÁGINA -->
<div class="footer">
  SIA · Contraloría del Estado Portuguesa · Documento generado el {{ now()->translatedFormat('d \d\e F \d\e Y \a \l\a\s H:i') }} · Confidencial
</div>

</body>
</html>
