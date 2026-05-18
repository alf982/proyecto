@extends('layouts.app')
@section('title','Catálogo de Artículos')
@section('breadcrumb')
    <span>Compras</span><span class="breadcrumb-sep">›</span><span class="current">Artículos</span>
@endsection
@section('content')

{{-- 
  VISTA INDEX DE ARTÍCULOS
  Muestra el catálogo principal de bienes, materiales, servicios y equipos.
  Incluye indicadores de stock bajo/agotado.
--}}

<div class="page-header">
    <div><h1 class="page-title">Catálogo de Artículos</h1><p class="page-subtitle">Bienes, materiales, servicios y equipos</p></div>
    @can('compras.articulos.crear')
    <a href="{{ route('compras.articulos.create') }}" class="btn btn-primary">＋ Nuevo Artículo</a>
    @endcan
</div>
@include('components.alert')

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem">
    <div class="card" style="padding:1.2rem;border-left:4px solid var(--primary)">
        <div style="font-size:.7rem;text-transform:uppercase;color:var(--text-muted)">Total Artículos</div>
        <div style="font-size:1.8rem;font-weight:700;color:var(--primary)">{{ $resumen['total'] }}</div>
    </div>
    <div class="card" style="padding:1.2rem;border-left:4px solid #f59e0b">
        <div style="font-size:.7rem;text-transform:uppercase;color:var(--text-muted)">⚠ Bajo Stock</div>
        <div style="font-size:1.8rem;font-weight:700;color:#f59e0b">{{ $resumen['bajo_stock'] }}</div>
    </div>
    <div class="card" style="padding:1.2rem;border-left:4px solid #ef4444">
        <div style="font-size:.7rem;text-transform:uppercase;color:var(--text-muted)">🚫 Sin Stock</div>
        <div style="font-size:1.8rem;font-weight:700;color:#ef4444">{{ $resumen['sin_stock'] }}</div>
    </div>
</div>

<div class="card" style="padding:1.2rem;margin-bottom:1.5rem">
    <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end">
        <div class="form-group" style="flex:2;min-width:200px">
            <label class="form-label">Buscar</label>
            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Código o nombre...">
        </div>
        <div class="form-group" style="min-width:150px">
            <label class="form-label">Tipo</label>
            <select name="tipo" class="form-control">
                <option value="">Todos</option>
                <option value="bien"     {{ request('tipo')=='bien'?'selected':'' }}>Bien</option>
                <option value="material" {{ request('tipo')=='material'?'selected':'' }}>Material</option>
                <option value="servicio" {{ request('tipo')=='servicio'?'selected':'' }}>Servicio</option>
                <option value="equipo"   {{ request('tipo')=='equipo'?'selected':'' }}>Equipo</option>
            </select>
        </div>
        <div class="form-group" style="display:flex;align-items:flex-end">
            <label style="display:flex;gap:.4rem;align-items:center;cursor:pointer;white-space:nowrap">
                <input type="checkbox" name="bajo_stock" value="1" {{ request('bajo_stock')?'checked':'' }} style="accent-color:var(--primary)">
                Solo bajo stock
            </label>
        </div>
        <div style="display:flex;gap:.5rem">
            <button type="submit" class="btn btn-primary">⚙ Filtrar</button>
            <a href="{{ route('compras.articulos.index') }}" class="btn btn-ghost">Limpiar</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr><th>Código</th><th>Nombre</th><th>Tipo</th><th>Unidad</th><th style="text-align:right">Stock</th><th style="text-align:right">Mínimo</th><th style="text-align:right">Precio Ref.</th><th>Almacén</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @forelse($q as $art)
                <tr class="{{ $art->getBajoStock() ? 'style=background:rgba(245,158,11,.05)' : '' }}">
                    <td><code style="font-size:.8rem">{{ $art->codigo }}</code></td>
                    <td>
                        <div style="font-weight:600">{{ $art->nombre }}</div>
                        @if($art->getBajoStock())<div style="font-size:.7rem;color:#f59e0b">⚠ Stock bajo</div>@endif
                    </td>
                    <td><span class="badge badge-info" style="font-size:.7rem">{{ $art->getTipoLabel() }}</span></td>
                    <td style="font-size:.85rem;color:var(--text-muted)">{{ $art->unidad_medida }}</td>
                    <td style="text-align:right;font-weight:700;color:{{ $art->stock_actual > 0 ?'var(--success)':'var(--danger)' }}">{{ number_format($art->stock_actual,2) }}</td>
                    <td style="text-align:right;color:var(--text-muted);font-size:.85rem">{{ number_format($art->stock_minimo,2) }}</td>
                    <td style="text-align:right;font-family:monospace;font-size:.85rem">Bs. {{ number_format($art->precio_referencia,2) }}</td>
                    <td style="font-size:.8rem;color:var(--text-muted)">{{ $art->almacen?->nombre ?? '—' }}</td>
                    <td>
                        <div style="display:flex;gap:.35rem">
                            @can('compras.articulos.ver')
                            <a href="{{ route('compras.articulos.show',$art) }}" class="btn-icon" title="Ver">👁</a>
                            @endcan
                            @can('compras.articulos.editar')
                            <a href="{{ route('compras.articulos.edit',$art) }}" class="btn-icon" title="Editar">✏️</a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;padding:3rem;color:var(--text-muted)">No hay artículos. <a href="{{ route('compras.articulos.create') }}">Crear primero</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Componente estándar de paginación --}}
    <x-pagination :paginator="$q" />
</div>
@endsection
