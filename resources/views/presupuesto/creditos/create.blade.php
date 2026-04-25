@extends('layouts.app')
@section('title', 'Nuevo Crédito Presupuestario')
@section('breadcrumb')
    <a href="{{ route('presupuesto.creditos.index') }}" style="color:var(--text-secondary);text-decoration:none;">Créditos</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Nuevo</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div><h1 class="page-title">Nuevo Crédito Presupuestario</h1><p class="page-subtitle">Asigna un monto a una partida y unidad ejecutora</p></div>
    <a href="{{ route('presupuesto.creditos.index') }}" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

<div class="card fade-up" style="animation-delay:.05s;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-coins" style="color:var(--accent-3);margin-right:8px;"></i>Datos del Crédito</div></div>
    <div class="card-body">
        <form method="POST" action="{{ route('presupuesto.creditos.store') }}">
            @csrf
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="ejercicio_fiscal_id">Ejercicio Fiscal *</label>
                    <select id="ejercicio_fiscal_id" name="ejercicio_fiscal_id" class="form-control" required>
                        <option value="">Seleccionar...</option>
                        @foreach($ejercicios as $ej)
                            <option value="{{ $ej->id }}" {{ old('ejercicio_fiscal_id')==$ej->id?'selected':'' }}>{{ $ej->anio }} ({{ ucfirst($ej->estado) }})</option>
                        @endforeach
                    </select>
                    @error('ejercicio_fiscal_id')<div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="unidad_ejecutora_id">Unidad Ejecutora *</label>
                    <select id="unidad_ejecutora_id" name="unidad_ejecutora_id" class="form-control" required>
                        <option value="">Seleccionar...</option>
                        @foreach($unidades as $unidad)
                            <option value="{{ $unidad->id }}" {{ old('unidad_ejecutora_id')==$unidad->id?'selected':'' }}>[{{ $unidad->codigo }}] {{ $unidad->nombre }}</option>
                        @endforeach
                    </select>
                    @error('unidad_ejecutora_id')<div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="partida_presupuestaria_id">Partida Presupuestaria *</label>
                    <select id="partida_presupuestaria_id" name="partida_presupuestaria_id" class="form-control" required>
                        <option value="">Seleccionar...</option>
                        @foreach($partidas as $partida)
                            <option value="{{ $partida->id }}" {{ old('partida_presupuestaria_id')==$partida->id?'selected':'' }}>{{ $partida->codigo }} — {{ Str::limit($partida->descripcion, 50) }}</option>
                        @endforeach
                    </select>
                    @error('partida_presupuestaria_id')<div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="fuente_financiamiento_id">Fuente de Financiamiento</label>
                    <select id="fuente_financiamiento_id" name="fuente_financiamiento_id" class="form-control">
                        <option value="">Sin fuente específica</option>
                        @foreach($fuentes as $fuente)
                            <option value="{{ $fuente->id }}" {{ old('fuente_financiamiento_id')==$fuente->id?'selected':'' }}>{{ $fuente->codigo }} — {{ $fuente->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="proyecto_sia_id">Proyecto (opcional)</label>
                    <select id="proyecto_sia_id" name="proyecto_sia_id" class="form-control">
                        <option value="">Sin proyecto asociado</option>
                        @foreach($proyectos as $proyecto)
                            <option value="{{ $proyecto->id }}" {{ old('proyecto_sia_id')==$proyecto->id?'selected':'' }}>{{ $proyecto->codigo }} — {{ Str::limit($proyecto->nombre, 40) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="monto_aprobado">Monto Aprobado (Bs.) *</label>
                    <input type="number" id="monto_aprobado" name="monto_aprobado" class="form-control" value="{{ old('monto_aprobado') }}" required min="0" step="0.01" placeholder="0.00" style="font-size:16px;font-weight:600;">
                    @error('monto_aprobado')<div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="observaciones">Observaciones</label>
                <textarea id="observaciones" name="observaciones" class="form-control" rows="3" placeholder="Notas sobre este crédito...">{{ old('observaciones') }}</textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:16px;border-top:1px solid var(--border);">
                <a href="{{ route('presupuesto.creditos.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar Crédito</button>
            </div>
        </form>
    </div>
</div>
@endsection
