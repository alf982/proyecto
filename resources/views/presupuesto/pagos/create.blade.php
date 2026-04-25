@extends('layouts.app')
@section('title','Nuevo Pago')
@section('breadcrumb')
    <a href="{{ route('presupuesto.pagos.index') }}">Pagos</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Nuevo Pago</span>
@endsection
@section('content')
<div class="page-header fade-up">
    <h1 class="page-title">Registrar Pago</h1>
    <p class="page-subtitle">Seleccione la causación aprobada que desea pagar</p>
</div>

<form method="POST" action="{{ route('presupuesto.pagos.store') }}" style="max-width:860px;">
@csrf

{{-- ── 1. CAUSACIÓN ─────────────────────────────────────────── --}}
<div class="card fade-up">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-file-invoice" style="color:var(--accent);margin-right:6px;"></i>
            Causación a Pagar <span style="color:var(--accent-danger);">*</span>
        </div>
    </div>
    <div class="card-body">
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Seleccione la causación aprobada</label>
            <select name="causacion_id" id="causacion_id" class="form-control" required onchange="cargarCausacion(this)">
                <option value="">-- Seleccione una causación --</option>
                @foreach($causaciones as $c)
                @php
                    $bModel    = $c->compromiso?->beneficiarioModel ?? null;
                    $bancoBen  = $bModel ? ($bModel->banco_nombre ?? '') : '';
                    $cuentaBen = $bModel ? ($bModel->banco_cuenta ?? '') : '';
                    $tipoCuenta = $bModel ? ($bModel->banco_tipo_cuenta ?? '') : '';
                @endphp
                <option value="{{ $c->id }}"
                    data-monto="{{ $c->monto_causado }}"
                    data-sin-iva="{{ $c->monto_sin_iva ?? '' }}"
                    data-alicuota-iva="{{ $c->alicuota_iva ?? '' }}"
                    data-beneficiario="{{ $c->beneficiario }}"
                    data-rif="{{ $c->rif_beneficiario }}"
                    data-partida="{{ $c->partida?->codigo ?? '' }}"
                    data-partida-desc="{{ $c->partida?->descripcion ?? '' }}"
                    data-saldo="{{ $c->partida?->saldo_actual ?? 0 }}"
                    data-concepto="{{ $c->concepto }}"
                    data-unidad="{{ $c->unidadEjecutora?->nombre ?? '' }}"
                    data-banco="{{ $bancoBen }}"
                    data-cuenta="{{ $cuentaBen }}"
                    data-tipo-cuenta="{{ $tipoCuenta }}"
                    {{ (old('causacion_id') == $c->id || ($causacion && $causacion->id == $c->id)) ? 'selected' : '' }}>
                    {{ $c->numero }} — {{ $c->beneficiario }} — Bs. {{ number_format($c->monto_causado,2) }}
                </option>
                @endforeach
            </select>
            @error('causacion_id')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        {{-- Panel info causación --}}
        <div id="panel-causacion" style="display:none;margin-top:14px;padding:14px 16px;background:rgba(79,142,247,0.06);border:1px solid rgba(79,142,247,0.2);border-radius:12px;">
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;font-size:13px;">
                <div>
                    <div style="font-size:10px;color:var(--text-secondary);font-weight:600;text-transform:uppercase;margin-bottom:3px;">Beneficiario</div>
                    <div id="info-beneficiario" style="font-weight:700;"></div>
                    <div id="info-rif" style="font-size:11px;color:var(--text-secondary);"></div>
                </div>
                <div>
                    <div style="font-size:10px;color:var(--text-secondary);font-weight:600;text-transform:uppercase;margin-bottom:3px;">Partida</div>
                    <div id="info-partida" style="font-family:monospace;color:var(--accent);font-weight:700;"></div>
                    <div id="info-partida-desc" style="font-size:11px;color:var(--text-secondary);margin-top:1px;"></div>
                </div>
                <div>
                    <div style="font-size:10px;color:var(--text-secondary);font-weight:600;text-transform:uppercase;margin-bottom:3px;">Saldo Restante Partida</div>
                    <div id="info-saldo" style="font-weight:800;font-size:16px;"></div>
                </div>
                <div id="info-banco-wrap" style="display:none;grid-column:span 3;padding-top:10px;border-top:1px solid rgba(79,142,247,0.15);">
                    <div style="font-size:10px;color:var(--text-secondary);font-weight:600;text-transform:uppercase;margin-bottom:5px;">Datos Bancarios del Beneficiario</div>
                    <div style="display:flex;gap:20px;flex-wrap:wrap;">
                        <div><span style="font-size:11px;color:var(--text-secondary);">Banco:</span> <span id="info-banco" style="font-weight:600;font-size:13px;"></span></div>
                        <div><span style="font-size:11px;color:var(--text-secondary);">Cuenta:</span> <span id="info-cuenta" style="font-family:monospace;font-weight:600;font-size:13px;color:var(--accent-3);"></span></div>
                        <div><span style="font-size:11px;color:var(--text-secondary);">Tipo:</span> <span id="info-tipo-cuenta" style="font-weight:600;font-size:13px;"></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── 2. DESGLOSE DE MONTOS ────────────────────────────────── --}}
<div class="card fade-up" style="margin-top:18px;animation-delay:.04s;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-receipt" style="color:var(--accent-warn);margin-right:6px;"></i>
            Desglose de la Factura
        </div>
    </div>
    <div class="card-body">

        {{-- Campos ocultos necesarios para el backend --}}
        <input type="hidden" name="monto_sin_iva" id="campo-monto-sin-iva" value="{{ old('monto_sin_iva') }}">

        {{-- Tarjetas de monto --}}
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:16px;">
            {{-- Monto Neto --}}
            <div style="padding:16px;background:rgba(79,142,247,0.06);border:1px solid rgba(79,142,247,0.18);border-radius:12px;text-align:center;">
                <div style="font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:8px;">
                    <i class="fa-solid fa-tag" style="margin-right:4px;"></i>Monto Neto (Base)
                </div>
                <div id="dsg-sin-iva" style="font-size:22px;font-weight:800;font-family:monospace;color:var(--accent);">
                    Bs. —
                </div>
                <div style="font-size:10px;color:var(--text-secondary);margin-top:4px;">Sin IVA · Base para ISLR</div>
            </div>
            {{-- IVA --}}
            <div style="padding:16px;background:rgba(247,185,79,0.07);border:1px solid rgba(247,185,79,0.25);border-radius:12px;text-align:center;">
                <div style="font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:8px;">
                    <i class="fa-solid fa-percent" style="margin-right:4px;"></i>IVA del Proveedor
                </div>
                <div id="dsg-iva" style="font-size:22px;font-weight:800;font-family:monospace;color:var(--accent-warn);">
                    Bs. —
                </div>
                <div style="font-size:10px;color:var(--text-secondary);margin-top:4px;" id="dsg-alicuota-lbl">— · Base para Ret. IVA</div>
            </div>
            {{-- Total --}}
            <div style="padding:16px;background:rgba(34,211,166,0.07);border:1px solid rgba(34,211,166,0.25);border-radius:12px;text-align:center;">
                <div style="font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:8px;">
                    <i class="fa-solid fa-file-invoice-dollar" style="margin-right:4px;"></i>Total Factura (con IVA)
                </div>
                <div id="dsg-total" style="font-size:22px;font-weight:800;font-family:monospace;color:var(--accent-3);">
                    Bs. —
                </div>
                <div style="font-size:10px;color:var(--text-secondary);margin-top:4px;">Monto a registrar en el pago</div>
            </div>
        </div>

        {{-- Campo monto (editable) --}}
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">
                Monto a Pagar (Bs.) *
                <span style="font-size:11px;font-weight:400;color:var(--text-secondary);margin-left:6px;">
                    Se llena automáticamente · ajuste manualmente si es necesario
                </span>
            </label>
            <div style="position:relative;">
                <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:14px;font-weight:700;color:var(--text-secondary);">Bs.</span>
                <input type="number" id="monto_pagado" name="monto_pagado" class="form-control"
                       value="{{ old('monto_pagado') }}" step="0.01" min="0.01" required
                       placeholder="0.00" style="padding-left:40px;font-size:18px;font-weight:700;"
                       oninput="verificarMonto(); calcularRetenciones()">
            </div>
            <div id="alerta-monto" style="display:none;margin-top:6px;font-size:12px;color:var(--accent-danger);">
                <i class="fa-solid fa-triangle-exclamation"></i> Supera el saldo disponible de la partida
            </div>
            @error('monto_pagado')<div class="form-error">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

{{-- ── 3. RETENCIONES ──────────────────────────────────────── --}}
@include('components.retenciones-selector', [
    'modulo'         => 'pago',
    'inputMonto'     => 'monto_pagado',   // total con IVA → para Ret. IVA
    'inputMontoBase' => 'monto_sin_iva',  // base sin IVA  → para ISLR etc.
])

{{-- ── 4. RESUMEN NETO ─────────────────────────────────────── --}}
<div class="card fade-up" style="margin-top:18px;animation-delay:.1s;border:1px solid rgba(34,211,166,0.25);">
    <div class="card-body" style="padding:18px 20px;">
        <div style="font-size:11px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:14px;">
            <i class="fa-solid fa-calculator" style="color:var(--accent-3);margin-right:5px;"></i>Resumen del Pago
        </div>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px;">
                <span style="color:var(--text-secondary);">Total factura (con IVA):</span>
                <strong id="res-bruto" style="font-family:monospace;">Bs. 0.00</strong>
            </div>
            <div id="res-ret-row" style="display:none;justify-content:space-between;align-items:center;font-size:13px;">
                <span style="color:var(--accent-warn);">
                    <i class="fa-solid fa-minus" style="font-size:10px;margin-right:4px;"></i>Retenciones (SENIAT):
                </span>
                <strong id="res-ret" style="font-family:monospace;color:var(--accent-warn);">Bs. 0.00</strong>
            </div>
            <div style="height:1px;background:var(--border);"></div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:13px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;">A transferir al proveedor:</span>
                <strong id="res-neto" style="font-size:24px;font-family:monospace;color:var(--accent-3);">Bs. 0.00</strong>
            </div>
        </div>
        <div style="margin-top:12px;padding-top:10px;border-top:1px solid var(--border);font-size:10px;color:var(--text-secondary);line-height:1.6;">
            <i class="fa-solid fa-circle-info" style="color:var(--accent-warn);margin-right:4px;"></i>
            Las retenciones son descontadas del monto a transferir al proveedor y pagadas por el ente directamente al SENIAT.
        </div>
    </div>
</div>

{{-- ── 5. DATOS DEL PAGO ───────────────────────────────────── --}}
<div class="card fade-up" style="margin-top:18px;animation-delay:.12s;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-money-bill-wave" style="color:var(--accent-3);margin-right:6px;"></i>
            Datos del Pago
        </div>
    </div>
    <div class="card-body">
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Tipo de Pago *</label>
                <select name="tipo_pago" class="form-control" required>
                    <option value="transferencia" {{ old('tipo_pago','transferencia')==='transferencia'?'selected':'' }}>Transferencia</option>
                    <option value="cheque"        {{ old('tipo_pago')==='cheque'?'selected':'' }}>Cheque</option>
                    <option value="efectivo"      {{ old('tipo_pago')==='efectivo'?'selected':'' }}>Efectivo</option>
                    <option value="otro"          {{ old('tipo_pago')==='otro'?'selected':'' }}>Otro</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Fecha de Pago *</label>
                <input type="date" name="fecha_pago" class="form-control" value="{{ old('fecha_pago', date('Y-m-d')) }}" required>
                @error('fecha_pago')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">N° Referencia / Cheque</label>
                <input type="text" name="numero_referencia" class="form-control" value="{{ old('numero_referencia') }}" placeholder="Número de transacción">
            </div>
            <div class="form-group">
                <label class="form-label">Banco <span id="lbl-banco-auto" style="font-size:11px;color:var(--accent-3);font-weight:400;margin-left:6px;"></span></label>
                <input type="text" name="banco" id="campo-banco" class="form-control" value="{{ old('banco') }}" placeholder="Ej: Banco de Venezuela">
            </div>
            <div class="form-group" style="grid-column:span 2;">
                <label class="form-label">N° Cuenta Bancaria <span id="lbl-cuenta-auto" style="font-size:11px;color:var(--accent-3);font-weight:400;margin-left:6px;"></span></label>
                <input type="text" name="cuenta_bancaria" id="campo-cuenta" class="form-control" value="{{ old('cuenta_bancaria') }}" placeholder="Ej: 0102-0000-00-0000000000">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Concepto *</label>
            <textarea id="campo-concepto" name="concepto" class="form-control" rows="2" required maxlength="500">{{ old('concepto') }}</textarea>
            @error('concepto')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones') }}</textarea>
        </div>
    </div>
</div>

{{-- ── 6. BOTONES ──────────────────────────────────────────── --}}
<div class="card fade-up" style="margin-top:18px;animation-delay:.14s;">
    <div class="card-body" style="display:flex;gap:12px;align-items:center;">
        <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;padding:14px;">
            <i class="fa-solid fa-money-bill-wave"></i> Registrar Pago
        </button>
        <a href="{{ route('presupuesto.pagos.index') }}" class="btn btn-outline" style="flex:1;justify-content:center;padding:14px;">
            Cancelar
        </a>
    </div>
</div>

</form>

@push('scripts')
<script>
let saldoPartida = 0;

function cargarCausacion(sel) {
    const opt   = sel.options[sel.selectedIndex];
    const panel = document.getElementById('panel-causacion');
    if (!sel.value) { panel.style.display = 'none'; return; }

    panel.style.display = 'block';
    document.getElementById('info-beneficiario').textContent = opt.dataset.beneficiario || '—';
    document.getElementById('info-rif').textContent          = opt.dataset.rif ? 'RIF: ' + opt.dataset.rif : '';
    document.getElementById('info-partida').textContent      = opt.dataset.partida || '—';
    document.getElementById('info-partida-desc').textContent = opt.dataset.partidaDesc || '';

    saldoPartida = parseFloat(opt.dataset.saldo || 0);
    const saldoEl = document.getElementById('info-saldo');
    saldoEl.textContent = 'Bs. ' + saldoPartida.toLocaleString('es-VE',{minimumFractionDigits:2});
    saldoEl.style.color = saldoPartida > 0 ? 'var(--accent-3)' : 'var(--accent-danger)';

    // ── Desglose IVA ─────────────────────────────────────────
    const sinIva   = parseFloat(opt.dataset.sinIva || 0);
    const alicuota = parseFloat(opt.dataset.alicuotaIva || 0);
    const campoSinIva = document.getElementById('campo-monto-sin-iva');
    const fmt = v => 'Bs. ' + v.toLocaleString('es-VE',{minimumFractionDigits:2,maximumFractionDigits:2});

    if (sinIva > 0) {
        const montoIva = Math.round(sinIva * (alicuota / 100) * 100) / 100;
        const total    = Math.round((sinIva + montoIva) * 100) / 100;
        document.getElementById('dsg-sin-iva').textContent    = fmt(sinIva);
        document.getElementById('dsg-iva').textContent        = fmt(montoIva);
        document.getElementById('dsg-alicuota-lbl').textContent = 'IVA ' + alicuota + '% · Base para Ret. IVA';
        document.getElementById('dsg-total').textContent      = fmt(total);
        if (campoSinIva) campoSinIva.value = sinIva.toFixed(2);
    } else {
        const total = parseFloat(opt.dataset.monto || 0);
        document.getElementById('dsg-sin-iva').textContent    = 'Bs. —';
        document.getElementById('dsg-iva').textContent        = 'Bs. —';
        document.getElementById('dsg-alicuota-lbl').textContent = '— · Base para Ret. IVA';
        document.getElementById('dsg-total').textContent      = fmt(total);
        if (campoSinIva) campoSinIva.value = '';
    }

    // ── Datos bancarios ─────────────────────────────────────
    const banco   = opt.dataset.banco   || '';
    const cuenta  = opt.dataset.cuenta  || '';
    const tipoCta = opt.dataset.tipoCuenta || '';
    const bancoBancoWrap = document.getElementById('info-banco-wrap');

    if (banco || cuenta) {
        document.getElementById('info-banco').textContent       = banco || '—';
        document.getElementById('info-cuenta').textContent      = cuenta || '—';
        document.getElementById('info-tipo-cuenta').textContent = tipoCta || '—';
        bancoBancoWrap.style.display = 'block';
        const campoBanco  = document.getElementById('campo-banco');
        const campoCuenta = document.getElementById('campo-cuenta');
        if (!campoBanco.value)  { campoBanco.value  = banco;  document.getElementById('lbl-banco-auto').textContent  = '(desde catálogo)'; }
        if (!campoCuenta.value) { campoCuenta.value = cuenta; document.getElementById('lbl-cuenta-auto').textContent = '(desde catálogo)'; }
    } else {
        bancoBancoWrap.style.display = 'none';
        document.getElementById('lbl-banco-auto').textContent  = '';
        document.getElementById('lbl-cuenta-auto').textContent = '';
    }

    // Pre-llenar monto y concepto
    const montoEl = document.getElementById('monto_pagado');
    if (!montoEl.value) montoEl.value = parseFloat(opt.dataset.monto||0).toFixed(2);

    const conceptoEl = document.getElementById('campo-concepto');
    if (!conceptoEl.value && opt.dataset.concepto) conceptoEl.value = opt.dataset.concepto;

    verificarMonto();
    if (typeof calcularRetenciones === 'function') calcularRetenciones();
}

function verificarMonto() {
    const monto = parseFloat(document.getElementById('monto_pagado').value || 0);
    document.getElementById('alerta-monto').style.display =
        (saldoPartida > 0 && monto > saldoPartida) ? 'block' : 'none';
    actualizarResumen();
}

function actualizarResumen() {
    const bruto = parseFloat(document.getElementById('monto_pagado').value || 0);
    const retTotal = document.getElementById('ret-total');
    let retenciones = 0;
    if (retTotal) {
        // ret-total usa toFixed(2) → formato US con punto decimal
        // Parsear directo sin manipular separadores de miles
        const raw = retTotal.getAttribute('data-valor') || retTotal.textContent;
        retenciones = parseFloat(raw.replace('Bs. ', '').replace(',', '.')) || 0;
    }
    const neto = Math.max(0, bruto - retenciones);
    const fmt  = v => 'Bs. ' + v.toLocaleString('es-VE', { minimumFractionDigits:2, maximumFractionDigits:2 });

    const elBruto  = document.getElementById('res-bruto');
    const elRet    = document.getElementById('res-ret');
    const elRetRow = document.getElementById('res-ret-row');
    const elNeto   = document.getElementById('res-neto');

    if (elBruto)  elBruto.textContent  = fmt(bruto);
    if (elRet)    elRet.textContent    = fmt(retenciones);
    if (elRetRow) elRetRow.style.display = retenciones > 0 ? 'flex' : 'none';
    if (elNeto)   elNeto.textContent   = fmt(neto);
}

// Hook: actualizar resumen cuando cambien las retenciones
const _calcRetOrig = window.calcularRetenciones;
window.calcularRetenciones = function() {
    if (typeof _calcRetOrig === 'function') _calcRetOrig();
    actualizarResumen();
};

// Asegurar que el evento input del monto también llame la versión envuelta
document.addEventListener('DOMContentLoaded', function() {
    const montoEl = document.getElementById('monto_pagado');
    if (montoEl) montoEl.addEventListener('input', actualizarResumen);
});

window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('causacion_id');
    if (sel.value) cargarCausacion(sel);
});
</script>
@endpush
@endsection
