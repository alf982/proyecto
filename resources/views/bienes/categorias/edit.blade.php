@extends('layouts.app')
@section('title', 'Editar Categoría')
@section('breadcrumb')
    <a href="{{ route('bienes.categorias.index') }}">Categorías</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Editar</span>
@endsection
@section('content')
<div class="page-header fade-up"><h1 class="page-title">Editar Categoría: {{ $categoria->nombre }}</h1></div>
<div style="max-width:600px;">
<form method="POST" action="{{ route('bienes.categorias.update', $categoria) }}">
@csrf @method('PUT')
<div class="card fade-up">
    <div class="card-header"><div class="card-title">Datos de la Categoría</div></div>
    <div class="card-body">
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Código *</label>
                <input type="text" name="codigo" value="{{ old('codigo', $categoria->codigo) }}" class="form-control" required maxlength="20">
                @error('codigo')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Nombre *</label>
                <input type="text" name="nombre" value="{{ old('nombre', $categoria->nombre) }}" class="form-control" required maxlength="150">
                @error('nombre')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Vida Útil (años) *</label>
                <input type="number" name="vida_util_anios" value="{{ old('vida_util_anios', $categoria->vida_util_anios) }}" class="form-control" required min="1" max="99">
            </div>
            <div class="form-group">
                <label class="form-label">Tasa de Depreciación Anual (%) *</label>
                <input type="number" name="tasa_depreciacion" value="{{ old('tasa_depreciacion', $categoria->tasa_depreciacion) }}" class="form-control" required step="0.0001" min="0" max="100">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion', $categoria->descripcion) }}</textarea>
        </div>
        <div class="form-group">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <input type="checkbox" name="activo" value="1" {{ old('activo', $categoria->activo) ? 'checked' : '' }}>
                <span class="form-label" style="margin:0;">Categoría activa</span>
            </label>
        </div>
    </div>
</div>
<div style="display:flex;gap:10px;margin-top:16px;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Actualizar</button>
    <a href="{{ route('bienes.categorias.index') }}" class="btn btn-outline">Cancelar</a>
</div>
</form>
</div>
@endsection
