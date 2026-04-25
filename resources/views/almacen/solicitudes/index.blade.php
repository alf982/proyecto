@extends('layouts.app')
@section('title','Solicitudes de Artículos — Entregas a Oficinas')
@section('breadcrumb')
    <span>Almacén</span><span class="breadcrumb-sep">›</span><span class="current">Solicitudes de Artículos</span>
@endsection
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Solicitudes de Artículos</h1>
        <p class="page-subtitle">Pedidos de artículos del almacén por parte de las oficinas</p>
    </div>
    @can('almacen.solicitudes.crear')
    <a href="{{ route('almacen.solicitudes.create') }}" class="btn btn-primary" id="btn-nueva-solicitud">＋ Nueva Solicitud</a>
    @endcan
</div>
@include('components.alert')

{{-- KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem">
    <div class="card" style="padding:1.2rem;border-left:4px solid #3b82f6">
        <div style="font-size:.7rem;text-transform:uppercase;color:var(--text-muted)">📨 Pendientes</div>
        <div style="font-size:1.9rem;font-weight:700;color:#3b82f6">{{ $kpis['pendientes'] }}</div>
        <div style="font-size:.72rem;color:var(--text-muted)">esperando aprobación</div>
    </div>
    <div class="card" style="padding:1.2rem;border-left:4px solid #8b5cf6">
        <div style="font-size:.7rem;text-transform:uppercase;color:var(--text-muted)">✅ Aprobadas</div>
        <div style="font-size:1.9rem;font-weight:700;color:#8b5cf6">{{ $kpis['aprobadas'] }}</div>
        <div style="font-size:.72rem;color:var(--text-muted)">pendientes entrega</div>
    </div>
    <div class="card" style="padding:1.2rem;border-left:4px solid var(--success,#22c55e)">
        <div style="font-size:.7rem;text-transform:uppercase;color:var(--text-muted)">📦 Entregadas</div>
        <div style="font-size:1.9rem;font-weight:700;color:var(--success,#22c55e)">{{ $kpis['entregadas'] }}</div>
        <div style="font-size:.72rem;color:var(--text-muted)">completadas</div>
    </div>
    <div class="card" style="padding:1.2rem;border-left:4px solid #ef4444">
        <div style="font-size:.7rem;text-transform:uppercase;color:var(--text-muted)">❌ Rechazadas</div>
        <div style="font-size:1.9rem;font-weight:700;color:#ef4444">{{ $kpis['rechazadas'] }}</div>
        <div style="font-size:.72rem;color:var(--text-muted)">no procesadas</div>
    </div>
</div>

{{-- Alerta de pendientes de entrega --}}
@if($kpis['aprobadas'] > 0)
<div style="background:rgba(139,92,246,.08);border:1px solid rgba(139,92,246,.3);border-radius:10px;padding:.9rem 1.25rem;display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem">
    <span style="font-size:1.4rem">📦</span>
    <div>
        <div style="font-weight:600;color:#8b5cf6;font-size:.9rem">{{ $kpis['aprobadas'] }} solicitud{{ $kpis['aprobadas'] !== 1 ? 'es' : '' }} aprobada{{ $kpis['aprobadas'] !== 1 ? 's' : '' }} pendiente{{ $kpis['aprobadas'] !== 1 ? 's' : '' }} de entrega</div>
        <div style="font-size:.78rem;color:var(--text-muted)">Articulos aprobados que aún no han sido entregados físicamente a las oficinas</div>
    </div>
</div>
@endif

{{-- Filtros --}}
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
                <option value="enviada"    {{ request('estado')=='enviada'?'selected':'' }}>Enviada</option>
                <option value="aprobada"   {{ request('estado')=='aprobada'?'selected':'' }}>Aprobada</option>
                <option value="entregada"  {{ request('estado')=='entregada'?'selected':'' }}>Entregada</option>
                <option value="rechazada"  {{ request('estado')=='rechazada'?'selected':'' }}>Rechazada</option>
            </select>
        </div>
        <div class="form-group" style="min-width:150px">
            <label class="form-label">Prioridad</label>
            <select name="prioridad" class="form-control">
                <option value="">Todas</option>
                <option value="urgente" {{ request('prioridad')=='urgente'?'selected':'' }}>Urgente</option>
                <option value="alta"    {{ request('prioridad')=='alta'?'selected':'' }}>Alta</option>
                <option value="media"   {{ request('prioridad')=='media'?'selected':'' }}>Media</option>
                <option value="baja"    {{ request('prioridad')=='baja'?'selected':'' }}>Baja</option>
            </select>
        </div>
        <div class="form-group" style="min-width:180px">
            <label class="form-label">Unidad</label>
            <select name="unidad" class="form-control">
                <option value="">Todas</option>
                @foreach($unidades as $u)
                <option value="{{ $u->id }}" {{ request('unidad')==$u->id?'selected':'' }}>{{ $u->nombre }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">⚙ Filtrar</button>
        <a href="{{ route('almacen.solicitudes.index') }}" class="btn btn-ghost">Limpiar</a>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Unidad Solicitante</th>
                    <th>Motivo</th>
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
                    <td><a href="{{ route('almacen.solicitudes.show', $sol) }}" style="color:var(--primary);font-family:monospace;font-weight:600">{{ $sol->numero }}</a></td>
                    <td style="font-size:.85rem">{{ $sol->unidadEjecutora?->nombre }}</td>
                    <td style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.85rem">{{ $sol->motivo }}</td>
                    <td><span class="badge {{ $sol->getPrioridadBadge() }}" style="font-size:.7rem">{{ ucfirst($sol->prioridad) }}</span></td>
                    <td style="font-size:.8rem;color:var(--text-muted)">{{ $sol->fecha_requerida?->format('d/m/Y') ?? '—' }}</td>
                    <td><span class="badge {{ $sol->getEstadoBadge() }}" style="font-size:.72rem">{{ $sol->getEstadoLabel() }}</span></td>
                    <td style="font-size:.85rem">{{ $sol->solicitadoPor?->name ?? '—' }}</td>
                    <td>
                        <div style="display:flex;gap:.35rem;align-items:center">
                            <a href="{{ route('almacen.solicitudes.show', $sol) }}" class="btn-icon" title="Ver detalle">👁</a>
                            @can('almacen.solicitudes.aprobar')
                            @if($sol->esAprobada())
                            <a href="{{ route('almacen.solicitudes.show', $sol) }}" class="btn btn-primary" style="font-size:.72rem;padding:.25rem .6rem" title="Registrar entrega">📦 Entregar</a>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:3rem;color:var(--text-muted)">No hay solicitudes. <a href="{{ route('almacen.solicitudes.create') }}">Crear primera</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem">{{ $q->links() }}</div>
</div>
@endsection
