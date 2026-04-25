@extends('layouts.app')
@section('title', 'Editar Bien ' . $bien->numero_inventario)
@section('breadcrumb')
    <a href="{{ route('bienes.bienes.show', $bien) }}">{{ $bien->numero_inventario }}</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Editar</span>
@endsection
@section('content')
<div class="page-header fade-up"><h1 class="page-title">Editar Bien</h1></div>
<form method="POST" action="{{ route('bienes.bienes.update', $bien) }}">
@csrf @method('PUT')
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
<div>
<div class="card fade-up">
    <div class="card-header"><div class="card-title">Identificación</div></div>
    <div class="card-body">
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">N° Inventario *</label>
                <input type="text" name="numero_inventario" value="{{ old('numero_inventario', $bien->numero_inventario) }}" class="form-control" required maxlength="30">
                @error('numero_inventario')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Categoría *</label>
                <select name="categoria_bien_id" class="form-control" required>
                    @foreach($categorias as $cat)<option value="{{ $cat->id }}" {{ old('categoria_bien_id',$bien->categoria_bien_id)==$cat->id?'selected':'' }}>{{ $cat->nombre }}</option>@endforeach
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Descripción *</label>
            <input type="text" name="descripcion" value="{{ old('descripcion', $bien->descripcion) }}" class="form-control" required maxlength="300">
        </div>
        <div class="form-row form-row-3">
            <div class="form-group"><label class="form-label">Marca</label><input type="text" name="marca" value="{{ old('marca', $bien->marca) }}" class="form-control" maxlength="100"></div>
            <div class="form-group"><label class="form-label">Modelo</label><input type="text" name="modelo" value="{{ old('modelo', $bien->modelo) }}" class="form-control" maxlength="100"></div>
            <div class="form-group"><label class="form-label">Serial</label><input type="text" name="serial" value="{{ old('serial', $bien->serial) }}" class="form-control" maxlength="100"></div>
        </div>
        <div class="form-row form-row-3">
            <div class="form-group"><label class="form-label">Año Adquisición</label><input type="number" name="anio_adquisicion" value="{{ old('anio_adquisicion', $bien->anio_adquisicion) }}" class="form-control" min="1900" max="{{ date('Y') }}"></div>
            <div class="form-group"><label class="form-label">Valor Adquisición (Bs.)</label><input type="number" name="valor_adquisicion" value="{{ old('valor_adquisicion', $bien->valor_adquisicion) }}" class="form-control" step="0.01" min="0"></div>
            <div class="form-group"><label class="form-label">Valor Actual (Bs.) *</label><input type="number" name="valor_actual" value="{{ old('valor_actual', $bien->valor_actual) }}" class="form-control" required step="0.01" min="0"></div>
        </div>
    </div>
</div>
<div class="card fade-up" style="margin-top:20px;animation-delay:.05s">
    <div class="card-header"><div class="card-title">Ubicación y Estado</div></div>
    <div class="card-body">
        <div class="form-group">
            <label class="form-label">Unidad Ejecutora *</label>
            <select name="unidad_ejecutora_id" class="form-control" required>
                @foreach($unidades as $u)<option value="{{ $u->id }}" {{ old('unidad_ejecutora_id',$bien->unidad_ejecutora_id)==$u->id?'selected':'' }}>{{ $u->nombre }}</option>@endforeach
            </select>
        </div>
        <div class="form-row form-row-2">
            <div class="form-group"><label class="form-label">Ubicación</label><input type="text" name="ubicacion" value="{{ old('ubicacion', $bien->ubicacion) }}" class="form-control" maxlength="200"></div>
            <div class="form-group"><label class="form-label">Responsable</label><input type="text" name="responsable" value="{{ old('responsable', $bien->responsable) }}" class="form-control" maxlength="150"></div>
        </div>
        <div class="form-group">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-control">
                @foreach(['activo','en_reparacion','extraviado'] as $e)<option value="{{ $e }}" {{ old('estado',$bien->estado)==$e?'selected':'' }}>{{ ucwords(str_replace('_',' ',$e)) }}</option>@endforeach
            </select>
        </div>
        <div class="form-group"><label class="form-label">Observaciones</label><textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones', $bien->observaciones) }}</textarea></div>
    </div>
</div>
</div>
<div>
<div class="card fade-up" style="animation-delay:.1s">
    <div class="card-body">
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-bottom:10px;"><i class="fa-solid fa-save"></i> Guardar Cambios</button>
        <a href="{{ route('bienes.bienes.show', $bien) }}" class="btn btn-outline" style="width:100%;justify-content:center;">Cancelar</a>
    </div>
</div>
</div>
</div>
</form>
@endsection
