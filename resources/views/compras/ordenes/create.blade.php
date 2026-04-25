@extends('layouts.app')
@section('title','Nueva Orden de Compra')
@section('content')
<div class="page-header">
    <div><h1 class="page-title">Nueva Orden de Compra</h1><p class="page-subtitle">Documento formal de adquisición</p></div>
    <a href="{{ route('compras.ordenes.index') }}" class="btn btn-ghost">← Volver</a>
</div>
@include('components.alert')
<form method="POST" action="{{ route('compras.ordenes.store') }}">
@csrf
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start">
    <div class="card" style="padding:1.5rem">
        <div style="display:grid;gap:1.1rem">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div class="form-group">
                    <label class="form-label">Solicitud Vinculada (opcional)</label>
                    <select name="solicitud_compra_id" class="form-control">
                        <option value="">Ninguna</option>
                        @foreach($solicitudes as $sol)<option value="{{ $sol->id }}" {{ old('solicitud_compra_id')==$sol->id?'selected':'' }}>{{ $sol->numero }} — {{ Str::limit($sol->motivo,40) }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Modalidad</label>
                    <select name="modalidad" class="form-control">
                        <option value="compra_directa">Compra Directa</option>
                        <option value="concurso">Concurso</option>
                        <option value="licitacion">Licitación</option>
                    </select>
                </div>
            </div>

            {{-- Partida Presupuestaria --}}
            <div class="form-group">
                <label class="form-label">
                    Partida Presupuestaria
                    <span style="font-size:11px;color:var(--text-muted);font-weight:400;margin-left:6px;">(opcional — reserva saldo al emitir)</span>
                </label>
                <select name="partida_presupuestaria_id" id="sel-partida" class="form-control" onchange="mostrarSaldo(this)">
                    <option value="">— Sin afectación presupuestaria —</option>
                    @foreach($partidas as $p)
                    <option value="{{ $p->id }}"
                        data-saldo="{{ $p->saldo_actual }}"
                        data-codigo="{{ $p->codigo }}"
                        {{ old('partida_presupuestaria_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->codigo }} — {{ $p->descripcion }} (Bs. {{ number_format($p->saldo_actual,2) }})
                    </option>
                    @endforeach
                </select>
                @error('partida_presupuestaria_id')<div class="form-error">{{ $message }}</div>@enderror
                {{-- Panel de saldo --}}
                <div id="panel-saldo" style="display:none;margin-top:8px;padding:10px 14px;background:rgba(34,211,166,0.06);border:1px solid rgba(34,211,166,0.2);border-radius:8px;display:none;">
                    <div style="display:flex;gap:24px;font-size:12px;flex-wrap:wrap;">
                        <div>
                            <span style="color:var(--text-secondary);">Partida:</span>
                            <strong id="info-codigo-partida" style="color:var(--accent);font-family:monospace;margin-left:4px;"></strong>
                        </div>
                        <div>
                            <span style="color:var(--text-secondary);">Saldo disponible:</span>
                            <strong id="info-saldo-partida" style="color:var(--accent-3);margin-left:4px;"></strong>
                        </div>
                    </div>
                    <div id="alerta-saldo" style="display:none;margin-top:6px;color:var(--accent-danger);font-size:11px;font-weight:600;">
                        ⚠ El total de la orden supera el saldo disponible
                    </div>
                </div>
            </div>

            <hr style="border-color:var(--border)">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div class="form-group">
                    <label class="form-label">Fecha Emisión *</label>
                    <input type="date" name="fecha_emision" class="form-control" required value="{{ old('fecha_emision',now()->toDateString()) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha Entrega Estimada</label>
                    <input type="date" name="fecha_entrega_estimada" class="form-control" value="{{ old('fecha_entrega_estimada') }}">
                </div>
            </div>
            <hr style="border-color:var(--border)">
            <h4 style="font-size:.85rem;color:var(--text-muted);text-transform:uppercase">Proveedor</h4>

            {{-- Selector del catálogo --}}
            <div class="form-group">
                <label class="form-label">
                    Buscar en catálogo de beneficiarios
                    <span style="font-size:11px;color:var(--text-muted);font-weight:400;margin-left:6px;">(opcional — auto-rellena los datos)</span>
                </label>
                <select id="sel-beneficiario" name="beneficiario_id" class="form-control" onchange="autoRellenarProveedor(this)">
                    <option value="">— Ingresar manualmente —</option>
                    @foreach($beneficiarios as $ben)
                    <option value="{{ $ben->id }}"
                        data-nombre="{{ $ben->razon_social }}"
                        data-rif="{{ $ben->rif }}"
                        data-banco="{{ $ben->banco_nombre }}"
                        data-cuenta="{{ $ben->banco_cuenta }}"
                        data-tipo-cuenta="{{ $ben->banco_tipo_cuenta }}"
                        {{ old('beneficiario_id') == $ben->id ? 'selected' : '' }}>
                        [{{ $ben->rif }}] {{ $ben->razon_social }}
                        @if($ben->tipo) · {{ $ben->getTipoLabel() }}@endif
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Campos del proveedor (auto-rellenados o manuales) --}}
            <div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem">
                <div class="form-group">
                    <label class="form-label">Nombre del Proveedor</label>
                    <input type="text" id="inp-proveedor-nombre" name="proveedor_nombre" class="form-control"
                           value="{{ old('proveedor_nombre') }}" placeholder="Nombre o razón social">
                </div>
                <div class="form-group">
                    <label class="form-label">RIF</label>
                    <input type="text" id="inp-proveedor-rif" name="proveedor_rif" class="form-control"
                           value="{{ old('proveedor_rif') }}" placeholder="J-12345678-9">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Concepto / Descripción *</label>
                <input type="text" name="concepto" class="form-control" required value="{{ old('concepto') }}">
            </div>

            {{-- Renglones --}}
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem">
                    <strong style="font-size:.9rem">Renglones</strong>
                    <button type="button" onclick="agregarLinea()" class="btn btn-ghost" style="font-size:.8rem">＋ Agregar</button>
                </div>
                <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;min-width:680px">
                    <thead>
                        <tr style="font-size:.73rem;text-transform:uppercase;color:var(--text-muted);background:rgba(0,0,0,.03)">
                            <th style="padding:.5rem .4rem;text-align:left;min-width:180px">Artículo / Código</th>
                            <th style="padding:.5rem .4rem;text-align:left;min-width:160px">Descripción *</th>
                            <th style="padding:.5rem .4rem;text-align:center;width:60px">U/M</th>
                            <th style="padding:.5rem .4rem;text-align:right;width:75px">Cantidad</th>
                            <th style="padding:.5rem .4rem;text-align:right;width:110px">Precio Unit.</th>
                            <th style="padding:.5rem .4rem;text-align:right;width:90px">Subtotal</th>
                            <th style="width:30px"></th>
                        </tr>
                    </thead>
                    <tbody id="lineas-body">
                        <tr id="linea-0" style="border-bottom:1px solid var(--border)">
                            <td style="padding:.4rem .3rem">
                                <select name="lineas[0][articulo_id]" class="form-control art-sel" style="font-size:.78rem" onchange="autoDescripcion(this,0)">
                                    <option value="">— Libre —</option>
                                    @foreach($articulos as $art)
                                    <option value="{{ $art->id }}" data-nombre="{{ $art->nombre }}" data-unidad="{{ $art->unidad_medida }}">{{ $art->codigo }} — {{ Str::limit($art->nombre,28) }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="padding:.4rem .3rem">
                                <input type="text" name="lineas[0][descripcion]" id="desc-0" class="form-control" style="font-size:.78rem" required placeholder="Descripción del item...">
                            </td>
                            <td style="padding:.4rem .3rem">
                                <input type="text" name="lineas[0][unidad_medida]" id="um-0" class="form-control" style="font-size:.78rem;text-align:center" value="unidad" placeholder="U/M">
                            </td>
                            <td style="padding:.4rem .3rem">
                                <input type="number" name="lineas[0][cantidad]" class="form-control cant" style="font-size:.78rem;text-align:right" value="1" step="0.01" min="0.01" required onchange="calcular()">
                            </td>
                            <td style="padding:.4rem .3rem">
                                <input type="number" name="lineas[0][precio_unitario]" class="form-control punit" style="font-size:.78rem;text-align:right" value="0" step="0.01" min="0" required onchange="calcular()" placeholder="0.00">
                            </td>
                            <td style="padding:.4rem .3rem;text-align:right;font-family:monospace;font-size:.82rem;white-space:nowrap" class="sub-td">0.00</td>
                            <td style="padding:.3rem;text-align:center"></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight:700;background:rgba(0,0,0,.02)">
                            <td colspan="4" style="padding:.6rem .4rem;text-align:right;color:var(--text-muted)">TOTAL:</td>
                            <td style="padding:.6rem .4rem;text-align:right;font-family:monospace;color:var(--primary);font-size:1rem" id="td-total">0.00</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Condiciones / Términos</label>
                <textarea name="condiciones" class="form-control" rows="2">{{ old('condiciones') }}</textarea>
            </div>
        </div>

        {{-- ── RETENCIONES ─────────────────────────────────── --}}
        {{-- Input oculto que recibe el total calculado dinámicamente --}}
        <input type="hidden" name="total_para_retencion" id="total-para-retencion" value="0">
        @include('components.retenciones-selector', [
            'modulo'     => 'orden_compra',
            'inputMonto' => 'total_para_retencion',
        ])

        {{-- ── RESUMEN FINANCIERO ───────────────────────────── --}}
        <div style="margin-top:16px;padding:14px 18px;background:rgba(34,211,166,0.05);
                    border:1px solid rgba(34,211,166,0.2);border-radius:12px;">
            <div style="font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:12px;">
                <i class="fa-solid fa-calculator" style="color:var(--accent-3);margin-right:5px;"></i>Resumen del Pedido
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--text-secondary);">Subtotal renglones:</span>
                    <strong id="res-oc-bruto" style="font-family:monospace;">Bs. 0.00</strong>
                </div>
                <div id="res-oc-ret-row" style="display:none;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--accent-warn);">
                        <i class="fa-solid fa-minus" style="font-size:10px;margin-right:3px;"></i>Retenciones aplicadas:
                    </span>
                    <strong id="res-oc-ret" style="font-family:monospace;color:var(--accent-warn);">Bs. 0.00</strong>
                </div>
                <div style="height:1px;background:var(--border);"></div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:12px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;">Neto a pagar al proveedor:</span>
                    <strong id="res-oc-neto" style="font-size:20px;font-family:monospace;color:var(--accent-3);">Bs. 0.00</strong>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:.75rem;justify-content:flex-end;border-top:1px solid var(--border);padding-top:1rem;margin-top:1rem">
            <a href="{{ route('compras.ordenes.index') }}" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-primary">📋 Emitir Orden de Compra</button>
        </div>
    </div>
    <div class="card" style="padding:1.2rem">
        <h4 style="font-size:.8rem;text-transform:uppercase;color:var(--text-muted);margin-bottom:.75rem">Estados</h4>
        <div style="font-size:.82rem;color:var(--text-secondary);line-height:2">
            <div>📋 <strong>Emitida</strong> — Generada</div>
            <div>🤝 <strong>Confirmada</strong> — Proveedor aceptó</div>
            <div>🚚 <strong>En Tránsito</strong> — Entregando</div>
            <div>✅ <strong>Completada</strong> — Recibida</div>
        </div>
    </div>
</div>
</form>
<script>
let c = 1;
let saldoPartida = 0;
const artOpts = `<option value="">— Libre —</option>@foreach($articulos as $art)<option value="{{ $art->id }}" data-nombre="{{ addslashes($art->nombre) }}" data-unidad="{{ $art->unidad_medida }}">{{ $art->codigo }} — {{ addslashes(Str::limit($art->nombre,28)) }}</option>@endforeach`;

// ── AUTO-RELLENO PROVEEDOR ─────────────────────────────────────────
function autoRellenarProveedor(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (!sel.value) {
        // Limpiar campos
        document.getElementById('inp-proveedor-nombre').value = '';
        document.getElementById('inp-proveedor-rif').value    = '';
        return;
    }
    document.getElementById('inp-proveedor-nombre').value = opt.dataset.nombre || '';
    document.getElementById('inp-proveedor-rif').value    = opt.dataset.rif    || '';
}

function mostrarSaldo(sel) {
    const opt    = sel.options[sel.selectedIndex];
    const panel  = document.getElementById('panel-saldo');
    if (!sel.value) { panel.style.display = 'none'; saldoPartida = 0; validarSaldo(); return; }
    saldoPartida = parseFloat(opt.dataset.saldo || 0);
    document.getElementById('info-codigo-partida').textContent = opt.dataset.codigo || '';
    document.getElementById('info-saldo-partida').textContent  = 'Bs. ' + saldoPartida.toLocaleString('es-VE',{minimumFractionDigits:2});
    panel.style.display = 'block';
    validarSaldo();
}

function validarSaldo() {
    const total = parseFloat(document.getElementById('td-total').textContent || 0);
    const alerta = document.getElementById('alerta-saldo');
    if (!alerta) return;
    alerta.style.display = (saldoPartida > 0 && total > saldoPartida) ? 'block' : 'none';
    document.getElementById('info-saldo-partida') && (
        document.getElementById('info-saldo-partida').style.color =
            (saldoPartida > 0 && total > saldoPartida) ? 'var(--accent-danger)' : 'var(--accent-3)'
    );
}

function calcular() {
    let sub = 0;
    document.querySelectorAll('#lineas-body tr').forEach(tr => {
        const cant = parseFloat(tr.querySelector('.cant')?.value) || 0;
        const pu   = parseFloat(tr.querySelector('.punit')?.value) || 0;
        const s    = cant * pu;
        const td   = tr.querySelector('.sub-td');
        if (td) td.textContent = s.toFixed(2);
        sub += s;
    });
    document.getElementById('td-total').textContent    = sub.toFixed(2);
    // Actualizar input oculto para el selector de retenciones
    const hiddenTotal = document.getElementById('total-para-retencion');
    if (hiddenTotal) {
        hiddenTotal.value = sub.toFixed(2);
        hiddenTotal.dispatchEvent(new Event('input'));
    }
    // Actualizar resumen (bruto siempre, luego retenciones via hook)
    actualizarResumenOC(sub, null);
    validarSaldo();
}

function actualizarResumenOC(bruto, retForzada) {
    if (bruto === null) bruto = parseFloat(document.getElementById('td-total').textContent || 0);
    const retTotalEl = document.getElementById('ret-total');
    let ret = 0;
    if (retForzada !== null && retForzada !== undefined) {
        ret = retForzada;
    } else if (retTotalEl) {
        ret = parseFloat(retTotalEl.getAttribute('data-valor') || retTotalEl.textContent.replace('Bs. ', '').replace(',', '.')) || 0;
    }
    const neto = Math.max(0, bruto - ret);
    const fmt  = v => 'Bs. ' + v.toLocaleString('es-VE', {minimumFractionDigits:2, maximumFractionDigits:2});

    const elBruto  = document.getElementById('res-oc-bruto');
    const elRet    = document.getElementById('res-oc-ret');
    const elRetRow = document.getElementById('res-oc-ret-row');
    const elNeto   = document.getElementById('res-oc-neto');

    if (elBruto)  elBruto.textContent  = fmt(bruto);
    if (elRet)    elRet.textContent    = fmt(ret);
    if (elRetRow) elRetRow.style.display = ret > 0 ? 'flex' : 'none';
    if (elNeto)   elNeto.textContent   = fmt(neto);
}

function autoDescripcion(sel, idx) {
    const opt = sel.options[sel.selectedIndex];
    if (!sel.value) return;
    const descEl = document.getElementById('desc-' + idx);
    const umEl   = document.getElementById('um-'   + idx);
    if (descEl && !descEl.value) descEl.value = opt.dataset.nombre || '';
    if (umEl)                    umEl.value   = opt.dataset.unidad  || 'unidad';
}

function agregarLinea() {
    const idx = c++;
    const tr  = document.createElement('tr');
    tr.id = 'linea-' + idx;
    tr.style.borderBottom = '1px solid var(--border)';
    tr.innerHTML = `
        <td style="padding:.4rem .3rem">
            <select name="lineas[${idx}][articulo_id]" class="form-control art-sel" style="font-size:.78rem" onchange="autoDescripcionDyn(this,${idx})">
                ${artOpts}
            </select>
        </td>
        <td style="padding:.4rem .3rem">
            <input type="text" name="lineas[${idx}][descripcion]" id="desc-${idx}" class="form-control" style="font-size:.78rem" required placeholder="Descripción del item...">
        </td>
        <td style="padding:.4rem .3rem">
            <input type="text" name="lineas[${idx}][unidad_medida]" id="um-${idx}" class="form-control" style="font-size:.78rem;text-align:center" value="unidad" placeholder="U/M">
        </td>
        <td style="padding:.4rem .3rem">
            <input type="number" name="lineas[${idx}][cantidad]" class="form-control cant" style="font-size:.78rem;text-align:right" value="1" step="0.01" min="0.01" required onchange="calcular()">
        </td>
        <td style="padding:.4rem .3rem">
            <input type="number" name="lineas[${idx}][precio_unitario]" class="form-control punit" style="font-size:.78rem;text-align:right" value="0" step="0.01" min="0" required onchange="calcular()" placeholder="0.00">
        </td>
        <td style="padding:.4rem .3rem;text-align:right;font-family:monospace;font-size:.82rem;white-space:nowrap" class="sub-td">0.00</td>
        <td style="padding:.3rem;text-align:center">
            <button type="button" onclick="this.closest('tr').remove();calcular()" class="btn-icon btn-icon-danger" title="Quitar">✕</button>
        </td>`;
    document.getElementById('lineas-body').appendChild(tr);
}

function autoDescripcionDyn(sel, idx) {
    const opt = sel.options[sel.selectedIndex];
    if (!sel.value) return;
    const descEl = document.getElementById('desc-' + idx);
    const umEl   = document.getElementById('um-'   + idx);
    if (descEl && !descEl.value) descEl.value = opt.dataset.nombre || '';
    if (umEl)                    umEl.value   = opt.dataset.unidad  || 'unidad';
}

// Hook: actualizar resumen cuando cambien las retenciones
const _calcRetOrigOC = window.calcularRetenciones;
window.calcularRetenciones = function() {
    if (typeof _calcRetOrigOC === 'function') _calcRetOrigOC();
    const retTotalEl = document.getElementById('ret-total');
    const ret = retTotalEl ? (parseFloat(retTotalEl.getAttribute('data-valor')) || 0) : 0;
    const bruto = parseFloat(document.getElementById('td-total').textContent || 0);
    actualizarResumenOC(bruto, ret);
};

// Init
window.addEventListener('DOMContentLoaded', () => {
    calcular();
    const selPartida = document.getElementById('sel-partida');
    if (selPartida?.value) mostrarSaldo(selPartida);
    const selBen = document.getElementById('sel-beneficiario');
    if (selBen?.value) autoRellenarProveedor(selBen);
});
</script>
@endsection
