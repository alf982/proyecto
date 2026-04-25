@extends('layouts.app')
@section('title', 'Categorías de Bienes')
@section('breadcrumb')
    <span>Bienes Nacionales</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Categorías</span>
@endsection
@section('content')
<div class="page-header fade-up">
    <h1 class="page-title">Categorías de Bienes</h1>
    <p class="page-subtitle">Clasificación de activos fijos con tasas de depreciación configurables</p>
</div>
<div class="card fade-up">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-tags" style="color:var(--accent);margin-right:8px;"></i>Listado</div>
        <a href="{{ route('bienes.categorias.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nueva Categoría</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Código</th><th>Nombre</th><th>Vida Útil</th><th>Tasa Deprec.</th><th>Bienes</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            @forelse($categorias as $c)
            <tr>
                <td><code style="color:var(--accent);font-size:12px;">{{ $c->codigo }}</code></td>
                <td>{{ $c->nombre }}</td>
                <td>{{ $c->vida_util_anios }} años</td>
                <td><strong>{{ $c->tasa_depreciacion }}%</strong> anual</td>
                <td><span class="badge badge-blue">{{ $c->bienes_count }}</span></td>
                <td><span class="badge {{ $c->activo ? 'badge-active' : 'badge-danger' }}">{{ $c->activo ? 'Activo' : 'Inactivo' }}</span></td>
                <td><a href="{{ route('bienes.categorias.edit', $c) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i></a></td>
            </tr>
            @empty
            <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">🏷️</div><div class="empty-title">Sin categorías</div></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
