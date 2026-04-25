@extends('layouts.app')
@section('title','Almacén — '.$almacen->nombre)
@section('breadcrumb')
    <span>Compras</span><span class="breadcrumb-sep">›</span>
    <a href="{{ route('compras.almacenes.index') }}">Almacenes</a>
    <span class="breadcrumb-sep">›</span><span class="current">{{ $almacen->nombre }}</span>
@endsection
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $almacen->nombre }}</h1>
        <p class="page-subtitle"><code>{{ $almacen->codigo }}</code> · {{ $almacen->ubicacion ?? 'Sin ubicación registrada' }}</p>
    </div>
    <div style="display:flex;gap:.75rem;align-items:center">
        <span class="badge {{ $almacen->activo ? 'badge-active' : 'badge-inactive' }}">{{ $almacen->activo ? 'Activo' : 'Inactivo' }}</span>
        @can('compras.almacenes.editar')
        <a href="{{ route('compras.almacenes.edit', $almacen) }}" class="btn btn-ghost">✏️ Editar</a>
        @endcan
        <a href="{{ route('compras.almacenes.index') }}" class="btn btn-ghost">← Volver</a>
    </div>
</div>
@include('components.alert')

{{-- KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem">
    <div class="card" style="padding:1.2rem;border-left:4px solid var(--primary)">
        <div style="font-size:.7rem;text-transform:uppercase;color:var(--text-muted)">Total Artículos</div>
        <div style="font-size:1.8rem;font-weight:700;color:var(--primary)">{{ $stats['total'] }}</div>
    </div>
    <div class="card" style="padding:1.2rem;border-left:4px solid #f59e0b">
        <div style="font-size:.7rem;text-transform:uppercase;color:var(--text-muted)">⚠ Bajo Stock</div>
        <div style="font-size:1.8rem;font-weight:700;color:#f59e0b">{{ $stats['bajo_stock'] }}</div>
    </div>
    <div class="card" style="padding:1.2rem;border-left:4px solid #ef4444">
        <div style="font-size:.7rem;text-transform:uppercase;color:var(--text-muted)">🚫 Sin Stock</div>
        <div style="font-size:1.8rem;font-weight:700;color:#ef4444">{{ $stats['sin_stock'] }}</div>
    </div>
    <div class="card" style="padding:1.2rem;border-left:4px solid var(--success,#22c55e)">
        <div style="font-size:.7rem;text-transform:uppercase;color:var(--text-muted)">Valor Estimado</div>
        <div style="font-size:1.2rem;font-weight:700;color:var(--success,#22c55e);font-family:monospace">Bs. {{ number_format($stats['valor_total'],2) }}</div>
    </div>
</div>

{{-- Información del almacén --}}
@if($almacen->responsable)
<div class="card" style="padding:1rem 1.5rem;margin-bottom:1.5rem;display:flex;gap:2rem;flex-wrap:wrap">
    <div><span style="font-size:.75rem;color:var(--text-muted);text-transform:uppercase">Responsable</span><div style="font-weight:600;margin-top:.2rem">{{ $almacen->responsable }}</div></div>
    @if($almacen->ubicacion)<div><span style="font-size:.75rem;color:var(--text-muted);text-transform:uppercase">Ubicación</span><div style="margin-top:.2rem">{{ $almacen->ubicacion }}</div></div>@endif
</div>
@endif

{{-- Artículos del almacén --}}
<div class="card">
    <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
        <h3 style="font-size:.9rem">Artículos en este Almacén</h3>
        @can('compras.articulos.crear')
        <a href="{{ route('compras.articulos.create') }}" class="btn btn-primary" style="font-size:.82rem">＋ Nuevo Artículo</a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Unidad</th>
                    <th style="text-align:right">Stock Actual</th>
                    <th style="text-align:right">Mínimo</th>
                    <th style="text-align:right">Precio Ref.</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($articulos as $art)
                <tr>
                    <td><code style="font-size:.8rem">{{ $art->codigo }}</code></td>
                    <td>
                        <div style="font-weight:600">{{ $art->nombre }}</div>
                        @if($art->getBajoStock())<div style="font-size:.7rem;color:#f59e0b">⚠ Stock bajo</div>@endif
                        @if($art->stock_actual <= 0)<div style="font-size:.7rem;color:#ef4444">🚫 Sin stock</div>@endif
                    </td>
                    <td><span class="badge badge-info" style="font-size:.7rem">{{ $art->getTipoLabel() }}</span></td>
                    <td style="font-size:.85rem;color:var(--text-muted)">{{ $art->unidad_medida }}</td>
                    <td style="text-align:right;font-weight:700;color:{{ $art->stock_actual > 0 ? 'var(--success)' : 'var(--danger)' }}">
                        {{ number_format($art->stock_actual, 2) }}
                    </td>
                    <td style="text-align:right;color:var(--text-muted);font-size:.85rem">{{ number_format($art->stock_minimo, 2) }}</td>
                    <td style="text-align:right;font-family:monospace;font-size:.85rem">Bs. {{ number_format($art->precio_referencia, 2) }}</td>
                    <td>
                        <div style="display:flex;gap:.35rem">
                            <a href="{{ route('compras.articulos.show', $art) }}" class="btn-icon" title="Ver">👁</a>
                            @can('compras.articulos.editar')
                            <a href="{{ route('compras.articulos.edit', $art) }}" class="btn-icon" title="Editar">✏️</a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:3rem;color:var(--text-muted)">
                    No hay artículos en este almacén.
                    @can('compras.articulos.crear')<a href="{{ route('compras.articulos.create') }}">Agregar primero</a>@endcan
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem">{{ $articulos->links() }}</div>
</div>
@endsection
