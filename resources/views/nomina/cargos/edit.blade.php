@extends('layouts.app')
@section('title', 'Editar Cargo')
@section('breadcrumb')
    <a href="{{ route('nomina.cargos.index') }}">Cargos</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Editar</span>
@endsection
@section('content')
<div class="page-header fade-up"><h1 class="page-title">Editar Cargo: {{ $cargo->nombre }}</h1></div>
<div style="max-width:560px;">
<form method="POST" action="{{ route('nomina.cargos.update', $cargo) }}">
@csrf @method('PUT')
<div class="card fade-up">
    <div class="card-header"><div class="card-title">Datos del Cargo</div></div>
    <div class="card-body">
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Código *</label>
                <input type="text" name="codigo" value="{{ old('codigo', $cargo->codigo) }}" class="form-control" required maxlength="20">
                @error('codigo')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Nombre del Cargo *</label>
                <input type="text" name="nombre" value="{{ old('nombre', $cargo->nombre) }}" class="form-control" required maxlength="150">
                @error('nombre')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Nivel *</label>
                <select name="nivel" class="form-control" required>
                    @foreach(['directivo','profesional','tecnico','administrativo','obrero'] as $n)
                    <option value="{{ $n }}" {{ old('nivel',$cargo->nivel)==$n?'selected':'' }}>{{ ucfirst($n) }}</option>@endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Salario Base (Bs.) *</label>
                <input type="number" name="salario_base" value="{{ old('salario_base', $cargo->salario_base) }}" class="form-control" required step="0.01" min="0">
            </div>
        </div>
        <div class="form-group">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <input type="checkbox" name="activo" value="1" {{ old('activo', $cargo->activo)?'checked':'' }}>
                <span class="form-label" style="margin:0;">Cargo activo</span>
            </label>
        </div>
    </div>
</div>
<div style="display:flex;gap:10px;margin-top:16px;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Actualizar</button>
    <a href="{{ route('nomina.cargos.index') }}" class="btn btn-outline">Cancelar</a>
</div>
</form>
</div>
@endsection
