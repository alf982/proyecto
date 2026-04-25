@extends('layouts.app')
@section('title','Nuevo Artículo')
@section('breadcrumb')
    <span>Compras</span><span class="breadcrumb-sep">›</span>
    <a href="{{ route('compras.articulos.index') }}">Artículos</a>
    <span class="breadcrumb-sep">›</span><span class="current">Nuevo</span>
@endsection
@section('content')
<div class="page-header">
    <div><h1 class="page-title">Nuevo Artículo</h1><p class="page-subtitle">Agregar al catálogo</p></div>
    <a href="{{ route('compras.articulos.index') }}" class="btn btn-ghost">← Volver</a>
</div>
@include('components.alert')
<div class="card" style="max-width:700px;padding:1.5rem">
    <form method="POST" action="{{ route('compras.articulos.store') }}">
        @csrf
        <div style="display:grid;gap:1.1rem">
            <div style="display:grid;grid-template-columns:1fr 2fr;gap:1rem">
                <div class="form-group">
                    <label class="form-label">Código *</label>
                    <input type="text" name="codigo" class="form-control" value="{{ old('codigo') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem">
                <div class="form-group">
                    <label class="form-label">Tipo *</label>
                    <select name="tipo" class="form-control" required>
                        <option value="material" {{ old('tipo')=='material'?'selected':'' }}>Material</option>
                        <option value="bien"     {{ old('tipo')=='bien'?'selected':'' }}>Bien</option>
                        <option value="servicio" {{ old('tipo')=='servicio'?'selected':'' }}>Servicio</option>
                        <option value="equipo"   {{ old('tipo')=='equipo'?'selected':'' }}>Equipo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Unidad de Medida</label>
                    <input type="text" name="unidad_medida" class="form-control" value="{{ old('unidad_medida','unidad') }}" placeholder="und, kg, lt, m2...">
                </div>
                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <input type="text" name="categoria" class="form-control" value="{{ old('categoria') }}" placeholder="Papelería, Equipos...">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem">
                <div class="form-group">
                    <label class="form-label">Precio Referencia (Bs.)</label>
                    <input type="number" name="precio_referencia" class="form-control" value="{{ old('precio_referencia',0) }}" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Stock Inicial</label>
                    <input type="number" name="stock_inicial" class="form-control" value="{{ old('stock_inicial',0) }}" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Stock Mínimo</label>
                    <input type="number" name="stock_minimo" class="form-control" value="{{ old('stock_minimo',0) }}" step="0.01" min="0">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Almacén</label>
                <select name="almacen_id" class="form-control">
                    <option value="">Sin almacén asignado</option>
                    @foreach($almacenes as $alm)
                    <option value="{{ $alm->id }}" {{ old('almacen_id')==$alm->id?'selected':'' }}>{{ $alm->codigo }} — {{ $alm->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion') }}</textarea>
            </div>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;border-top:1px solid var(--border);padding-top:1rem">
                <a href="{{ route('compras.articulos.index') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Guardar Artículo</button>
            </div>
        </div>
    </form>
</div>
@endsection
