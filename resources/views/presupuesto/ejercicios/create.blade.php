@extends('layouts.app')
@section('title', 'Nuevo Ejercicio Fiscal')
@section('breadcrumb')
    <a href="{{ route('presupuesto.ejercicios.index') }}" style="color:var(--text-secondary);text-decoration:none;">Ejercicios</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Nuevo</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Nuevo Ejercicio Fiscal</h1>
        <p class="page-subtitle">Define el período presupuestario anual</p>
    </div>
    <a href="{{ route('presupuesto.ejercicios.index') }}" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

<div class="card fade-up" style="animation-delay:.05s;max-width:600px;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-calendar-days" style="color:var(--accent);margin-right:8px;"></i>Datos del Ejercicio</div></div>
    <div class="card-body">
        <form method="POST" action="{{ route('presupuesto.ejercicios.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="anio">Año fiscal *</label>
                <input type="number" id="anio" name="anio" class="form-control" value="{{ old('anio', date('Y')) }}" min="2000" max="2100" required>
                @error('anio')<div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
            </div>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="fecha_inicio">Fecha de inicio *</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="{{ old('fecha_inicio') }}" required>
                    @error('fecha_inicio')<div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="fecha_fin">Fecha de fin *</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="{{ old('fecha_fin') }}" required>
                    @error('fecha_fin')<div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="observaciones">Observaciones</label>
                <textarea id="observaciones" name="observaciones" class="form-control" rows="3" placeholder="Notas sobre este ejercicio fiscal...">{{ old('observaciones') }}</textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:16px;border-top:1px solid var(--border);">
                <a href="{{ route('presupuesto.ejercicios.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection
