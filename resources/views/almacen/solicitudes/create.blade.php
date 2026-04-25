@extends('layouts.app')
@section('title','Nueva Solicitud de Despacho')
@section('breadcrumb')
    <span>Almacén</span><span class="breadcrumb-sep">›</span>
    <a href="{{ route('almacen.solicitudes.index') }}">Solicitudes de Despacho</a>
    <span class="breadcrumb-sep">›</span><span class="current">Nueva</span>
@endsection
@section('content')
<div class="page-header">
    <div><h1 class="page-title">Nueva Solicitud de Despacho</h1><p class="page-subtitle">Solicite artículos del inventario del almacén</p></div>
    <a href="{{ route('almacen.solicitudes.index') }}" class="btn btn-ghost">← Volver</a>
</div>
@include('components.alert')

<form method="POST" action="{{ route('almacen.solicitudes.store') }}" id="form-despacho">
@csrf
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start">

    {{-- Panel principal --}}
    <div class="card" style="padding:1.5rem">
        <div style="display:grid;gap:1.1rem">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div class="form-group">
                    <label class="form-label">Unidad Solicitante *</label>
                    <select name="unidad_ejecutora_id" class="form-control" required>
                        <option value="">Seleccionar...</option>
                        @foreach($unidades as $u)
                        <option value="{{ $u->id }}" {{ old('unidad_ejecutora_id')==$u->id?'selected':'' }}>{{ $u->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha Requerida</label>
                    <input type="date" name="fecha_requerida" class="form-control" value="{{ old('fecha_requerida') }}" min="{{ date('Y-m-d') }}">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem">
                <div class="form-group">
                    <label class="form-label">Justificación / Motivo *</label>
                    <textarea name="motivo" class="form-control" rows="2" required placeholder="Indique el motivo del despacho...">{{ old('motivo') }}</textarea>
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
            </div>

            {{-- Tabla de artículos --}}
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem">
                    <strong style="font-size:.9rem">Artículos a Despachar</strong>
                    <button type="button" onclick="agregarLinea()" class="btn btn-ghost" style="font-size:.8rem">＋ Agregar artículo</button>
                </div>

                @if(count($articulos) === 0)
                <div style="padding:1.5rem;text-align:center;color:var(--text-muted);background:rgba(0,0,0,.04);border-radius:8px;font-size:.85rem">
                    ⚠ No hay artículos con stock disponible en este momento.
                </div>
                @else
                <table style="width:100%;border-collapse:collapse" id="tabla-lineas">
                    <thead>
                        <tr style="font-size:.75rem;text-transform:uppercase;color:var(--text-muted)">
                            <th style="padding:.4rem;text-align:left">Artículo *</th>
                            <th style="padding:.4rem;width:10%;text-align:center">Stock Disp.</th>
                            <th style="padding:.4rem;width:12%;text-align:right">Cantidad *</th>
                            <th style="padding:.4rem;width:22%;text-align:left">Observación</th>
                            <th style="padding:.4rem;width:5%"></th>
                        </tr>
                    </thead>
                    <tbody id="lineas-body">
                        <tr class="linea" id="linea-0">
                            <td style="padding:.3rem .2rem">
                                <select name="lineas[0][articulo_id]" class="form-control art-select" style="font-size:.8rem" required onchange="actualizarStock(this,0)">
                                    <option value="">— Seleccione —</option>
                                    @foreach($articulos as $art)
                                    <option value="{{ $art->id }}" data-stock="{{ $art->stock_actual }}" data-unidad="{{ $art->unidad_medida }}">
                                        {{ $art->codigo }} — {{ $art->nombre }} ({{ number_format($art->stock_actual,2) }} {{ $art->unidad_medida }})
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="padding:.3rem .2rem;text-align:center">
                                <span id="stock-0" style="font-size:.78rem;color:var(--text-muted)">—</span>
                            </td>
                            <td style="padding:.3rem .2rem">
                                <input type="number" name="lineas[0][cantidad_solicitada]" id="cant-0" class="form-control" style="font-size:.8rem;text-align:right" value="1" min="0.01" step="0.01" required>
                            </td>
                            <td style="padding:.3rem .2rem">
                                <input type="text" name="lineas[0][observacion]" class="form-control" style="font-size:.8rem" placeholder="Opcional...">
                            </td>
                            <td style="padding:.3rem;text-align:center"></td>
                        </tr>
                    </tbody>
                </table>
                @endif
            </div>

            <div class="form-group">
                <label class="form-label">Observaciones Generales</label>
                <textarea name="observaciones" class="form-control" rows="2" placeholder="Información adicional...">{{ old('observaciones') }}</textarea>
            </div>
        </div>

        <div style="display:flex;gap:.75rem;justify-content:flex-end;border-top:1px solid var(--border);padding-top:1rem;margin-top:1rem">
            <a href="{{ route('almacen.solicitudes.index') }}" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-primary">📨 Enviar Solicitud</button>
        </div>
    </div>

    {{-- Panel lateral --}}
    <div class="card" style="padding:1.2rem">
        <h4 style="font-size:.8rem;text-transform:uppercase;color:var(--text-muted);margin-bottom:.75rem">Flujo de la Solicitud</h4>
        <div style="font-size:.82rem;color:var(--text-secondary);line-height:2.2">
            <div>📨 <strong>1. Enviar</strong> → La oficina registra la solicitud</div>
            <div>✅ <strong>2. Aprobar</strong> → Almacén revisa y aprueba</div>
            <div>📦 <strong>3. Entrega</strong> → Almacén entrega y registra en sistema</div>
            <div>❌ <strong>Rechazar</strong> → Almacén indica el motivo</div>
        </div>
        <hr style="border-color:var(--border);margin:1rem 0">
        <h4 style="font-size:.8rem;text-transform:uppercase;color:var(--text-muted);margin-bottom:.5rem">Nota Importante</h4>
        <p style="font-size:.78rem;color:var(--text-muted);line-height:1.6">
            El stock <strong>NO se descuenta</strong> al enviar la solicitud. Solo se descuenta cuando el personal de almacén registra la entrega física de los artículos.
        </p>
    </div>

</div>
</form>

<script>
@php
$articulosJson = $articulos->map(function($a) {
    return [
        'id'     => $a->id,
        'codigo' => $a->codigo,
        'nombre' => $a->nombre,
        'stock'  => $a->stock_actual,
        'unidad' => $a->unidad_medida,
    ];
})->values()->all();
@endphp
// Opciones de artículos para filas nuevas
const articulosData = @json($articulosJson);

let linCount = 1;

function actualizarStock(select, idx) {
    const opt   = select.options[select.selectedIndex];
    const stock = opt?.dataset?.stock;
    const unidad= opt?.dataset?.unidad ?? '';
    const span  = document.getElementById('stock-'+idx);
    const cant  = document.getElementById('cant-'+idx);
    if (span) span.textContent = stock ? parseFloat(stock).toFixed(2) + ' ' + unidad : '—';
    if (cant && stock) cant.max = parseFloat(stock);
}

function agregarLinea() {
    const idx = linCount++;
    const options = articulosData.map(a =>
        `<option value="${a.id}" data-stock="${a.stock}" data-unidad="${a.unidad}">${a.codigo} — ${a.nombre} (${parseFloat(a.stock).toFixed(2)} ${a.unidad})</option>`
    ).join('');
    const tr = document.createElement('tr');
    tr.className = 'linea'; tr.id = 'linea-'+idx;
    tr.innerHTML = `
        <td style="padding:.3rem .2rem">
            <select name="lineas[${idx}][articulo_id]" class="form-control art-select" style="font-size:.8rem" required onchange="actualizarStock(this,${idx})">
                <option value="">— Seleccione —</option>${options}
            </select>
        </td>
        <td style="padding:.3rem .2rem;text-align:center">
            <span id="stock-${idx}" style="font-size:.78rem;color:var(--text-muted)">—</span>
        </td>
        <td style="padding:.3rem .2rem">
            <input type="number" name="lineas[${idx}][cantidad_solicitada]" id="cant-${idx}" class="form-control" style="font-size:.8rem;text-align:right" value="1" min="0.01" step="0.01" required>
        </td>
        <td style="padding:.3rem .2rem">
            <input type="text" name="lineas[${idx}][observacion]" class="form-control" style="font-size:.8rem" placeholder="Opcional...">
        </td>
        <td style="padding:.3rem;text-align:center">
            <button type="button" onclick="this.closest('tr').remove()" class="btn-icon btn-icon-danger" title="Eliminar">✕</button>
        </td>`;
    document.getElementById('lineas-body').appendChild(tr);
}
</script>
@endsection
