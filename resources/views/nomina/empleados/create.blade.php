@extends('layouts.app')
@section('title', 'Nuevo Empleado')
@section('breadcrumb')
    <a href="{{ route('nomina.empleados.index') }}">Empleados</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Nuevo</span>
@endsection
@section('content')
<div class="page-header fade-up"><h1 class="page-title">Registrar Nuevo Empleado</h1></div>
<form method="POST" action="{{ route('nomina.empleados.store') }}">
@csrf
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
<div>
<div class="card fade-up">
    <div class="card-header"><div class="card-title">Datos Personales</div></div>
    <div class="card-body">
        <div class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label">Cédula *</label>
                <input type="text" name="cedula" value="{{ old('cedula') }}" class="form-control" required maxlength="15" placeholder="V-12345678">
                @error('cedula')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Nombre(s) *</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" class="form-control" required maxlength="100">
                @error('nombre')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Apellido(s) *</label>
                <input type="text" name="apellido" value="{{ old('apellido') }}" class="form-control" required maxlength="100">
                @error('apellido')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" value="{{ old('telefono') }}" class="form-control" maxlength="20" placeholder="0412-1234567">
            </div>
            <div class="form-group">
                <label class="form-label">Correo Electrónico</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" maxlength="150">
            </div>
        </div>
    </div>
</div>

<div class="card fade-up" style="margin-top:20px;animation-delay:.05s">
    <div class="card-header"><div class="card-title">Datos Laborales</div></div>
    <div class="card-body">
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Cargo *</label>
                <select name="cargo_id" class="form-control" required>
                    <option value="">Seleccione…</option>
                    @foreach($cargos as $c)<option value="{{ $c->id }}" {{ old('cargo_id')==$c->id?'selected':'' }}>{{ $c->nombre }} — Bs. {{ number_format($c->salario_base,2) }}</option>@endforeach
                </select>
                @error('cargo_id')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Unidad Ejecutora *</label>
                <select name="unidad_ejecutora_id" class="form-control" required>
                    <option value="">Seleccione…</option>
                    @foreach($unidades as $u)<option value="{{ $u->id }}" {{ old('unidad_ejecutora_id')==$u->id?'selected':'' }}>{{ $u->nombre }}</option>@endforeach
                </select>
                @error('unidad_ejecutora_id')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Tipo de Empleado *</label>
                <select name="tipo" class="form-control" required>
                    <option value="fijo" {{ old('tipo','fijo')=='fijo'?'selected':'' }}>Fijo</option>
                    <option value="contratado" {{ old('tipo')=='contratado'?'selected':'' }}>Contratado</option>
                    <option value="obrero" {{ old('tipo')=='obrero'?'selected':'' }}>Obrero</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Fecha de Ingreso *</label>
                <input type="date" name="fecha_ingreso" value="{{ old('fecha_ingreso', date('Y-m-d')) }}" class="form-control" required>
                @error('fecha_ingreso')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

<div class="card fade-up" style="margin-top:20px;animation-delay:.1s">
    <div class="card-header"><div class="card-title">Datos Bancarios</div></div>
    <div class="card-body">
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Banco</label>
                <input type="text" name="banco" value="{{ old('banco') }}" class="form-control" maxlength="80" placeholder="Ej: Banco de Venezuela">
            </div>
            <div class="form-group">
                <label class="form-label">N° de Cuenta</label>
                <input type="text" name="numero_cuenta" value="{{ old('numero_cuenta') }}" class="form-control" maxlength="30" placeholder="0102-XXXX-XXXX">
            </div>
        </div>
    </div>
</div>
</div>

<div>
<div class="card fade-up" style="animation-delay:.15s">
    <div class="card-body">
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-bottom:10px;"><i class="fa-solid fa-save"></i> Registrar Empleado</button>
        <a href="{{ route('nomina.empleados.index') }}" class="btn btn-outline" style="width:100%;justify-content:center;">Cancelar</a>
    </div>
</div>
<div class="card fade-up" style="margin-top:16px;animation-delay:.2s">
    <div class="card-body">
        <div class="form-group"><label class="form-label">Observaciones</label><textarea name="observaciones" class="form-control" rows="3">{{ old('observaciones') }}</textarea></div>
    </div>
</div>
</div>
</div>
</form>
@endsection
