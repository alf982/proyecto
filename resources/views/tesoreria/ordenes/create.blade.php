@extends('layouts.app')
@section('title', 'Nueva Orden de Pago')
@section('breadcrumb')
    <a href="{{ route('tesoreria.ordenes.index') }}">Órdenes de Pago</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Nueva Orden</span>
@endsection
@section('content')
<div class="page-header fade-up">
    <h1 class="page-title">Nueva Orden de Pago</h1>
    <p class="page-subtitle">Seleccione la causación presupuestaria de origen</p>
</div>

<form method="POST" action="{{ route('tesoreria.ordenes.store') }}">
@csrf
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
<div>

{{-- ── CAUSACIÓN (OBLIGATORIO) ────────────────────────────── --}}
<div class="card fade-up">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-file-invoice" style="color:var(--accent);margin-right:6px;"></i>
            Causación Presupuestaria <span style="color:var(--accent-danger);">*</span>
        </div>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label class="form-label">Seleccione la causación aprobada</label>
            <select name="causacion_id" id="causacion_id" class="form-control" required onchange="cargarDatosCausacion(this)">
                <option value="">-- Seleccione una causación --</option>
                @foreach($causaciones as $c)
                <option value="{{ $c->id }}"
                    data-monto="{{ $c->monto_causado }}"
                    data-beneficiario="{{ $c->beneficiario }}"
                    data-rif="{{ $c->rif_beneficiario }}"
                    data-partida="{{ $c->partida->codigo ?? '' }}"
                    data-partida-desc="{{ $c->partida->descripcion ?? '' }}"
                    data-saldo="{{ $c->partida->saldo_actual ?? 0 }}"
                    data-concepto="{{ $c->concepto }}"
                    data-unidad="{{ $c->unidadEjecutora->nombre ?? '' }}"
                    {{ (old('causacion_id') == $c->id || ($causacionSeleccionada && $causacionSeleccionada->id == $c->id)) ? 'selected' : '' }}>
                    {{ $c->numero }} — {{ $c->beneficiario }} — Bs. {{ number_format($c->monto_causado,2) }}
                </option>
                @endforeach
            </select>
            @error('causacion_id')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        {{-- Panel de info de la causación seleccionada --}}
        <div id="panel-causacion" style="display:none;margin-top:12px;padding:16px;background:rgba(79,142,247,0.06);border:1px solid rgba(79,142,247,0.2);border-radius:12px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:13px;">
                <div>
                    <div style="font-size:10px;color:var(--text-secondary);font-weight:600;text-transform:uppercase;">Beneficiario</div>
                    <div id="info-beneficiario" style="font-weight:600;margin-top:2px;"></div>
                    <div id="info-rif" style="font-size:11px;color:var(--text-secondary);"></div>
                </div>
                <div>
                    <div style="font-size:10px;color:var(--text-secondary);font-weight:600;text-transform:uppercase;">Monto Causado</div>
                    <div id="info-monto" style="font-weight:800;font-size:16px;color:var(--accent-3);margin-top:2px;"></div>
                </div>
                <div>
                    <div style="font-size:10px;color:var(--text-secondary);font-weight:600;text-transform:uppercase;">Partida Presupuestaria</div>
                    <div id="info-partida" style="font-family:monospace;color:var(--accent);font-weight:700;margin-top:2px;"></div>
                    <div id="info-partida-desc" style="font-size:11px;color:var(--text-secondary);"></div>
                </div>
                <div>
                    <div style="font-size:10px;color:var(--text-secondary);font-weight:600;text-transform:uppercase;">Saldo Disponible</div>
                    <div id="info-saldo" style="font-weight:800;font-size:16px;margin-top:2px;"></div>
                </div>
            </div>
            <div style="margin-top:12px;padding-top:12px;border-top:1px solid rgba(79,142,247,0.15);">
                <div id="alerta-saldo" class="alert alert-warning" style="display:none;padding:8px 14px;font-size:12px;margin:0;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <strong>Saldo insuficiente:</strong> el monto de la orden supera el saldo disponible en la partida.
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── DATOS DE LA ORDEN ───────────────────────────────────── --}}
<div class="card fade-up" style="margin-top:20px;animation-delay:.05s;">
    <div class="card-header"><div class="card-title">Datos del Pago</div></div>
    <div class="card-body">
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Tipo de Pago *</label>
                <select name="tipo_pago" class="form-control" required>
                    <option value="transferencia" {{ old('tipo_pago','transferencia')=='transferencia'?'selected':'' }}>Transferencia</option>
                    <option value="cheque" {{ old('tipo_pago')=='cheque'?'selected':'' }}>Cheque</option>
                    <option value="efectivo" {{ old('tipo_pago')=='efectivo'?'selected':'' }}>Efectivo</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Beneficiario (opcional)</label>
                <select name="beneficiario_id" id="sel-beneficiario" class="form-control" onchange="cargarDatosBeneficiario(this)">
                    <option value="">Sin beneficiario específico</option>
                    @foreach($beneficiarios as $b)
                    <option value="{{ $b->id }}"
                        data-banco="{{ $b->banco_nombre }}"
                        data-cuenta="{{ $b->banco_cuenta }}"
                        data-tipo-cuenta="{{ $b->banco_tipo_cuenta }}"
                        data-rif="{{ $b->rif }}"
                        {{ old('beneficiario_id')==$b->id?'selected':'' }}>
                        [{{ $b->rif }}] {{ $b->razon_social }}
                    </option>
                    @endforeach
                </select>
                {{-- Panel de datos bancarios del beneficiario --}}
                <div id="panel-banco-benef" style="display:none;margin-top:10px;padding:12px 16px;background:rgba(124,92,252,0.06);border:1px solid rgba(124,92,252,0.2);border-radius:10px;">
                    <div style="font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px;">
                        <i class="fa-solid fa-building-columns" style="color:var(--accent-2);margin-right:4px;"></i>
                        Datos Bancarios del Beneficiario
                    </div>
                    <div style="display:grid;grid-template-columns:auto 1fr;gap:4px 14px;font-size:12.5px;">
                        <span style="color:var(--text-secondary);">Banco:</span>
                        <span id="benef-banco" style="font-weight:600;"></span>
                        <span style="color:var(--text-secondary);">Número:</span>
                        <span id="benef-cuenta" style="font-family:monospace;font-weight:700;color:var(--accent-2);letter-spacing:.5px;"></span>
                        <span style="color:var(--text-secondary);">Tipo:</span>
                        <span id="benef-tipo" style="font-weight:600;"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Concepto del Pago *</label>
            <textarea name="concepto" id="campo-concepto" class="form-control" rows="2" required maxlength="500">{{ old('concepto') }}</textarea>
            @error('concepto')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label class="form-label">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones') }}</textarea>
        </div>
    </div>
</div>

{{-- ── DETALLE DE LÍNEAS ───────────────────────────────────── --}}
<div class="card fade-up" style="margin-top:20px;animation-delay:.07s;">
    <div class="card-header">
        <div class="card-title">Detalle del Pago</div>
        <button type="button" class="btn btn-outline btn-sm" onclick="agregarLinea()">
            <i class="fa-solid fa-plus"></i> Agregar línea
        </button>
    </div>
    <div class="card-body" id="lineas-container">
        <div class="linea-op" style="display:grid;grid-template-columns:1fr 160px auto;gap:10px;margin-bottom:10px;align-items:end;">
            <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Descripción</label>
                <input type="text" name="lineas[0][descripcion]" class="form-control" required placeholder="Descripción del item">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Monto (Bs.)</label>
                <input type="number" name="lineas[0][monto]" class="form-control linea-monto" step="0.01" min="0" required oninput="calcularTotal()">
            </div>
            <div style="padding-bottom:2px;">
                <button type="button" class="btn btn-outline btn-sm" style="color:var(--accent-danger);border-color:rgba(247,95,95,.3);" onclick="this.closest('.linea-op').remove();calcularTotal()" title="Eliminar línea">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;align-items:center;gap:12px;">
        <span style="font-size:13px;color:var(--text-secondary);">Total de la Orden:</span>
        <span id="total-display" style="font-size:22px;font-weight:800;color:var(--accent-3);">Bs. 0.00</span>
    </div>
</div>

</div>
{{-- ── PANEL LATERAL ───────────────────────────────────────── --}}
<div>
<div class="card fade-up" style="animation-delay:.1s">
    <div class="card-header"><div class="card-title">Ejercicio Fiscal</div></div>
    <div class="card-body">
        @if($ejercicio)
        <div style="padding:12px;background:rgba(34,211,166,0.07);border:1px solid rgba(34,211,166,0.2);border-radius:10px;">
            <div style="font-size:12px;color:var(--text-secondary);">Ejercicio activo</div>
            <div style="font-size:18px;font-weight:700;color:var(--accent-3);">{{ $ejercicio->anio }}</div>
        </div>
        @else
        <div class="alert alert-warning">Sin ejercicio fiscal activo.</div>
        @endif
    </div>
</div>

<div class="card fade-up" style="margin-top:16px;animation-delay:.12s;border:1px solid rgba(34,211,166,0.2);">
    <div class="card-body" style="padding:16px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
            <i class="fa-solid fa-circle-info" style="color:var(--accent-3);"></i>
            <span style="font-size:12px;font-weight:600;color:var(--text-secondary);">FLUJO INTEGRADO</span>
        </div>
        <div style="font-size:12px;color:var(--text-secondary);line-height:1.7;">
            Al marcar la orden como <strong>pagada</strong>, el sistema:<br>
            <span style="color:var(--accent-3);">✓</span> Crea el Pago presupuestario<br>
            <span style="color:var(--accent-3);">✓</span> Descuenta el saldo de la partida<br>
            <span style="color:var(--accent-3);">✓</span> Marca la causación como pagada
        </div>
    </div>
</div>

<div class="card fade-up" style="margin-top:16px;animation-delay:.15s">
    <div class="card-body" style="display:flex;flex-direction:column;gap:10px;">
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
            <i class="fa-solid fa-paper-plane"></i> Crear Orden de Pago
        </button>
        <a href="{{ route('tesoreria.ordenes.index') }}" class="btn btn-outline" style="width:100%;justify-content:center;">Cancelar</a>
    </div>
</div>
</div>
</div>
</form>

@push('scripts')
<script>
let idx = 1;

function cargarDatosCausacion(sel) {
    const opt = sel.options[sel.selectedIndex];
    const panel = document.getElementById('panel-causacion');

    if (!sel.value) {
        panel.style.display = 'none';
        return;
    }

    panel.style.display = 'block';
    document.getElementById('info-beneficiario').textContent = opt.dataset.beneficiario || '—';
    document.getElementById('info-rif').textContent          = opt.dataset.rif ? 'RIF: ' + opt.dataset.rif : '';
    document.getElementById('info-monto').textContent        = 'Bs. ' + parseFloat(opt.dataset.monto||0).toLocaleString('es-VE',{minimumFractionDigits:2});
    document.getElementById('info-partida').textContent      = opt.dataset.partida || '—';
    document.getElementById('info-partida-desc').textContent = opt.dataset.partidaDesc || '';

    const saldo = parseFloat(opt.dataset.saldo || 0);
    const saldoEl = document.getElementById('info-saldo');
    saldoEl.textContent = 'Bs. ' + saldo.toLocaleString('es-VE',{minimumFractionDigits:2});
    saldoEl.style.color = saldo > 0 ? 'var(--accent-3)' : 'var(--accent-danger)';

    // Pre-llenar concepto si está vacío
    const conceptoEl = document.getElementById('campo-concepto');
    if (!conceptoEl.value && opt.dataset.concepto) {
        conceptoEl.value = opt.dataset.concepto;
    }

    calcularTotal();
}

function calcularTotal() {
    let t = 0;
    document.querySelectorAll('.linea-monto').forEach(i => t += parseFloat(i.value || 0));
    document.getElementById('total-display').textContent = 'Bs. ' + t.toLocaleString('es-VE',{minimumFractionDigits:2});

    // Comparar con saldo disponible
    const sel = document.getElementById('causacion_id');
    if (sel.value) {
        const saldo = parseFloat(sel.options[sel.selectedIndex].dataset.saldo || 0);
        document.getElementById('alerta-saldo').style.display = (t > saldo && saldo > 0) ? 'block' : 'none';
    }
}

function cargarDatosBeneficiario(sel) {
    const panel = document.getElementById('panel-banco-benef');
    if (!sel.value) { panel.style.display = 'none'; return; }
    const opt = sel.options[sel.selectedIndex];
    const banco  = opt.dataset.banco  || '';
    const cuenta = opt.dataset.cuenta || '';
    const tipo   = opt.dataset.tipoCuenta || '';
    if (!banco && !cuenta) { panel.style.display = 'none'; return; }
    panel.style.display = 'block';
    document.getElementById('benef-banco').textContent  = banco  || '—';
    document.getElementById('benef-cuenta').textContent = cuenta || '—';
    document.getElementById('benef-tipo').textContent   = tipo ? tipo.charAt(0).toUpperCase() + tipo.slice(1) : '—';
}

function agregarLinea() {
    const c = document.getElementById('lineas-container');
    c.insertAdjacentHTML('beforeend', `
    <div class="linea-op" style="display:grid;grid-template-columns:1fr 160px auto;gap:10px;margin-bottom:10px;align-items:end;">
        <div class="form-group" style="margin-bottom:0">
            <input type="text" name="lineas[${idx}][descripcion]" class="form-control" placeholder="Descripción" required>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <input type="number" name="lineas[${idx}][monto]" class="form-control linea-monto" step="0.01" min="0" required oninput="calcularTotal()">
        </div>
        <div style="padding-bottom:2px;">
            <button type="button" class="btn btn-outline btn-sm" style="color:var(--accent-danger);border-color:rgba(247,95,95,.3);" onclick="this.closest('.linea-op').remove();calcularTotal()" title="Eliminar">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    </div>`);
    idx++;
}

// Cargar si hay pre-selección
window.addEventListener('DOMContentLoaded', () => {
    const sel  = document.getElementById('causacion_id');
    if (sel.value) cargarDatosCausacion(sel);

    const selB = document.getElementById('sel-beneficiario');
    if (selB && selB.value) cargarDatosBeneficiario(selB);
});
</script>
@endpush
@endsection
