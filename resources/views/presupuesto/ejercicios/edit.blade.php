@extends('layouts.app')
@section('title', 'Editar Ejercicio Fiscal')
@section('breadcrumb')
    <a href="{{ route('presupuesto.ejercicios.index') }}" style="color:var(--text-secondary);text-decoration:none;">Ejercicios</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Editar</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Editar Ejercicio Fiscal</h1>
        <p class="page-subtitle">Modificar el ejercicio <strong>{{ $ejercicio->anio }}</strong></p>
    </div>
    <a href="{{ route('presupuesto.ejercicios.index') }}" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
</div>

<div class="card fade-up" style="animation-delay:.05s;max-width:600px;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-calendar-days" style="color:var(--accent);margin-right:8px;"></i>
            Datos del Ejercicio
        </div>
        @php
            $cls = match($ejercicio->estado) { 'activo'=>'badge-active','cerrado'=>'badge-danger',default=>'badge-warn' };
            $lbl = match($ejercicio->estado) { 'activo'=>'Activo','cerrado'=>'Cerrado',default=>'Borrador' };
        @endphp
        <span class="badge {{ $cls }}">{{ $lbl }}</span>
    </div>
    <div class="card-body">
        @if($ejercicio->estado === 'cerrado')
            <div class="flash flash-error" style="margin-bottom:20px;">
                <i class="fa-solid fa-lock"></i> Este ejercicio está cerrado y no puede modificarse.
            </div>
        @endif

        <form method="POST" action="{{ route('presupuesto.ejercicios.update', $ejercicio) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label" for="anio">Año fiscal *</label>
                <input type="number" id="anio" name="anio" class="form-control"
                    value="{{ old('anio', $ejercicio->anio) }}"
                    min="2000" max="2100" required
                    {{ $ejercicio->estado === 'cerrado' ? 'disabled' : '' }}>
                @error('anio')
                    <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="fecha_inicio">Fecha de inicio *</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control"
                        value="{{ old('fecha_inicio', $ejercicio->fecha_inicio->format('Y-m-d')) }}" required
                        {{ $ejercicio->estado === 'cerrado' ? 'disabled' : '' }}>
                    @error('fecha_inicio')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="fecha_fin">Fecha de fin *</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" class="form-control"
                        value="{{ old('fecha_fin', $ejercicio->fecha_fin->format('Y-m-d')) }}" required
                        {{ $ejercicio->estado === 'cerrado' ? 'disabled' : '' }}>
                    @error('fecha_fin')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="observaciones">Observaciones</label>
                <textarea id="observaciones" name="observaciones" class="form-control" rows="3"
                    {{ $ejercicio->estado === 'cerrado' ? 'disabled' : '' }}>{{ old('observaciones', $ejercicio->observaciones) }}</textarea>
            </div>

            @if($ejercicio->estado !== 'cerrado')
            <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:16px;border-top:1px solid var(--border);">
                <a href="{{ route('presupuesto.ejercicios.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                </button>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection
