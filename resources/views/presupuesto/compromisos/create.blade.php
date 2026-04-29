@extends('layouts.app')
@section('title','Nuevo Compromiso')
@section('breadcrumb')
    <a href="{{ route('presupuesto.compromisos.index') }}" style="color:var(--text-secondary);text-decoration:none;">Compromisos</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Nuevo</span>
@endsection
@push('styles')
<style>
    .dropdown-ajax-results {
        position: absolute; top: 100%; left: 0; right: 0; z-index: 1000;
        background: #fff; border: 1px solid var(--border); border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-top: 4px; display: none;
        max-height: 250px; overflow-y: auto;
    }
    .result-item {
        padding: 10px 14px; cursor: pointer; border-bottom: 1px solid var(--border);
        transition: background .2s;
    }
    .result-item:hover, .result-item.active { background: var(--bg-hover); border-left: 3px solid var(--accent); }
    .result-item:last-child { border-bottom: none; }
    .result-item .rif { font-family: monospace; font-weight: 700; color: var(--primary); font-size: 12px; }
    .result-item .name { font-size: 13px; color: var(--text-main); display: block; }
</style>
@endpush
@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Nuevo Compromiso</h1>
        <p class="page-subtitle">Al aprobarse generará automáticamente la causación</p>
    </div>
    <a href="{{ route('presupuesto.compromisos.index') }}" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
</div>

<form method="POST" action="{{ route('presupuesto.compromisos.store') }}">
@csrf
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
<div>

{{-- ── 1. PARTIDA PRESUPUESTARIA ────────────────────────────── --}}
<div class="card fade-up">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-list-ol" style="color:var(--accent);margin-right:8px;"></i>Imputación Presupuestaria
        </div>
    </div>
    <div class="card-body">
        <div class="form-row form-row-2">
            <div class="form-group" style="grid-column:span 2;">
                <label class="form-label">Partida Presupuestaria *</label>
                <select name="partida_presupuestaria_id" id="partida_id" class="form-control" required onchange="mostrarSaldo(this)">
                    <option value="">— Seleccione una partida —</option>
                    @foreach($partidas as $p)
                    <option value="{{ $p->id }}"
                        data-saldo="{{ $p->saldo_actual }}"
                        {{ old('partida_presupuestaria_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->codigo }} — {{ $p->descripcion }}
                    </option>
                    @endforeach
                </select>
                @error('partida_presupuestaria_id')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Unidad Ejecutora *</label>
                <select name="unidad_ejecutora_id" class="form-control" required>
                    <option value="">Seleccionar...</option>
                    @foreach($unidades as $u)
                    <option value="{{ $u->id }}" {{ old('unidad_ejecutora_id') == $u->id ? 'selected' : '' }}>
                        [{{ $u->codigo }}] {{ $u->nombre }}
                    </option>
                    @endforeach
                </select>
                @error('unidad_ejecutora_id')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Proyecto (opcional)</label>
                <select name="proyecto_id" class="form-control">
                    <option value="">Sin proyecto</option>
                    @foreach($proyectos as $p)
                    <option value="{{ $p->id }}" {{ old('proyecto_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->codigo }} — {{ $p->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Saldo disponible --}}
        <div id="panel-saldo" style="display:none;margin-top:12px;padding:12px 18px;background:rgba(34,211,166,0.07);border:1px solid rgba(34,211,166,0.2);border-radius:10px;">
            <div style="font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:4px;">Saldo disponible en la partida</div>
            <div id="texto-saldo" style="font-size:22px;font-weight:800;font-family:monospace;color:var(--accent-3);">—</div>
            <div id="alerta-saldo-cero" style="display:none;margin-top:6px;font-size:12px;color:var(--accent-danger);">
                <i class="fa-solid fa-triangle-exclamation"></i> Sin saldo disponible
            </div>
        </div>
    </div>
</div>

{{-- ── 2. BENEFICIARIO ─────────────────────────────────────────── --}}
<div class="card fade-up" style="margin-top:18px;animation-delay:.03s;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-building" style="color:var(--accent-2);margin-right:8px;"></i>Beneficiario
        </div>
        <a href="{{ route('admin.beneficiarios.create') }}" target="_blank"
           style="font-size:12px;color:var(--accent);text-decoration:none;display:flex;align-items:center;gap:5px;">
            <i class="fa-solid fa-plus"></i> Agregar nuevo
        </a>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label class="form-label">Seleccionar del catálogo *</label>
            <select id="sel-beneficiario-lista" name="beneficiario_id" class="form-control" onchange="autoRellenarDesdeLista(this)">
                <option value="">— Seleccione un beneficiario para auto-rellenar —</option>
                @foreach($beneficiarios as $b)
                <option value="{{ $b->id }}"
                    data-nombre="{{ $b->razon_social }}"
                    data-rif="{{ $b->rif }}"
                    data-tipo="{{ $b->getTipoLabel() }}"
                    data-banco="{{ $b->banco_nombre }}"
                    data-cuenta="{{ $b->banco_cuenta }}"
                    {{ old('beneficiario_id') == $b->id ? 'selected' : '' }}>
                    [{{ $b->rif }}] {{ $b->razon_social }}
                </option>
                @endforeach
            </select>
            @error('beneficiario_id')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        {{-- Ficha del beneficiario seleccionado --}}
        <div id="ficha-beneficiario" style="display:none;margin-top:12px;padding:12px 16px;background:rgba(79,142,247,0.06);border:1px solid rgba(79,142,247,0.18);border-radius:10px;">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;">
                <div>
                    <div style="font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:3px;">Razón Social</div>
                    <div id="fb-nombre" style="font-size:13px;font-weight:600;"></div>
                </div>
                <div>
                    <div style="font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:3px;">RIF</div>
                    <div id="fb-rif" style="font-size:13px;font-family:monospace;font-weight:600;color:var(--accent);"></div>
                </div>
                <div>
                    <div style="font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:3px;">Tipo</div>
                    <div id="fb-tipo" style="font-size:13px;"></div>
                </div>
                <div id="fb-banco-wrap" style="display:none;">
                    <div style="font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:3px;">Banco</div>
                    <div id="fb-banco" style="font-size:12px;"></div>
                    <div id="fb-cuenta" style="font-size:11px;color:var(--text-secondary);font-family:monospace;"></div>
                </div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem;margin-top:15px;">
            <div class="form-group">
                <label class="form-label">Nombre / Razón Social *</label>
                <input type="text" name="beneficiario" id="hid-beneficiario" class="form-control" 
                       value="{{ old('beneficiario') }}" placeholder="Nombre del beneficiario">
            </div>
            <div class="form-group">
                <label class="form-label">RIF *</label>
                <div style="display:flex;gap:5px">
                    <input type="text" name="rif_beneficiario" id="hid-rif" class="form-control" 
                           value="{{ old('rif_beneficiario') }}" placeholder="J-12345678-9">
                    <button type="button" class="btn btn-outline" onclick="buscarPorRifManual()" title="Buscar y autorellenar">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>



{{-- ── 4. CONCEPTO Y MONTO ──────────────────────────────────────── --}}
<div class="card fade-up" style="margin-top:18px;animation-delay:.07s;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-calculator" style="color:var(--accent-warn);margin-right:8px;"></i>Concepto y Montos de la Factura
        </div>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label class="form-label">Concepto del gasto *</label>
            <textarea name="concepto" class="form-control" rows="2" required>{{ old('concepto') }}</textarea>
            @error('concepto')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Fecha Compromiso *</label>
                <input type="date" name="fecha_compromiso" class="form-control"
                       value="{{ old('fecha_compromiso', now()->toDateString()) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Fecha Vencimiento</label>
                <input type="date" name="fecha_vencimiento" class="form-control"
                       value="{{ old('fecha_vencimiento') }}">
            </div>
        </div>

        {{-- Calculadora IVA --}}
        <div style="margin-top:12px;padding:16px;background:rgba(79,142,247,0.04);border:1px solid rgba(79,142,247,0.15);border-radius:12px;">
            <div style="font-size:11px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:14px;">
                <i class="fa-solid fa-receipt" style="color:var(--accent);margin-right:5px;"></i>
                Desglose de la Factura
            </div>

            <div class="form-row form-row-3">
                {{-- Monto base sin IVA --}}
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Monto Base (sin IVA) *</label>
                    <input type="number" name="monto_sin_iva" id="campo-sin-iva"
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
                        <option value="0"  {{ old('alicuota_iva', 16) == 0  ? 'selected':'' }}>Sin IVA (0%)</option>
                        <option value="8"  {{ old('alicuota_iva', 16) == 8  ? 'selected':'' }}>IVA reducido 8%</option>
                        <option value="16" {{ old('alicuota_iva', 16) == 16 ? 'selected':'' }}>IVA general 16%</option>
                        <option value="31" {{ old('alicuota_iva', 16) == 31 ? 'selected':'' }}>IVA adicional 31%</option>
                    </select>
                </div>

                {{-- Monto IVA (sólo lectura) --}}
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Monto IVA (calculado)</label>
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
                        Total Factura con IVA = Monto del Compromiso
                    </div>
                    <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">
                        Este monto se reserva de la partida presupuestaria
                    </div>
                </div>
                <div style="font-size:28px;font-weight:800;font-family:monospace;color:var(--accent-3);"
                     id="display-total-monto">
                    Bs. 0.00
                </div>
            </div>

            {{-- monto (ajustable manualmente) --}}
            <div style="margin-top:10px;">
                <label class="form-label" style="font-size:11px;color:var(--text-secondary);">
                    <i class="fa-solid fa-pen" style="font-size:10px;"></i>
                    Ajuste manual si el total difiere del calculado:
                </label>
                <input type="number" name="monto" id="campo-monto" class="form-control"
                       value="{{ old('monto') }}" required min="0.01" step="0.01"
                       placeholder="Se llena automáticamente"
                       oninput="verificarMonto(); calcularNeto()"
                       style="max-width:260px;">
                @error('monto')<div class="form-error">{{ $message }}</div>@enderror
                <div id="alerta-monto" style="display:none;margin-top:6px;font-size:12px;color:var(--accent-danger);">
                    <i class="fa-solid fa-triangle-exclamation"></i> Supera el saldo disponible
                </div>
            </div>
        </div>

        <div class="form-group" style="margin-top:14px;">
            <label class="form-label">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones') }}</textarea>
        </div>
    </div>
</div>


{{-- ── 3. DOCUMENTO SOPORTE ────────────────────────────────────── --}}
<div class="card fade-up" style="margin-top:18px;animation-delay:.09s;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-file-invoice" style="color:var(--accent-3);margin-right:8px;"></i>Documento Soporte (Factura / Contrato)
        </div>
    </div>
    <div class="card-body">
        <div class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label">Tipo de Documento</label>
                <select name="tipo_documento" class="form-control">
                    <option value="">— Sin documento aún —</option>
                    @foreach(['factura'=>'Factura','contrato'=>'Contrato','recibo'=>'Recibo','planilla'=>'Planilla','otro'=>'Otro'] as $v => $l)
                    <option value="{{ $v }}" {{ old('tipo_documento') === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
                @error('tipo_documento')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">N° Documento</label>
                <input type="text" name="numero_documento" class="form-control"
                       value="{{ old('numero_documento') }}" placeholder="FAC-001-0045">
                @error('numero_documento')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Fecha del Documento</label>
                <input type="date" name="fecha_documento" class="form-control"
                       value="{{ old('fecha_documento') }}">
                @error('fecha_documento')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Descripción del Documento</label>
            <input type="text" name="descripcion_documento" class="form-control"
                   value="{{ old('descripcion_documento') }}" placeholder="Ej: Factura por suministro de materiales">
        </div>
    </div>
</div>

</div>{{-- fin columna principal --}}

{{-- ── PANEL LATERAL ────────────────────────────────────────────── --}}
<div>
    @if($ejercicio)
    <div class="card fade-up" style="animation-delay:.04s;border:1px solid rgba(34,211,166,0.25);">
        <div class="card-body" style="padding:16px;">
            <div style="font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;">Ejercicio Fiscal Activo</div>
            <div style="font-size:26px;font-weight:800;color:var(--accent-3);margin-top:4px;">{{ $ejercicio->anio }}</div>
        </div>
    </div>
    @endif

    <div class="card fade-up" style="margin-top:14px;animation-delay:.06s;">
        <div class="card-body" style="padding:14px 16px;">
            <div style="font-size:11px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:10px;">Flujo del proceso</div>
            <div style="font-size:12px;line-height:2.2;color:var(--text-secondary);">
                <div><span style="color:var(--accent);">1.</span> <strong>Crear compromiso</strong></div>
                <div style="padding-left:12px;font-size:11px;opacity:.7;">↳ incluye datos de la factura/documento</div>
                <div><span style="color:var(--accent);">2.</span> Aprobar compromiso</div>
                <div style="padding-left:12px;font-size:11px;opacity:.7;">↳ genera causación con los datos del doc.</div>
                <div><span style="color:var(--accent-3);">3.</span> <strong>Aprobar causación → Pago</strong></div>
            </div>
        </div>
    </div>

    <div class="card fade-up" style="margin-top:14px;animation-delay:.08s;">
        <div class="card-body" style="display:flex;flex-direction:column;gap:10px;">
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                <i class="fa-solid fa-save"></i> Registrar Compromiso
            </button>
            <a href="{{ route('presupuesto.compromisos.index') }}" class="btn btn-outline" style="width:100%;justify-content:center;">
                Cancelar
            </a>
        </div>
    </div>
</div>

</div>{{-- fin grid --}}
</form>

@push('scripts')
<script>
// ── Saldo de partida ──────────────────────────────
let saldoActual = 0;

function mostrarSaldo(sel) {
    const opt = sel.options[sel.selectedIndex];
    const panel = document.getElementById('panel-saldo');
    if (!sel.value) { panel.style.display = 'none'; saldoActual = 0; return; }
    saldoActual = parseFloat(opt.dataset.saldo || 0);
    panel.style.display = 'block';
    const txt = document.getElementById('texto-saldo');
    txt.textContent = 'Bs. ' + saldoActual.toLocaleString('es-VE', { minimumFractionDigits: 2 });
    txt.style.color = saldoActual > 0 ? 'var(--accent-3)' : 'var(--accent-danger)';
    document.getElementById('alerta-saldo-cero').style.display = saldoActual <= 0 ? 'block' : 'none';
    verificarMonto();
}

function verificarMonto() {
    const monto = parseFloat(document.getElementById('campo-monto').value || 0);
    document.getElementById('alerta-monto').style.display =
        (saldoActual > 0 && monto > saldoActual) ? 'block' : 'none';
}

// ── Calculadora IVA ───────────────────────────────
function calcularConIva() {
    const sinIva   = parseFloat(document.getElementById('campo-sin-iva')?.value || 0);
    const alicuota = parseFloat(document.getElementById('sel-alicuota')?.value || 0);
    const montoIva = Math.round(sinIva * (alicuota / 100) * 100) / 100;
    const total    = Math.round((sinIva + montoIva) * 100) / 100;

    const fmt = v => 'Bs. ' + v.toLocaleString('es-VE', {minimumFractionDigits:2, maximumFractionDigits:2});

    const dispIva   = document.getElementById('display-monto-iva');
    const dispTotal = document.getElementById('display-total-monto');
    if (dispIva)   dispIva.textContent   = fmt(montoIva);
    if (dispTotal) dispTotal.textContent = fmt(total);

    // Auto-rellenar campo monto si el sin_iva > 0
    const campoMonto = document.getElementById('campo-monto');
    if (campoMonto && sinIva > 0) {
        campoMonto.value = total.toFixed(2);
        verificarMonto();
    }
}

// ── BÚSQUEDA AJAX PROVEEDOR ────────────────────────────────────────
const searchInp = document.getElementById('search-beneficiario');
const resultsDiv = document.getElementById('results-beneficiario');
const valIdInp = document.getElementById('val-beneficiario-id');
let timeout = null;

// ── AUTO-RELLENO DESDE LISTA ───────────────────────────────────────
function autoRellenarDesdeLista(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (!sel.value) {
        document.getElementById('ficha-beneficiario').style.display = 'none';
        return;
    }
    
    // Rellenar campos de escritura
    document.getElementById('hid-beneficiario').value = opt.dataset.nombre;
    document.getElementById('hid-rif').value          = opt.dataset.rif;
    
    // Rellenar ficha visual
    const ficha = document.getElementById('ficha-beneficiario');
    document.getElementById('fb-nombre').textContent = opt.dataset.nombre;
    document.getElementById('fb-rif').textContent    = opt.dataset.rif;
    document.getElementById('fb-tipo').textContent   = opt.dataset.tipo;
    
    const bw = document.getElementById('fb-banco-wrap');
    if (opt.dataset.banco) {
        document.getElementById('fb-banco').textContent  = opt.dataset.banco;
        document.getElementById('fb-cuenta').textContent = opt.dataset.cuenta;
        bw.style.display = 'block';
    } else { bw.style.display = 'none'; }
    
    ficha.style.display = 'block';
    
    // Feedback visual
    sel.style.borderColor = 'var(--accent-3)';
    setTimeout(() => sel.style.borderColor = '', 1000);
}

// ── AUTO-RELLENO AL ESCRIBIR RIF MANUAMENTE ────────────────────────
const inpRif = document.getElementById('hid-rif');

inpRif.addEventListener('blur', function() {
    buscarPorRifManual();
});

function buscarPorRifManual() {
    const rif = inpRif.value.trim();
    if (rif.length < 5) return;

    fetch(`{{ route('admin.beneficiarios.search-ajax') }}?q=${rif}`)
        .then(res => res.json())
        .then(data => {
            const exacto = data.find(b => b.rif.toLowerCase() === rif.toLowerCase());
            if (exacto) {
                // Simular selección
                const sel = document.getElementById('sel-beneficiario-lista');
                if (sel) {
                    sel.value = exacto.id;
                    autoRellenarDesdeLista(sel);
                }
                inpRif.classList.add('is-valid');
                setTimeout(() => inpRif.classList.remove('is-valid'), 2000);
            }
        });
}

// Cerrar resultados al hacer click fuera
document.addEventListener('click', (e) => {
    if (e.target !== searchInp) resultsDiv.style.display = 'none';
});

// Pre-cargar si hay old values (Esto requeriría cargar el beneficiario por ID via AJAX al inicio si hay old)
window.addEventListener('DOMContentLoaded', () => {
    const sp = document.getElementById('partida_id');
    if (sp?.value) mostrarSaldo(sp);
    
    // Si hay un old('beneficiario_id'), podríamos cargar sus datos aquí.
    // Por simplicidad, el usuario tendrá que buscarlo de nuevo si falla la validación por ahora.
    
    calcularConIva();
});
</script>
@endpush
@endsection

