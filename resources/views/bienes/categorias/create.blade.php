@extends('layouts.app')
@section('title', 'Nueva Categoría de Bien')
@section('breadcrumb')
    <a href="{{ route('bienes.categorias.index') }}">Categorías</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Nueva</span>
@endsection
@section('content')
<div class="page-header fade-up"><h1 class="page-title">Nueva Categoría de Bien</h1></div>
<div style="max-width:600px;">
<form method="POST" action="{{ route('bienes.categorias.store') }}">
@csrf
<div class="card fade-up">
    <div class="card-header"><div class="card-title">Datos de la Categoría</div></div>
    <div class="card-body">
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Código *</label>
                <input type="text" name="codigo" value="{{ old('codigo') }}" class="form-control" required maxlength="20" placeholder="Ej: MOB, EQC, VEH">
                @error('codigo')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Nombre *</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" class="form-control" required maxlength="150" placeholder="Ej: Mobiliario, Equipos de Computación">
                @error('nombre')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Vida Útil (años) *</label>
                <input type="number" name="vida_util_anios" value="{{ old('vida_util_anios', 5) }}" class="form-control" required min="1" max="99">
                @error('vida_util_anios')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Tasa de Depreciación Anual (%) *</label>
                <input type="number" name="tasa_depreciacion" value="{{ old('tasa_depreciacion', 20) }}" class="form-control" required min="0" max="100" step="0.0001">
                @error('tasa_depreciacion')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion') }}</textarea>
        </div>
    </div>
</div>
<div style="display:flex;gap:10px;margin-top:16px;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Guardar</button>
    <a href="{{ route('bienes.categorias.index') }}" class="btn btn-outline">Cancelar</a>
</div>
</form>
</div>
@endsection
