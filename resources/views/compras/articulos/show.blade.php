@extends('layouts.app')
@section('title','Artículo — '.$articulo->nombre)
@section('breadcrumb')
    <span>Compras</span><span class="breadcrumb-sep">›</span>
    <a href="{{ route('compras.articulos.index') }}">Artículos</a>
    <span class="breadcrumb-sep">›</span><span class="current">{{ $articulo->codigo }}</span>
@endsection
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $articulo->nombre }}</h1>
        <p class="page-subtitle"><code>{{ $articulo->codigo }}</code> · {{ $articulo->getTipoLabel() }}</p>
    </div>
    <div style="display:flex;gap:.75rem">
        <a href="{{ route('compras.articulos.edit',$articulo) }}" class="btn btn-ghost">✏️ Editar</a>
        <a href="{{ route('compras.articulos.index') }}" class="btn btn-ghost">← Volver</a>
    </div>
</div>
@include('components.alert')

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">
    <div class="card" style="padding:1.5rem">
        <h3 style="font-size:.85rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:1rem">Información</h3>
        <dl style="display:grid;grid-template-columns:130px 1fr;gap:.5rem;font-size:.9rem">
            <dt style="color:var(--text-muted)">Código</dt>       <dd><code>{{ $articulo->codigo }}</code></dd>
            <dt style="color:var(--text-muted)">Nombre</dt>        <dd>{{ $articulo->nombre }}</dd>
            <dt style="color:var(--text-muted)">Tipo</dt>          <dd>{{ $articulo->getTipoLabel() }}</dd>
            <dt style="color:var(--text-muted)">Unidad</dt>        <dd>{{ $articulo->unidad_medida }}</dd>
            <dt style="color:var(--text-muted)">Categoría</dt>     <dd>{{ $articulo->categoria ?? '—' }}</dd>
            <dt style="color:var(--text-muted)">Almacén</dt>       <dd>{{ $articulo->almacen?->nombre ?? '—' }}</dd>
            <dt style="color:var(--text-muted)">Precio Ref.</dt>   <dd>Bs. {{ number_format($articulo->precio_referencia,2) }}</dd>
            <dt style="color:var(--text-muted)">Descripción</dt>   <dd>{{ $articulo->descripcion ?? '—' }}</dd>
        </dl>
    </div>
    <div style="display:grid;gap:1rem">
        <div class="card" style="padding:1.2rem;border-left:4px solid {{ $articulo->getBajoStock()?'#f59e0b':'var(--success)' }}">
            <div style="font-size:.7rem;text-transform:uppercase;color:var(--text-muted)">Stock Actual</div>
            <div style="font-size:2rem;font-weight:700;color:{{ $articulo->getBajoStock()?'#f59e0b':'var(--success)' }}">
                {{ number_format($articulo->stock_actual,2) }} <span style="font-size:1rem">{{ $articulo->unidad_medida }}</span>
            </div>
            @if($articulo->getBajoStock())<div style="font-size:.8rem;color:#f59e0b">⚠️ Bajo el mínimo de {{ number_format($articulo->stock_minimo,2) }}</div>@endif
        </div>
        {{-- Ajustar inventario --}}
        <div class="card" style="padding:1.2rem">
            <h4 style="font-size:.85rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:.75rem">Ajuste de Inventario</h4>
            <form method="POST" action="{{ route('compras.articulos.ajustar',$articulo) }}">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem">
                    <div class="form-group">
                        <label class="form-label" style="font-size:.8rem">Tipo</label>
                        <select name="tipo" class="form-control" style="font-size:.85rem">
                            <option value="entrada">↑ Entrada</option>
                            <option value="salida">↓ Salida</option>
                            <option value="ajuste">⟳ Ajuste</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size:.8rem">Cantidad</label>
                        <input type="number" name="cantidad" class="form-control" style="font-size:.85rem" step="0.01" min="0.01" required>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:.75rem">
                    <label class="form-label" style="font-size:.8rem">Concepto *</label>
                    <input type="text" name="concepto" class="form-control" style="font-size:.85rem" required placeholder="Motivo del ajuste...">
                </div>
                <button type="submit" class="btn btn-ghost" style="width:100%;font-size:.85rem">📦 Aplicar Ajuste</button>
            </form>
        </div>
    </div>
</div>

{{-- Kardex --}}
@if($movimientos->count())
<div class="card" style="margin-top:1.5rem">
    <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--border)">
        <h3 style="font-size:.9rem">Kardex — Movimientos de Inventario</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Fecha</th><th>Tipo</th><th>Concepto</th><th style="text-align:right">Cantidad</th><th style="text-align:right">Ant.</th><th style="text-align:right">Nuevo</th></tr></thead>
            <tbody>
                @foreach($movimientos as $mov)
                <tr>
                    <td>{{ $mov->fecha->format('d/m/Y') }}</td>
                    <td><span class="badge {{ $mov->getTipoBadge() }}" style="font-size:.7rem">{{ ucfirst($mov->tipo) }}</span></td>
                    <td style="font-size:.85rem">{{ $mov->concepto }}</td>
                    <td style="text-align:right;font-family:monospace;color:{{ $mov->tipo=='salida'?'var(--danger)':'var(--success)' }}">
                        {{ $mov->tipo=='salida'?'-':'+' }}{{ number_format($mov->cantidad,2) }}
                    </td>
                    <td style="text-align:right;font-family:monospace;color:var(--text-muted);font-size:.8rem">{{ number_format($mov->stock_anterior,2) }}</td>
                    <td style="text-align:right;font-family:monospace">{{ number_format($mov->stock_nuevo,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="padding:.75rem 1rem">{{ $movimientos->links() }}</div>
</div>
@endif
@endsection
