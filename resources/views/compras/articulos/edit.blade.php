@extends('layouts.app')
@section('title','Editar Artículo')
@section('breadcrumb')
    <span>Compras</span><span class="breadcrumb-sep">›</span>
    <a href="{{ route('compras.articulos.index') }}">Artículos</a>
    <span class="breadcrumb-sep">›</span><span class="current">Editar</span>
@endsection
@section('content')
<div class="page-header">
    <div><h1 class="page-title">Editar Artículo</h1><p class="page-subtitle">{{ $articulo->nombre }}</p></div>
    <a href="{{ route('compras.articulos.show',$articulo) }}" class="btn btn-ghost">← Volver</a>
</div>
@include('components.alert')
<div class="card" style="max-width:700px;padding:1.5rem">
    <form method="POST" action="{{ route('compras.articulos.update',$articulo) }}">
        @csrf @method('PUT')
        <div style="display:grid;gap:1.1rem">
            <div style="display:grid;grid-template-columns:1fr 2fr;gap:1rem">
                <div class="form-group"><label class="form-label">Código *</label><input type="text" name="codigo" class="form-control" value="{{ old('codigo',$articulo->codigo) }}" required></div>
                <div class="form-group"><label class="form-label">Nombre *</label><input type="text" name="nombre" class="form-control" value="{{ old('nombre',$articulo->nombre) }}" required></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem">
                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-control">
                        <option value="material" {{ old('tipo',$articulo->tipo)=='material'?'selected':'' }}>Material</option>
                        <option value="bien"     {{ old('tipo',$articulo->tipo)=='bien'?'selected':'' }}>Bien</option>
                        <option value="servicio" {{ old('tipo',$articulo->tipo)=='servicio'?'selected':'' }}>Servicio</option>
                        <option value="equipo"   {{ old('tipo',$articulo->tipo)=='equipo'?'selected':'' }}>Equipo</option>
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Unidad de Medida</label><input type="text" name="unidad_medida" class="form-control" value="{{ old('unidad_medida',$articulo->unidad_medida) }}"></div>
                <div class="form-group"><label class="form-label">Categoría</label><input type="text" name="categoria" class="form-control" value="{{ old('categoria',$articulo->categoria) }}"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div class="form-group"><label class="form-label">Precio Referencia (Bs.)</label><input type="number" name="precio_referencia" class="form-control" value="{{ old('precio_referencia',$articulo->precio_referencia) }}" step="0.01" min="0"></div>
                <div class="form-group"><label class="form-label">Stock Mínimo</label><input type="number" name="stock_minimo" class="form-control" value="{{ old('stock_minimo',$articulo->stock_minimo) }}" step="0.01" min="0"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Almacén</label>
                <select name="almacen_id" class="form-control">
                    <option value="">Sin almacén asignado</option>
                    @foreach($almacenes as $alm)<option value="{{ $alm->id }}" {{ old('almacen_id',$articulo->almacen_id)==$alm->id?'selected':'' }}>{{ $alm->codigo }} — {{ $alm->nombre }}</option>@endforeach
                </select>
            </div>
            <div class="form-group"><label class="form-label">Descripción</label><textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion',$articulo->descripcion) }}</textarea></div>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;border-top:1px solid var(--border);padding-top:1rem">
                <a href="{{ route('compras.articulos.show',$articulo) }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Actualizar</button>
            </div>
        </div>
    </form>
</div>
@endsection
