@extends('layouts.app')
@section('title','Nueva Recepción de Bienes')
@section('content')
<div class="page-header">
    <div><h1 class="page-title">Nueva Recepción de Bienes</h1><p class="page-subtitle">Registro de entrega y actualización de stock automática</p></div>
    <a href="{{ route('compras.recepciones.index') }}" class="btn btn-ghost">← Volver</a>
</div>
@include('components.alert')
<form method="POST" action="{{ route('compras.recepciones.store') }}">
@csrf
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem">
    <div class="card" style="padding:1.5rem">
        <div style="display:grid;gap:1.1rem">
            <div class="form-group">
                <label class="form-label">Orden de Compra *</label>
                <select name="orden_compra_id" id="sel-orden" class="form-control" required onchange="cargarDetalle(this.value)">
                    <option value="">Seleccionar orden...</option>
                    @foreach($ordenes as $oc)
                    <option value="{{ $oc->id }}" {{ (request('orden')==$oc->id||old('orden_compra_id')==$oc->id)?'selected':'' }}>{{ $oc->numero }} — {{ Str::limit($oc->concepto,50) }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div class="form-group"><label class="form-label">Fecha Recepción *</label><input type="date" name="fecha_recepcion" class="form-control" required value="{{ old('fecha_recepcion',now()->toDateString()) }}"></div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-control">
                        <option value="conforme">Conforme</option>
                        <option value="parcial">Parcial</option>
                        <option value="no_conforme">No Conforme</option>
                    </select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div class="form-group"><label class="form-label">Recibido Por *</label><input type="text" name="recibido_por" class="form-control" required value="{{ old('recibido_por') }}"></div>
                <div class="form-group"><label class="form-label">Entregado Por (Proveedor)</label><input type="text" name="entregado_por" class="form-control" value="{{ old('entregado_por') }}"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div class="form-group"><label class="form-label">N° Guía de Despacho</label><input type="text" name="numero_guia" class="form-control" value="{{ old('numero_guia') }}"></div>
                <div class="form-group"><label class="form-label">N° Factura</label><input type="text" name="numero_factura" class="form-control" value="{{ old('numero_factura') }}"></div>
            </div>

            {{-- Renglones dinámicos de la OC --}}
            <div id="renglones-section" style="{{ !$orden?'display:none':'' }}">
                <h4 style="font-size:.85rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:.75rem">Cantidades Recibidas</h4>
                <table style="width:100%;border-collapse:collapse">
                    <thead><tr style="font-size:.75rem;text-transform:uppercase;color:var(--text-muted)">
                        <th style="padding:.4rem">Artículo</th>
                        <th style="padding:.4rem;text-align:right">Ord.</th>
                        <th style="padding:.4rem;text-align:right">Pend.</th>
                        <th style="padding:.4rem;text-align:right">Recibido *</th>
                        <th style="padding:.4rem;text-align:right">P.Unit.</th>
                        <th style="padding:.4rem">Condición</th>
                    </tr></thead>
                    <tbody id="renglon-body">
                        @if($orden)
                        @foreach($orden->detalles as $i=>$det)
                        <tr>
                            <td style="padding:.35rem">
                                @if($det->articulo)<code style="font-size:.75rem">{{ $det->articulo->codigo }}</code><br>@endif
                                <span style="font-size:.82rem">{{ $det->descripcion }}</span>
                                <input type="hidden" name="lineas[{{ $i }}][orden_detalle_id]" value="{{ $det->id }}">
                                <input type="hidden" name="lineas[{{ $i }}][articulo_id]" value="{{ $det->articulo_id }}">
                            </td>
                            <td style="padding:.35rem;text-align:right;font-family:monospace;font-size:.82rem">{{ number_format($det->cantidad,2) }}</td>
                            <td style="padding:.35rem;text-align:right;font-family:monospace;font-size:.82rem;color:var(--primary)">{{ number_format($det->getPendienteRecibir(),2) }}</td>
                            <td style="padding:.35rem"><input type="number" name="lineas[{{ $i }}][cantidad_recibida]" class="form-control" style="font-size:.82rem;text-align:right" value="{{ number_format($det->getPendienteRecibir(),2) }}" step="0.01" min="0" required></td>
                            <td style="padding:.35rem"><input type="number" name="lineas[{{ $i }}][precio_unitario]" class="form-control" style="font-size:.82rem;text-align:right" value="{{ $det->precio_unitario }}" step="0.01" min="0"></td>
                            <td style="padding:.35rem">
                                <select name="lineas[{{ $i }}][condicion]" class="form-control" style="font-size:.8rem">
                                    <option value="bueno">Bueno</option>
                                    <option value="malo">Malo</option>
                                    <option value="incompleto">Incompleto</option>
                                </select>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="form-group"><label class="form-label">Observaciones</label><textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones') }}</textarea></div>
        </div>
        <div style="display:flex;gap:.75rem;justify-content:flex-end;border-top:1px solid var(--border);padding-top:1rem;margin-top:1rem">
            <a href="{{ route('compras.recepciones.index') }}" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-primary">📦 Registrar Recepción</button>
        </div>
    </div>
    <div class="card" style="padding:1.2rem">
        <h4 style="font-size:.8rem;text-transform:uppercase;color:var(--text-muted);margin-bottom:.75rem">💡 Al registrar</h4>
        <div style="font-size:.82rem;color:var(--text-secondary);line-height:1.9">
            <p>✅ El stock de cada artículo se actualiza automáticamente</p>
            <p>📊 Se crea un movimiento en el Kardex</p>
            <p>🔄 Si se recibió todo, la OC queda <strong>Completada</strong></p>
        </div>
    </div>
</div>
</form>
<script>
const ordenesData = @json($ordenesData);
function cargarDetalle(ordenId) {
    const sec = document.getElementById('renglones-section');
    const body = document.getElementById('renglon-body');
    if (!ordenId) { sec.style.display='none'; return; }
    const oc = ordenesData[ordenId];
    if (!oc) return;
    sec.style.display='';
    body.innerHTML = '';
    oc.detalles.forEach((d,i) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td style="padding:.35rem">${d.codigo?`<code style="font-size:.75rem">${d.codigo}</code><br>`:''}
            <span style="font-size:.82rem">${d.descripcion}</span>
            <input type="hidden" name="lineas[${i}][orden_detalle_id]" value="${d.id}">
            <input type="hidden" name="lineas[${i}][articulo_id]" value="${d.articulo_id??''}">
        </td>
        <td style="padding:.35rem;text-align:right;font-family:monospace;font-size:.82rem">${parseFloat(d.cantidad).toFixed(2)}</td>
        <td style="padding:.35rem;text-align:right;font-family:monospace;font-size:.82rem;color:var(--primary)">${parseFloat(d.pendiente).toFixed(2)}</td>
        <td style="padding:.35rem"><input type="number" name="lineas[${i}][cantidad_recibida]" class="form-control" style="font-size:.82rem;text-align:right" value="${parseFloat(d.pendiente).toFixed(2)}" step="0.01" min="0" required></td>
        <td style="padding:.35rem"><input type="number" name="lineas[${i}][precio_unitario]" class="form-control" style="font-size:.82rem;text-align:right" value="${d.precio_unitario}" step="0.01" min="0"></td>
        <td style="padding:.35rem"><select name="lineas[${i}][condicion]" class="form-control" style="font-size:.8rem"><option value="bueno">Bueno</option><option value="malo">Malo</option><option value="incompleto">Incompleto</option></select></td>`;
        body.appendChild(tr);
    });
}
</script>
@endsection
