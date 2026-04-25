<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a2e; }

  .recibo-wrap { max-width: 420px; margin: 0 auto; padding: 16px; }

  /* Encabezado */
  .recibo-header { text-align: center; border-bottom: 2px dashed #1F3864; padding-bottom: 12px; margin-bottom: 12px; }
  .inst-name { font-size: 11px; font-weight: bold; color: #1F3864; text-transform: uppercase; }
  .inst-sub  { font-size: 8px; color: #666; margin: 2px 0 8px; }
  .recibo-tipo { font-size: 9px; font-weight: bold; color: #555; text-transform: uppercase; letter-spacing: 1px; }
  .recibo-num  { font-size: 24px; font-weight: bold; color: #1F3864; margin: 4px 0; }
  .recibo-fecha { font-size: 9px; color: #777; }

  /* Filas */
  .row { display: table; width: 100%; padding: 5px 0; border-bottom: 1px dotted #ddd; }
  .row-key { display: table-cell; font-size: 9px; color: #666; width: 45%; vertical-align: top; }
  .row-val { display: table-cell; font-size: 9px; font-weight: bold; color: #222; text-align: right; }

  .section-sep { border-top: 1px solid #ccc; margin: 10px 0; }

  /* Total */
  .total-box { margin: 12px 0; border: 2px solid #1F3864; padding: 10px; text-align: center; }
  .total-label { font-size: 9px; text-transform: uppercase; color: #555; }
  .total-amount { font-size: 20px; font-weight: bold; color: #1F3864; }

  /* Estado */
  .estado { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
  .estado-registrado { background: #e8f5e9; color: #1b5e20; }
  .estado-anulado    { background: #ffebee; color: #b71c1c; }

  /* Firma */
  .firma-area { margin-top: 24px; border-top: 1px solid #333; padding-top: 5px; text-align: center; font-size: 8px; color: #555; }

  /* Pie */
  .recibo-footer { margin-top: 14px; border-top: 1px dashed #aaa; padding-top: 8px; text-align: center; font-size: 7.5px; color: #999; }

  .anulado-stripe { background: repeating-linear-gradient(45deg, #fff5f5, #fff5f5 10px, #ffebee 10px, #ffebee 20px); }
</style>
</head>
<body>
<div class="recibo-wrap {{ $ingreso->estado === 'anulado' ? 'anulado-stripe' : '' }}">

  <div class="recibo-header">
    <div class="inst-name">Contraloría del Estado Portuguesa</div>
    <div class="inst-sub">Unidad de Control de Ingresos</div>
    <div class="inst-sub">{{ $ingreso->caja?->unidadEjecutora?->nombre }}</div>
    <div class="recibo-tipo">Recibo de Ingreso</div>
    <div class="recibo-num">N° {{ $ingreso->numero_recibo }}</div>
    <div class="recibo-fecha">{{ \Carbon\Carbon::parse($ingreso->fecha)->translatedFormat('d \d\e F \d\e Y') }}</div>
    @if($ingreso->estado === 'anulado')
      <br><span class="estado estado-anulado">⚠ Anulado</span>
    @else
      <span class="estado estado-registrado">✓ Válido</span>
    @endif
  </div>

  <!-- Datos del pagador -->
  <div class="row">
    <span class="row-key">Pagador</span>
    <span class="row-val">{{ $ingreso->pagador_nombre ?: 'No especificado' }}</span>
  </div>
  @if($ingreso->pagador_rif)
  <div class="row">
    <span class="row-key">RIF / CI</span>
    <span class="row-val">{{ $ingreso->pagador_rif }}</span>
  </div>
  @endif

  <div class="section-sep"></div>

  <!-- Concepto -->
  <div class="row">
    <span class="row-key">Concepto</span>
    <span class="row-val">{{ $ingreso->concepto->nombre ?? '—' }}</span>
  </div>
  <div class="row">
    <span class="row-key">Forma de Pago</span>
    <span class="row-val">{{ ucfirst($ingreso->forma_pago) }}</span>
  </div>
  @if($ingreso->referencia_bancaria)
  <div class="row">
    <span class="row-key">Referencia</span>
    <span class="row-val">{{ $ingreso->referencia_bancaria }}</span>
  </div>
  @endif
  <div class="row">
    <span class="row-key">Caja</span>
    <span class="row-val">{{ $ingreso->caja->nombre ?? '—' }}</span>
  </div>
  <div class="row">
    <span class="row-key">Atendido por</span>
    <span class="row-val">{{ $ingreso->creadoPor->name ?? '—' }}</span>
  </div>

  <!-- Total -->
  <div class="total-box">
    <div class="total-label">Total Cobrado</div>
    <div class="total-amount">Bs. {{ number_format($ingreso->monto, 2) }}</div>
  </div>

  @if($ingreso->motivo_anulacion)
  <div style="background:#ffebee; border:1px solid #ef9a9a; padding:6px; margin:8px 0; font-size:8px;">
    <strong style="color:#b71c1c">Motivo de anulación:</strong><br>{{ $ingreso->motivo_anulacion }}
  </div>
  @endif

  <div class="firma-area">
    _____________________________<br>
    Firma del Cajero Recaudador<br>
    {{ $ingreso->creadoPor->name ?? '' }}
  </div>

  <div class="recibo-footer">
    Emitido el {{ now()->translatedFormat('d/m/Y') . ' a las ' . now()->format('H:i') }} ·
    SIA — Contraloría del Estado Portuguesa · Documento válido solo con sello original
  </div>

</div>
</body>
</html>
