@extends('layouts.app')
@section('title','Recepciones de Bienes')
@section('content')

{{-- 
  VISTA INDEX DE RECEPCIÓN DE BIENES
  Bandeja de actas de recepción emitidas.
  Permite auditar el ingreso físico de mercancía al almacén.
--}}

<div class="page-header">
    <div><h1 class="page-title">Recepciones de Bienes</h1><p class="page-subtitle">Confirmación de entregas y actualización de inventario</p></div>
    @can('compras.recepciones.crear')
    <a href="{{ route('compras.recepciones.create') }}" class="btn btn-primary">＋ Nueva Recepción</a>
    @endcan
</div>
@include('components.alert')

<div class="card" style="padding:.9rem;margin-bottom:1.5rem">
    <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end">
        <div class="form-group" style="flex:2;min-width:200px"><label class="form-label">Buscar</label><input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="N° recepción, factura..."></div>
        <div class="form-group" style="min-width:150px">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-control">
                <option value="">Todos</option>
                <option value="conforme"    {{ request('estado')=='conforme'?'selected':'' }}>Conforme</option>
                <option value="parcial"     {{ request('estado')=='parcial'?'selected':'' }}>Parcial</option>
                <option value="no_conforme" {{ request('estado')=='no_conforme'?'selected':'' }}>No Conforme</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">⚙ Filtrar</button>
        <a href="{{ route('compras.recepciones.index') }}" class="btn btn-ghost">Limpiar</a>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>N°</th><th>Orden de Compra</th><th>Fecha</th><th>N° Factura</th><th>Recibido Por</th><th style="text-align:right">Total</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
                @forelse($q as $rec)
                <tr>
                    <td>
                        @can('compras.recepciones.ver')
                        <a href="{{ route('compras.recepciones.show',$rec) }}" style="color:var(--primary);font-family:monospace;font-weight:600">{{ $rec->numero }}</a>
                        @else
                        <span style="font-family:monospace;font-weight:600">{{ $rec->numero }}</span>
                        @endcan
                    </td>
                    <td><code style="font-size:.8rem">{{ $rec->orden?->numero }}</code></td>
                    <td style="font-size:.85rem">{{ $rec->fecha_recepcion->format('d/m/Y') }}</td>
                    <td style="font-size:.8rem;color:var(--text-muted)">{{ $rec->numero_factura ?? '—' }}</td>
                    <td style="font-size:.85rem">{{ $rec->recibido_por }}</td>
                    <td style="text-align:right;font-family:monospace;font-weight:600">Bs. {{ number_format($rec->total_recibido,2) }}</td>
                    <td><span class="badge {{ $rec->getEstadoBadge() }}" style="font-size:.7rem">{{ ucfirst($rec->estado) }}</span></td>
                    <td>
                        @can('compras.recepciones.ver')
                        <a href="{{ route('compras.recepciones.show',$rec) }}" class="btn-icon" title="Ver">👁</a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:3rem;color:var(--text-muted)">Sin recepciones registradas. <a href="{{ route('compras.recepciones.create') }}">Registrar primera</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Componente estándar de paginación --}}
    <x-pagination :paginator="$q" />
</div>
@endsection
