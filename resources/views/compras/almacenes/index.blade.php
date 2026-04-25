@extends('layouts.app')
@section('title','Almacenes')
@section('breadcrumb')
    <span>Compras</span><span class="breadcrumb-sep">›</span><span class="current">Almacenes</span>
@endsection
@section('content')
<div class="page-header">
    <div><h1 class="page-title">Almacenes</h1><p class="page-subtitle">Registro de almacenes institucionales</p></div>
    <a href="{{ route('compras.almacenes.create') }}" class="btn btn-primary">＋ Nuevo Almacén</a>
</div>
@include('components.alert')
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Código</th><th>Nombre</th><th>Ubicación</th><th>Responsable</th><th>Artículos</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
                @forelse($almacenes as $a)
                <tr>
                    <td><code>{{ $a->codigo }}</code></td>
                    <td style="font-weight:600">
                        <a href="{{ route('compras.almacenes.show', $a) }}" style="color:var(--primary)">{{ $a->nombre }}</a>
                    </td>
                    <td style="color:var(--text-muted);font-size:.85rem">{{ $a->ubicacion ?? '—' }}</td>
                    <td style="font-size:.85rem">{{ $a->responsable ?? '—' }}</td>
                    <td style="text-align:center"><span style="font-weight:600">{{ $a->articulos_count }}</span></td>
                    <td><span class="badge {{ $a->activo?'badge-active':'badge-inactive' }}">{{ $a->activo?'Activo':'Inactivo' }}</span></td>
                    <td><a href="{{ route('compras.almacenes.edit',$a) }}" class="btn-icon" title="Editar">✏️</a></td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:2.5rem;color:var(--text-muted)">No hay almacenes. <a href="{{ route('compras.almacenes.create') }}">Crear primero</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
