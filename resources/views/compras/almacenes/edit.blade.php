@extends('layouts.app')
@section('title','Editar Almacén')
@section('breadcrumb')
    <span>Compras</span><span class="breadcrumb-sep">›</span>
    <a href="{{ route('compras.almacenes.index') }}">Almacenes</a>
    <span class="breadcrumb-sep">›</span><span class="current">Editar</span>
@endsection
@section('content')
<div class="page-header">
    <div><h1 class="page-title">Editar Almacén</h1><p class="page-subtitle">{{ $almacen->nombre }}</p></div>
    <a href="{{ route('compras.almacenes.index') }}" class="btn btn-ghost">← Volver</a>
</div>
@include('components.alert')
<div class="card" style="max-width:600px;padding:1.5rem">
    <form method="POST" action="{{ route('compras.almacenes.update',$almacen) }}">
        @csrf @method('PUT')
        <div style="display:grid;gap:1.1rem">
            <div style="display:grid;grid-template-columns:1fr 2fr;gap:1rem">
                <div class="form-group">
                    <label class="form-label">Código *</label>
                    <input type="text" name="codigo" class="form-control" value="{{ old('codigo',$almacen->codigo) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre',$almacen->nombre) }}" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Ubicación</label>
                <input type="text" name="ubicacion" class="form-control" value="{{ old('ubicacion',$almacen->ubicacion) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Responsable</label>
                <input type="text" name="responsable" class="form-control" value="{{ old('responsable',$almacen->responsable) }}">
            </div>
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                    <input type="checkbox" name="activo" value="1" {{ old('activo',$almacen->activo)?'checked':'' }} style="accent-color:var(--primary)">
                    <strong>Almacén activo</strong>
                </label>
            </div>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;border-top:1px solid var(--border);padding-top:1rem">
                <a href="{{ route('compras.almacenes.index') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Actualizar</button>
            </div>
        </div>
    </form>
</div>
@endsection
