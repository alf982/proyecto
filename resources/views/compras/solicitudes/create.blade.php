@extends('layouts.app')
@section('title','Nueva Solicitud de Reabastecimiento')
@section('breadcrumb')
    <span>Compras</span><span class="breadcrumb-sep">›</span>
    <a href="{{ route('compras.solicitudes.index') }}">Solicitudes</a>
    <span class="breadcrumb-sep">›</span><span class="current">Nueva</span>
@endsection
@section('content')
<div class="page-header">
    <div><h1 class="page-title">Nueva Solicitud de Reabastecimiento</h1><p class="page-subtitle">Pedido de compra para reponer artículos agotados o bajo mínimo</p></div>
    <a href="{{ route('compras.solicitudes.index') }}" class="btn btn-ghost">← Volver</a>
</div>
@include('components.alert')

<form method="POST" action="{{ route('compras.solicitudes.store') }}" id="form-sol-compra">
@csrf
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start">

    <div class="card" style="padding:1.5rem">
        <div style="display:grid;gap:1.1rem">

            <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:1rem">
                <div class="form-group">
                    <label class="form-label">Justificación / Motivo *</label>
                    <textarea name="motivo" class="form-control" rows="2" required placeholder="Ej: Reabastecimiento mensual de materiales de oficina...">{{ old('motivo') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Prioridad *</label>
                    <select name="prioridad" class="form-control" required>
                        <option value="baja"    {{ old('prioridad')=='baja'?'selected':'' }}>Baja</option>
                        <option value="media"   {{ old('prioridad','media')=='media'?'selected':'' }}>Media</option>
                        <option value="alta"    {{ old('prioridad')=='alta'?'selected':'' }}>Alta</option>
                        <option value="urgente" {{ old('prioridad')=='urgente'?'selected':'' }}>Urgente</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha Requerida</label>
                    <input type="date" name="fecha_requerida" class="form-control" value="{{ old('fecha_requerida') }}" min="{{ date('Y-m-d') }}">
                </div>
            </div>

            {{-- Artículos bajo stock --}}
            @if($agotados->isNotEmpty())
            <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);border-radius:8px;padding:.9rem 1rem">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.6rem">
                    <strong style="font-size:.82rem;color:var(--warn,#d97706)">⚠ Artículos que requieren reabastecimiento ({{ $agotados->count() }})</strong>
                    <button type="button" onclick="agregarTodosAgotados()" class="btn btn-ghost" style="font-size:.75rem;padding:.3rem .7rem">
                        ＋ Agregar todos
                    </button>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:.4rem" id="lista-agotados">
                    @foreach($agotados as $art)
                    <button type="button"
                        onclick="agregarDesdeAgotado({{ $art->id }}, '{{ addslashes($art->codigo) }}', '{{ addslashes($art->nombre) }}', '{{ $art->unidad_medida }}', {{ $art->stock_actual }}, {{ $art->stock_minimo }})"
                        class="btn btn-ghost"
                        id="btn-agotado-{{ $art->id }}"
                        style="font-size:.73rem;padding:.25rem .6rem;border-color:rgba(245,158,11,.4);color:var(--text-secondary)">
                        {{ $art->stock_actual <= 0 ? '🚫' : '⚠' }} {{ $art->nombre }}
                        <span style="color:var(--text-muted)">({{ number_format($art->stock_actual,1) }}/{{ number_format($art->stock_minimo,1) }})</span>
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Tabla de líneas --}}
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem">
                    <strong style="font-size:.9rem">Artículos a Comprar</strong>
                    <button type="button" onclick="agregarLinea()" class="btn btn-ghost" style="font-size:.8rem">＋ Agregar artículo</button>
                </div>
                <table style="width:100%;border-collapse:collapse" id="tabla-lineas">
                    <thead>
                        <tr style="font-size:.75rem;text-transform:uppercase;color:var(--text-muted)">
                            <th style="padding:.4rem;text-align:left">Artículo *</th>
                            <th style="padding:.4rem;width:9%;text-align:center">Stock Act.</th>
                            <th style="padding:.4rem;width:9%;text-align:center">Mínimo</th>
                            <th style="padding:.4rem;width:30%;text-align:left">Descripción / Especificación *</th>
                            <th style="padding:.4rem;width:6%;text-align:center">U/M</th>
                            <th style="padding:.4rem;width:9%;text-align:right">Cantidad *</th>
                            <th style="padding:.4rem;width:10%;text-align:right">P. Estimado</th>
                            <th style="padding:.4rem;width:4%"></th>
                        </tr>
                    </thead>
                    <tbody id="lineas-body">
                        <tr id="fila-vacia" style="font-size:.82rem">
                            <td colspan="8" style="text-align:center;padding:2rem;color:var(--text-muted)">
                                Seleccione artículos del panel de alerta o use "＋ Agregar artículo"
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="form-group">
                <label class="form-label">Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="2" placeholder="Proveedor sugerido, especificaciones técnicas adicionales...">{{ old('observaciones') }}</textarea>
            </div>
        </div>

        <div style="display:flex;gap:.75rem;justify-content:flex-end;border-top:1px solid var(--border);padding-top:1rem;margin-top:1rem">
            <a href="{{ route('compras.solicitudes.index') }}" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-primary">📨 Enviar Solicitud</button>
        </div>
    </div>

    <div class="card" style="padding:1.2rem">
        <h4 style="font-size:.8rem;text-transform:uppercase;color:var(--text-muted);margin-bottom:.75rem">Flujo de Compra</h4>
        <div style="font-size:.82rem;color:var(--text-secondary);line-height:2">
            <div>📋 <strong>Solicitud</strong> → Registrada</div>
            <div>✅ <strong>Aprobada</strong> → Lista para OC</div>
            <div>📦 <strong>Orden de Compra</strong> → Al proveedor</div>
            <div>🚚 <strong>Recepción</strong> → Stock repuesto</div>
        </div>
        <hr style="border-color:var(--border);margin:1rem 0">
        <h4 style="font-size:.8rem;text-transform:uppercase;color:var(--text-muted);margin-bottom:.5rem">Catálogo disponible</h4>
        <div id="buscador-art" style="position:relative">
            <input type="text" id="buscar-art" class="form-control" style="font-size:.8rem" placeholder="🔍 Buscar artículo..." oninput="filtrarArticulos(this.value)">
            <div id="lista-art" style="max-height:220px;overflow-y:auto;margin-top:.4rem;display:none;border:1px solid var(--border);border-radius:6px;background:var(--card-bg)"></div>
        </div>
    </div>
</div>
</form>

<script>
const todosArticulos = {!! $articulosJson !!};

const agotadosData = {!! $agotadosJson !!};

let linCount = 0;
const lineasAgregadas = new Set(); // track art IDs ya en la tabla

function filaVacia() {
    const fv = document.getElementById('fila-vacia');
    if (fv) fv.style.display = linCount === 0 ? '' : 'none';
}

function agregarLinea(art = null) {
    if (art && lineasAgregadas.has(art.id)) {
        // Ya está — solo resaltar
        const row = document.getElementById('row-'+art.id);
        if (row) { row.style.background='rgba(79,142,247,.1)'; setTimeout(()=>row.style.background='',1200); }
        return;
    }
    const idx = linCount++;
    const artId = art?.id ?? '';
    const options = todosArticulos.map(a =>
        `<option value="${a.id}" data-stock="${a.stock}" data-minimo="${a.minimo}" data-unidad="${a.unidad}" ${artId==a.id?'selected':''}>`+
        `${a.codigo} — ${a.nombre}</option>`
    ).join('');
    const cantSugerida = art ? Math.max(1, art.minimo - art.stock + (art.minimo * 0.5 | 0)) : 1;
    const tr = document.createElement('tr');
    tr.id = art ? 'row-'+art.id : 'row-new-'+idx;
    if (art) lineasAgregadas.add(art.id);
    tr.innerHTML = `
        <td style="padding:.3rem .2rem">
            <select name="lineas[${idx}][articulo_id]" class="form-control art-sel" style="font-size:.78rem" required onchange="onChangeArt(this,${idx})">
                <option value="">— Seleccione —</option>${options}
            </select>
        </td>
        <td style="padding:.3rem;text-align:center">
            <span id="stk-${idx}" style="font-size:.75rem;color:var(--text-muted)">${art ? parseFloat(art.stock).toFixed(1)+' '+art.unidad : '—'}</span>
        </td>
        <td style="padding:.3rem;text-align:center">
            <span id="min-${idx}" style="font-size:.75rem;color:var(--text-muted)">${art ? parseFloat(art.minimo).toFixed(1) : '—'}</span>
        </td>
        <td style="padding:.3rem .2rem">
            <input type="text" name="lineas[${idx}][descripcion]" class="form-control" style="font-size:.78rem" required
                placeholder="Descripción / especificación..." value="${art ? art.nombre : ''}">
            <input type="hidden" name="lineas[${idx}][unidad_medida]" id="um-${idx}" value="${art?.unidad ?? 'unidad'}">
        </td>
        <td style="padding:.3rem;text-align:center">
            <span id="umtxt-${idx}" style="font-size:.75rem;color:var(--text-muted)">${art?.unidad ?? '—'}</span>
        </td>
        <td style="padding:.3rem .2rem">
            <input type="number" name="lineas[${idx}][cantidad]" class="form-control" style="font-size:.78rem;text-align:right"
                value="${cantSugerida}" min="0.01" step="0.01" required>
        </td>
        <td style="padding:.3rem .2rem">
            <input type="number" name="lineas[${idx}][precio_estimado]" class="form-control" style="font-size:.78rem;text-align:right"
                value="0" min="0" step="0.01">
        </td>
        <td style="padding:.3rem;text-align:center">
            <button type="button" onclick="quitarLinea(this,'${art?.id??''}')" class="btn-icon btn-icon-danger" title="Quitar">✕</button>
        </td>`;
    document.getElementById('lineas-body').appendChild(tr);
    filaVacia();
    // Ocultar botón en lista de agotados
    if (art) { const btn = document.getElementById('btn-agotado-'+art.id); if (btn) btn.style.opacity='.35'; }
}

function quitarLinea(btn, artId) {
    btn.closest('tr').remove();
    linCount = Math.max(0, linCount - 1);
    if (artId) { lineasAgregadas.delete(parseInt(artId)); const b = document.getElementById('btn-agotado-'+artId); if (b) b.style.opacity='1'; }
    filaVacia();
}

function onChangeArt(sel, idx) {
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('stk-'+idx).textContent  = opt?.dataset?.stock  ? parseFloat(opt.dataset.stock).toFixed(1)+' '+opt.dataset.unidad  : '—';
    document.getElementById('min-'+idx).textContent  = opt?.dataset?.minimo ? parseFloat(opt.dataset.minimo).toFixed(1) : '—';
    document.getElementById('um-'+idx).value         = opt?.dataset?.unidad ?? 'unidad';
    document.getElementById('umtxt-'+idx).textContent= opt?.dataset?.unidad ?? '—';
}

function agregarDesdeAgotado(id, codigo, nombre, unidad, stock, minimo) {
    agregarLinea({id, codigo, nombre, unidad, stock, minimo});
}

function agregarTodosAgotados() {
    agotadosData.forEach(a => agregarLinea(a));
}

// Buscador lateral
function filtrarArticulos(q) {
    const lista = document.getElementById('lista-art');
    const term  = q.toLowerCase().trim();
    if (!term) { lista.style.display='none'; return; }
    const matches = todosArticulos.filter(a => a.nombre.toLowerCase().includes(term) || a.codigo.toLowerCase().includes(term));
    lista.innerHTML = matches.length
        ? matches.map(a => `<div onclick="agregarLinea(${JSON.stringify(a).replace(/"/g,'&quot;')})" style="padding:.5rem .75rem;cursor:pointer;font-size:.8rem;border-bottom:1px solid var(--border)" onmouseover="this.style.background='rgba(79,142,247,.08)'" onmouseout="this.style.background=''">
            <code style="font-size:.7rem;color:var(--text-muted)">${a.codigo}</code> ${a.nombre}
            <span style="float:right;font-size:.72rem;color:var(--text-muted)">${parseFloat(a.stock).toFixed(1)} ${a.unidad}</span>
          </div>`).join('')
        : `<div style="padding:.75rem;font-size:.8rem;color:var(--text-muted);text-align:center">Sin resultados</div>`;
    lista.style.display = 'block';
}

document.addEventListener('click', e => {
    if (!document.getElementById('buscador-art').contains(e.target))
        document.getElementById('lista-art').style.display = 'none';
});
filaVacia();
</script>
@endsection
