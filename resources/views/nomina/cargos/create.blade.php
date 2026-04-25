@extends('layouts.app')
@section('title', 'Nuevo Cargo')
@section('breadcrumb')
    <a href="{{ route('nomina.cargos.index') }}">Cargos</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Nuevo</span>
@endsection
@section('content')
<div class="page-header fade-up"><h1 class="page-title">Nuevo Cargo</h1></div>
<div style="max-width:560px;">
<form method="POST" action="{{ route('nomina.cargos.store') }}">
@csrf
<div class="card fade-up">
    <div class="card-header"><div class="card-title">Datos del Cargo</div></div>
    <div class="card-body">
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Código *</label>
                <input type="text" name="codigo" value="{{ old('codigo') }}" class="form-control" required maxlength="20" placeholder="Ej: DIR01, PROF03">
                @error('codigo')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Nombre del Cargo *</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" class="form-control" required maxlength="150" placeholder="Ej: Analista de Sistemas">
                @error('nombre')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Nivel *</label>
                <select name="nivel" class="form-control" required>
                    @foreach(['directivo','profesional','tecnico','administrativo','obrero'] as $n)
                    <option value="{{ $n }}" {{ old('nivel','administrativo')==$n?'selected':'' }}>{{ ucfirst($n) }}</option>@endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Salario Base (Bs.) *</label>
                <input type="number" name="salario_base" value="{{ old('salario_base', 0) }}" class="form-control" required step="0.01" min="0">
                @error('salario_base')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>
<div style="display:flex;gap:10px;margin-top:16px;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Guardar</button>
    <a href="{{ route('nomina.cargos.index') }}" class="btn btn-outline">Cancelar</a>
</div>
</form>
</div>
@endsection
