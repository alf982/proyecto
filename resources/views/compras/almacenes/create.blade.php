@extends('layouts.app')
@section('title','Nuevo Almacén')
@section('breadcrumb')
    <span>Compras</span><span class="breadcrumb-sep">›</span>
    <a href="{{ route('compras.almacenes.index') }}">Almacenes</a>
    <span class="breadcrumb-sep">›</span><span class="current">Nuevo</span>
@endsection
@section('content')
<div class="page-header">
    <div><h1 class="page-title">Nuevo Almacén</h1></div>
    <a href="{{ route('compras.almacenes.index') }}" class="btn btn-ghost">← Volver</a>
</div>
@include('components.alert')
<div class="card" style="max-width:600px;padding:1.5rem">
    <form method="POST" action="{{ route('compras.almacenes.store') }}">
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
            <div class="form-group">
                <label class="form-label">Ubicación</label>
                <input type="text" name="ubicacion" class="form-control" value="{{ old('ubicacion') }}" placeholder="Piso, edificio, dirección...">
            </div>
            <div class="form-group">
                <label class="form-label">Responsable</label>
                <input type="text" name="responsable" class="form-control" value="{{ old('responsable') }}">
            </div>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;border-top:1px solid var(--border);padding-top:1rem">
                <a href="{{ route('compras.almacenes.index') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">💾 Guardar</button>
            </div>
        </div>
    </form>
</div>
@endsection
