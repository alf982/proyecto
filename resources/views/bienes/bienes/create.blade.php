@extends('layouts.app')
@section('title', 'Incorporar Bien')
@section('breadcrumb')
    <a href="{{ route('bienes.bienes.index') }}">Inventario</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Incorporar Bien</span>
@endsection
@section('content')
<div class="page-header fade-up"><h1 class="page-title">Incorporar Nuevo Bien</h1></div>
<form method="POST" action="{{ route('bienes.bienes.store') }}">
@csrf
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
<div>
<div class="card fade-up">
    <div class="card-header"><div class="card-title">Identificación del Bien</div></div>
    <div class="card-body">
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">N° de Inventario *</label>
                <input type="text" name="numero_inventario" value="{{ old('numero_inventario', $numero) }}" class="form-control" required maxlength="30">
                @error('numero_inventario')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Categoría *</label>
                <select name="categoria_bien_id" class="form-control" required>
                    <option value="">Seleccione…</option>
                    @foreach($categorias as $cat)<option value="{{ $cat->id }}" {{ old('categoria_bien_id')==$cat->id?'selected':'' }}>{{ $cat->nombre }}</option>@endforeach
                </select>
                @error('categoria_bien_id')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Descripción *</label>
            <input type="text" name="descripcion" value="{{ old('descripcion') }}" class="form-control" required maxlength="300">
            @error('descripcion')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label">Marca</label>
                <input type="text" name="marca" value="{{ old('marca') }}" class="form-control" maxlength="100">
            </div>
            <div class="form-group">
                <label class="form-label">Modelo</label>
                <input type="text" name="modelo" value="{{ old('modelo') }}" class="form-control" maxlength="100">
            </div>
            <div class="form-group">
                <label class="form-label">Serial</label>
                <input type="text" name="serial" value="{{ old('serial') }}" class="form-control" maxlength="100">
            </div>
        </div>
        <div class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label">Año de Adquisición</label>
                <input type="number" name="anio_adquisicion" value="{{ old('anio_adquisicion', date('Y')) }}" class="form-control" min="1900" max="{{ date('Y') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Valor de Adquisición (Bs.) *</label>
                <input type="number" name="valor_adquisicion" value="{{ old('valor_adquisicion') }}" class="form-control" required step="0.01" min="0">
                @error('valor_adquisicion')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Fecha de Incorporación *</label>
                <input type="date" name="fecha_incorporacion" value="{{ old('fecha_incorporacion', date('Y-m-d')) }}" class="form-control" required>
                @error('fecha_incorporacion')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

<div class="card fade-up" style="margin-top:20px;animation-delay:.05s">
    <div class="card-header"><div class="card-title">Ubicación y Responsable</div></div>
    <div class="card-body">
        <div class="form-group">
            <label class="form-label">Unidad Ejecutora *</label>
            <select name="unidad_ejecutora_id" class="form-control" required>
                <option value="">Seleccione…</option>
                @foreach($unidades as $u)<option value="{{ $u->id }}" {{ old('unidad_ejecutora_id')==$u->id?'selected':'' }}>{{ $u->nombre }}</option>@endforeach
            </select>
            @error('unidad_ejecutora_id')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Ubicación física</label>
                <input type="text" name="ubicacion" value="{{ old('ubicacion') }}" class="form-control" maxlength="200" placeholder="Piso, oficina, sala…">
            </div>
            <div class="form-group">
                <label class="form-label">Responsable</label>
                <input type="text" name="responsable" value="{{ old('responsable') }}" class="form-control" maxlength="150">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones') }}</textarea>
        </div>
    </div>
</div>
</div>

<div>
<div class="card fade-up" style="animation-delay:.1s">
    <div class="card-body">
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-bottom:10px;"><i class="fa-solid fa-save"></i> Incorporar al Inventario</button>
        <a href="{{ route('bienes.bienes.index') }}" class="btn btn-outline" style="width:100%;justify-content:center;">Cancelar</a>
    </div>
</div>
</div>
</div>
</form>
@endsection
