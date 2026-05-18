@extends('layouts.app')
@section('title','Solicitudes de Reabastecimiento')
@section('breadcrumb')
    <span>Compras</span><span class="breadcrumb-sep">›</span><span class="current">Solicitudes de Reabastecimiento</span>
@endsection
@section('content')

{{-- 
  VISTA INDEX DE SOLICITUDES DE COMPRA
  Bandeja de entrada para requisiciones internas.
  Muestra una alerta rápida en caso de que existan artículos agotados o bajo mínimo.
--}}

<div class="page-header">
    <div>
        <h1 class="page-title">Solicitudes de Reabastecimiento</h1>
        <p class="page-subtitle">Pedidos del almacén al departamento de compras para reponer artículos agotados</p>
    </div>
    @can('compras.solicitudes.crear')
    <a href="{{ route('compras.solicitudes.create') }}" class="btn btn-primary">＋ Nueva Solicitud</a>
    @endcan
</div>
@include('components.alert')

{{-- Banner de alerta de stock --}}
@if($bajoStock > 0 || $sinStock > 0)
<div style="display:flex;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap">
    @if($sinStock > 0)
    <div style="flex:1;min-width:200px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:10px;padding:.9rem 1.25rem;display:flex;align-items:center;gap:.75rem">
        <span style="font-size:1.4rem">🚫</span>
        <div>
            <div style="font-weight:600;color:var(--danger);font-size:.9rem">{{ $sinStock }} artículo{{ $sinStock !== 1 ? 's' : '' }} sin stock</div>
            <div style="font-size:.78rem;color:var(--text-muted)">Stock agotado — requieren reabastecimiento urgente</div>
        </div>
        @can('compras.solicitudes.crear')
        <a href="{{ route('compras.solicitudes.create') }}" style="margin-left:auto;font-size:.78rem;color:var(--danger);text-decoration:underline">Crear solicitud</a>
        @endcan
    </div>
    @endif
    @if($bajoStock > 0)
    <div style="flex:1;min-width:200px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);border-radius:10px;padding:.9rem 1.25rem;display:flex;align-items:center;gap:.75rem">
        <span style="font-size:1.4rem">⚠️</span>
        <div>
            <div style="font-weight:600;color:var(--warn,#d97706);font-size:.9rem">{{ $bajoStock }} artículo{{ $bajoStock !== 1 ? 's' : '' }} bajo mínimo</div>
            <div style="font-size:.78rem;color:var(--text-muted)">Stock por debajo del mínimo configurado</div>
        </div>
    </div>
    @endif
</div>
@endif

<div class="card" style="padding:1rem;margin-bottom:1.5rem">
    <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end">
        <div class="form-group" style="flex:2;min-width:200px">
            <label class="form-label">Buscar</label>
            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="N°, motivo...">
        </div>
        <div class="form-group" style="min-width:150px">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-control">
                <option value="">Todos</option>
                <option value="enviada"   {{ request('estado')=='enviada'?'selected':'' }}>Enviada</option>
                <option value="aprobada"  {{ request('estado')=='aprobada'?'selected':'' }}>Aprobada</option>
                <option value="rechazada" {{ request('estado')=='rechazada'?'selected':'' }}>Rechazada</option>
                <option value="procesada" {{ request('estado')=='procesada'?'selected':'' }}>Procesada</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">⚙ Filtrar</button>
        <a href="{{ route('compras.solicitudes.index') }}" class="btn btn-ghost">Limpiar</a>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Motivo</th>
                    <th>Artículos</th>
                    <th>Prioridad</th>
                    <th>F. Requerida</th>
                    <th>Estado</th>
                    <th>Solicitado por</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($q as $sol)
                <tr>
                    <td>
                        @can('compras.solicitudes.ver')
                        <a href="{{ route('compras.solicitudes.show', $sol) }}" style="color:var(--primary);font-family:monospace;font-weight:600">{{ $sol->numero }}</a>
                        @else
                        <span style="font-family:monospace;font-weight:600">{{ $sol->numero }}</span>
                        @endcan
                    </td>
                    <td style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.85rem">{{ $sol->motivo }}</td>
                    <td style="font-size:.85rem;color:var(--text-muted)">{{ $sol->detalles_count ?? '—' }} artículo(s)</td>
                    <td><span class="badge {{ $sol->getPrioridadBadge() }}" style="font-size:.7rem">{{ ucfirst($sol->prioridad) }}</span></td>
                    <td style="font-size:.8rem;color:var(--text-muted)">{{ $sol->fecha_requerida?->format('d/m/Y') ?? '—' }}</td>
                    <td><span class="badge {{ $sol->getEstadoBadge() }}">{{ ucfirst($sol->estado) }}</span></td>
                    <td style="font-size:.85rem">{{ $sol->solicitadoPor?->name ?? '—' }}</td>
                    <td>
                        @can('compras.solicitudes.ver')
                        <a href="{{ route('compras.solicitudes.show', $sol) }}" class="btn-icon" title="Ver">👁</a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:3rem;color:var(--text-muted)">No hay solicitudes. <a href="{{ route('compras.solicitudes.create') }}">Crear primera</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Componente estándar de paginación --}}
    <x-pagination :paginator="$q" />
</div>
@endsection
