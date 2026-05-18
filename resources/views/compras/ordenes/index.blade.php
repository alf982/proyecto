@extends('layouts.app')
@section('title','Órdenes de Compra')
@section('breadcrumb')
    <span>Compras</span><span class="breadcrumb-sep">›</span><span class="current">Órdenes de Compra</span>
@endsection
@section('content')

{{-- 
  VISTA INDEX DE ÓRDENES DE COMPRA
  Muestra el catálogo de órdenes de compra emitidas a proveedores.
  Permite el seguimiento del estado de la adjudicación.
--}}

<div class="page-header">
    <div><h1 class="page-title">Órdenes de Compra</h1><p class="page-subtitle">Documentos formales de compra emitidos</p></div>
    @can('compras.ordenes.crear')
    <a href="{{ route('compras.ordenes.create') }}" class="btn btn-primary">＋ Nueva Orden</a>
    @endcan
</div>
@include('components.alert')

<div class="card" style="padding:1rem;margin-bottom:1.5rem">
    <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end">
        <div class="form-group" style="flex:2;min-width:200px"><label class="form-label">Buscar</label><input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="N°, concepto, proveedor..."></div>
        <div class="form-group" style="min-width:150px">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-control">
                <option value="">Todos</option>
                <option value="emitida"      {{ request('estado')=='emitida'?'selected':'' }}>Emitida</option>
                <option value="confirmada"   {{ request('estado')=='confirmada'?'selected':'' }}>Confirmada</option>
                <option value="en_transito"  {{ request('estado')=='en_transito'?'selected':'' }}>En Tránsito</option>
                <option value="completada"   {{ request('estado')=='completada'?'selected':'' }}>Completada</option>
                <option value="anulada"      {{ request('estado')=='anulada'?'selected':'' }}>Anulada</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">⚙ Filtrar</button>
        <a href="{{ route('compras.ordenes.index') }}" class="btn btn-ghost">Limpiar</a>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>N°</th><th>Fecha</th><th>Proveedor</th><th>Concepto</th><th style="text-align:right">Total Bruto</th><th style="text-align:right">Retenciones</th><th style="text-align:right">Monto Neto</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
                @forelse($q as $oc)
                <tr class="{{ $oc->estado=='anulada'?'opacity:0.5':'' }}">
                    <td>
                        @can('compras.ordenes.ver')
                        <a href="{{ route('compras.ordenes.show',$oc) }}" style="color:var(--primary);font-family:monospace;font-weight:600">{{ $oc->numero }}</a>
                        @else
                        <span style="font-family:monospace;font-weight:600">{{ $oc->numero }}</span>
                        @endcan
                    </td>
                    <td style="font-size:.85rem">{{ $oc->fecha_emision->format('d/m/Y') }}</td>
                    <td style="font-size:.85rem">{{ $oc->proveedor_nombre ?? $oc->beneficiario?->nombre ?? '—' }}</td>
                    <td style="max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.85rem">{{ $oc->concepto }}</td>
                    <td style="text-align:right;font-family:monospace;font-size:.85rem">Bs. {{ number_format($oc->total,2) }}</td>
                    <td style="text-align:right;font-family:monospace;font-size:.85rem;color:var(--accent-danger)">{{ $oc->monto_retencion > 0 ? 'Bs. '.number_format($oc->monto_retencion,2) : '—' }}</td>
                    <td style="text-align:right;font-family:monospace;font-weight:700;color:var(--accent-3)">Bs. {{ number_format($oc->monto_neto,2) }}</td>
                    <td><span class="badge {{ $oc->getEstadoBadge() }}">{{ $oc->getEstadoLabel() }}</span></td>
                    <td>
                        @can('compras.ordenes.ver')
                        <a href="{{ route('compras.ordenes.show',$oc) }}" class="btn-icon" title="Ver">👁</a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:3rem;color:var(--text-muted)">No hay órdenes. <a href="{{ route('compras.ordenes.create') }}">Crear primera</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Componente estándar de paginación --}}
    <x-pagination :paginator="$q" />
</div>
@endsection
