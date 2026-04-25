@extends('layouts.app')
@section('title', 'Nueva Causación')
@section('breadcrumb')
    <a href="{{ route('presupuesto.causaciones.index') }}" style="color:var(--text-secondary);text-decoration:none;">Causaciones</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Nueva</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Nueva Causación</h1>
        <p class="page-subtitle">Vinculada a un compromiso aprobado</p>
    </div>
    <a href="{{ route('presupuesto.causaciones.index') }}" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
</div>

<form method="POST" action="{{ route('presupuesto.causaciones.store') }}">
@csrf
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
<div>

{{-- ── 1. SELECTOR DE COMPROMISO ─────────────────────────────── --}}
<div class="card fade-up">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-handshake" style="color:var(--accent);margin-right:8px;"></i>
            Compromiso Origen *
        </div>
        <a href="{{ route('presupuesto.compromisos.create') }}" style="font-size:12px;color:var(--accent);text-decoration:none;gap:5px;display:flex;align-items:center;">
            <i class="fa-solid fa-plus"></i> Nuevo compromiso
        </a>
    </div>
    <div class="card-body">
        @if($compromisos->isEmpty())
            <div style="padding:20px;text-align:center;color:var(--text-secondary);font-size:13px;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size:24px;margin-bottom:8px;display:block;color:var(--accent-warn);"></i>
                No hay compromisos aprobados disponibles.<br>
                <a href="{{ route('presupuesto.compromisos.create') }}" class="btn btn-primary btn-sm" style="margin-top:12px;">
                    <i class="fa-solid fa-plus"></i> Crear compromiso
                </a>
            </div>
        @else
            <div class="form-group">
                <label class="form-label">Seleccionar compromiso aprobado *</label>
                <select name="compromiso_id" id="sel-compromiso" class="form-control" required onchange="cargarCompromiso(this)">
                    <option value="">— Seleccione un compromiso —</option>
                    @foreach($compromisos as $c)
                    <option value="{{ $c->id }}"
                        data-num="{{ $c->numero }}"
                        data-beneficiario="{{ $c->beneficiario }}"
                        data-rif="{{ $c->rif_beneficiario }}"
                        data-monto="{{ $c->monto }}"
                        data-partida="{{ $c->partida?->codigo }} — {{ $c->partida?->descripcion }}"
                        data-unidad="{{ $c->unidadEjecutora?->nombre }}"
                        data-concepto="{{ $c->concepto }}"
                        {{ (old('compromiso_id', $compromisoSeleccionado?->id) == $c->id) ? 'selected' : '' }}>
                        {{ $c->numero }} — {{ $c->beneficiario }} (Bs. {{ number_format($c->monto, 2) }})
                    </option>
                    @endforeach
                </select>
                @error('compromiso_id')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            {{-- Ficha del compromiso seleccionado --}}
            <div id="ficha-compromiso" style="display:none;margin-top:14px;padding:14px 16px;background:rgba(79,142,247,0.06);border:1px solid rgba(79,142,247,0.2);border-radius:10px;">
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;">
                    <div>
                        <div style="font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:3px;">Número</div>
                        <div id="fc-num" style="font-size:13px;font-weight:700;color:var(--accent);"></div>
                    </div>
                    <div>
                        <div style="font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:3px;">Beneficiario</div>
                        <div id="fc-beneficiario" style="font-size:13px;font-weight:600;"></div>
                        <div id="fc-rif" style="font-size:11px;color:var(--text-secondary);"></div>
                    </div>
                    <div>
                        <div style="font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:3px;">Monto Comprometido</div>
                        <div id="fc-monto" style="font-size:16px;font-weight:800;font-family:monospace;color:var(--accent-3);"></div>
                    </div>
                    <div>
                        <div style="font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:3px;">Partida</div>
                        <div id="fc-partida" style="font-size:12px;"></div>
                    </div>
                    <div>
                        <div style="font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:3px;">Unidad Ejecutora</div>
                        <div id="fc-unidad" style="font-size:12px;"></div>
                    </div>
                </div>
                <div id="fc-concepto-wrap" style="margin-top:10px;padding-top:10px;border-top:1px solid rgba(79,142,247,0.15);">
                    <div style="font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:3px;">Concepto</div>
                    <div id="fc-concepto" style="font-size:12px;color:var(--text-secondary);"></div>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ── 2. DOCUMENTO SOPORTE ───────────────────────────────────── --}}
<div class="card fade-up" style="margin-top:18px;animation-delay:.04s;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-file-invoice" style="color:var(--accent-3);margin-right:8px;"></i>Documento Soporte
        </div>
    </div>
    <div class="card-body">
        <div class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label">Tipo *</label>
                <select name="tipo_documento" class="form-control" required>
                    @foreach(['factura'=>'Factura','contrato'=>'Contrato','recibo'=>'Recibo','planilla'=>'Planilla','otro'=>'Otro'] as $v => $l)
                    <option value="{{ $v }}" {{ old('tipo_documento','factura') === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
                @error('tipo_documento')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">N° Documento</label>
                <input type="text" name="numero_documento" class="form-control"
                       value="{{ old('numero_documento') }}" placeholder="FAC-001-0045">
            </div>
            <div class="form-group">
                <label class="form-label">Fecha del Documento</label>
                <input type="date" name="fecha_documento" class="form-control"
                       value="{{ old('fecha_documento') }}">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Descripción del documento</label>
            <input type="text" name="descripcion_documento" class="form-control"
                   value="{{ old('descripcion_documento') }}" placeholder="Ej: Factura por suministro de materiales">
        </div>
    </div>
</div>

{{-- ── 3. CONCEPTO Y MONTOS ───────────────────────────────────── --}}
<div class="card fade-up" style="margin-top:18px;animation-delay:.06s;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-calculator" style="color:var(--accent-warn);margin-right:8px;"></i>Concepto y Montos
        </div>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label class="form-label">Concepto del Gasto *</label>
            <textarea name="concepto" class="form-control" rows="2" required
                placeholder="Descripción del gasto causado...">{{ old('concepto') }}</textarea>
            @error('concepto')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-row form-row-2" style="margin-bottom:0;">
            <div class="form-group">
                <label class="form-label">Fecha de Causación *</label>
                <input type="date" name="fecha_causacion" class="form-control"
                       value="{{ old('fecha_causacion', now()->toDateString()) }}" required>
                @error('fecha_causacion')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Calculadora de IVA --}}
        <div style="margin-top:16px;padding:16px;background:rgba(79,142,247,0.04);border:1px solid rgba(79,142,247,0.15);border-radius:12px;">
            <div style="font-size:11px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:14px;">
                <i class="fa-solid fa-receipt" style="color:var(--accent);margin-right:5px;"></i>
                Desglose de la Factura
            </div>

            <div class="form-row form-row-3">
                {{-- Monto base sin IVA --}}
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Monto Base (sin IVA) *</label>
                    <input type="number" name="monto_sin_iva" id="monto-sin-iva"
                           class="form-control" step="0.01" min="0" placeholder="0.00"
                           value="{{ old('monto_sin_iva') }}"
                           oninput="calcularConIva()"
                           style="font-size:16px;font-weight:600;">
                    @error('monto_sin_iva')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Alícuota IVA --}}
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Alícuota IVA (%)</label>
                    <select name="alicuota_iva" id="sel-alicuota" class="form-control"
                            onchange="calcularConIva()">
                        <option value="0"   {{ old('alicuota_iva', 16) == 0  ? 'selected':'' }}>Sin IVA (0%)</option>
                        <option value="8"   {{ old('alicuota_iva', 16) == 8  ? 'selected':'' }}>IVA reducido 8%</option>
                        <option value="16"  {{ old('alicuota_iva', 16) == 16 ? 'selected':'' }}>IVA general 16%</option>
                        <option value="31"  {{ old('alicuota_iva', 16) == 31 ? 'selected':'' }}>IVA adicional 31%</option>
                    </select>
                </div>

                {{-- Monto IVA (calculado, solo lectura) --}}
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Monto IVA (auto-calculado)</label>
                    <div id="display-monto-iva"
                         style="padding:9px 12px;background:rgba(247,185,79,0.08);border:1px solid rgba(247,185,79,0.2);
                                border-radius:8px;font-size:16px;font-weight:700;color:var(--accent-warn);
                                font-family:monospace;min-height:42px;">
                        Bs. 0.00
                    </div>
                </div>
            </div>

            {{-- Total con IVA --}}
            <div style="margin-top:14px;padding:14px 18px;background:rgba(34,211,166,0.08);border:1px solid rgba(34,211,166,0.25);border-radius:10px;
                        display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="font-size:11px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;">
                        Total Factura (con IVA) = Monto a Causar
                    </div>
                    <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">
                        Este es el monto comprometido en el compromiso presupuestario
                    </div>
                </div>
                <div style="font-size:26px;font-weight:800;font-family:monospace;color:var(--accent-3);"
                     id="display-total-causado">
                    Bs. 0.00
                </div>
            </div>

            {{-- Input hidden/editable del monto_causado --}}
            <div style="margin-top:10px;">
                <label class="form-label" style="font-size:11px;color:var(--text-secondary);">
                    <i class="fa-solid fa-pen" style="font-size:10px;"></i>
                    Ajuste manual del monto total (si difiere del calculado):
                </label>
                <input type="number" name="monto_causado" id="monto-causado" class="form-control"
                       value="{{ old('monto_causado') }}" required min="0.01" step="0.01"
                       placeholder="Se llena automáticamente o ingrese manualmente"
                       oninput="verificarMonto()" style="max-width:250px;">
                @error('monto_causado')<div class="form-error">{{ $message }}</div>@enderror
                <div id="alerta-monto-sup" style="display:none;margin-top:5px;font-size:12px;color:var(--accent-danger);">
                    <i class="fa-solid fa-triangle-exclamation"></i> Supera el monto del compromiso
                </div>
            </div>
        </div>

        <div class="form-group" style="margin-top:16px;">
            <label class="form-label">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones') }}</textarea>
        </div>
    </div>
</div>


{{-- ── 4. RETENCIONES ──────────────────────────────────────── --}}
@include('components.retenciones-selector', [
    'modulo'     => 'causacion',
    'inputMonto' => 'monto_causado',
])

</div>{{-- fin columna principal --}}

{{-- ── PANEL LATERAL ─────────────────────────────────────────── --}}
<div>
    <div class="card fade-up" style="animation-delay:.05s;">
        <div class="card-body" style="padding:14px 16px;">
            <div style="font-size:11px;color:var(--text-secondary);line-height:2;">
                Flujo:<br>
                <span style="color:var(--accent-3);">✓</span> Compromiso <strong>Aprobado</strong><br>
                <span style="color:var(--accent-warn);">→</span> Causación en <strong>Borrador</strong><br>
                <span style="color:var(--text-secondary);">○</span> Aprobar causación<br>
                <span style="color:var(--text-secondary);">○</span> Registrar <strong>Pago</strong>
            </div>
        </div>
    </div>

    <div class="card fade-up" style="margin-top:14px;animation-delay:.08s;">
        <div class="card-body" style="display:flex;flex-direction:column;gap:10px;">
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                <i class="fa-solid fa-save"></i> Registrar Causación
            </button>
            <a href="{{ route('presupuesto.causaciones.index') }}" class="btn btn-outline" style="width:100%;justify-content:center;">
                Cancelar
            </a>
        </div>
    </div>
</div>

</div>{{-- fin grid --}}
</form>

@push('scripts')
<script>
let montoCompromiso = 0;

function cargarCompromiso(sel) {
    const ficha = document.getElementById('ficha-compromiso');
    if (!sel.value) { ficha.style.display = 'none'; montoCompromiso = 0; return; }

    const opt = sel.options[sel.selectedIndex];
    montoCompromiso = parseFloat(opt.dataset.monto || 0);

    document.getElementById('fc-num').textContent        = opt.dataset.num;
    document.getElementById('fc-beneficiario').textContent = opt.dataset.beneficiario;
    document.getElementById('fc-rif').textContent        = opt.dataset.rif || '';
    document.getElementById('fc-monto').textContent      = 'Bs. ' + montoCompromiso.toLocaleString('es-VE', {minimumFractionDigits:2});
    document.getElementById('fc-partida').textContent    = opt.dataset.partida;
    document.getElementById('fc-unidad').textContent     = opt.dataset.unidad;
    document.getElementById('fc-concepto').textContent   = opt.dataset.concepto;

    ficha.style.display = 'block';
    verificarMonto();
}

function calcularConIva() {
    const sinIva    = parseFloat(document.getElementById('monto-sin-iva')?.value || 0);
    const alicuota  = parseFloat(document.getElementById('sel-alicuota')?.value || 0);
    const montoIva  = Math.round(sinIva * (alicuota / 100) * 100) / 100;
    const total     = Math.round((sinIva + montoIva) * 100) / 100;

    const fmt = v => 'Bs. ' + v.toLocaleString('es-VE', {minimumFractionDigits:2, maximumFractionDigits:2});

    // Actualizar displays
    const dispIva   = document.getElementById('display-monto-iva');
    const dispTotal = document.getElementById('display-total-causado');
    if (dispIva)   dispIva.textContent   = fmt(montoIva);
    if (dispTotal) dispTotal.textContent = fmt(total);

    // Auto-rellenar el campo monto_causado si el base > 0
    const campoCausado = document.getElementById('monto-causado');
    if (campoCausado && sinIva > 0) {
        campoCausado.value = total.toFixed(2);
    }

    verificarMonto();
}

function verificarMonto() {
    const causado = parseFloat(document.getElementById('monto-causado')?.value || 0);
    const alerta  = document.getElementById('alerta-monto-sup');
    if (alerta) alerta.style.display = (montoCompromiso > 0 && causado > montoCompromiso) ? 'block' : 'none';
}

window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('sel-compromiso');
    if (sel?.value) cargarCompromiso(sel);
    // Calcular si hay valores old() (error de validación)
    calcularConIva();
});
</script>
@endpush
@endsection

