@extends('layouts.app')
@section('title', 'Editar Solicitud ' . $solicitud->numero)
@section('breadcrumb')
    <a href="{{ route('compras.solicitudes.index') }}" style="color:var(--text-secondary);text-decoration:none;">Solicitudes</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Editar {{ $solicitud->numero }}</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;gap:12px;">
    <a href="{{ route('compras.solicitudes.show', $solicitud) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <h1 class="page-title">Editar Solicitud</h1>
        <p class="page-subtitle">{{ $solicitud->numero }}</p>
    </div>
</div>

@if(!in_array($solicitud->estado, ['borrador','enviada']))
<div class="alert alert-warning fade-up">
    <i class="fa-solid fa-triangle-exclamation"></i>
    Esta solicitud está en estado <strong>{{ ucfirst($solicitud->estado) }}</strong> y no puede editarse.
</div>
@else
<div class="card fade-up" style="max-width:800px;">
    <div class="card-header"><div class="card-title">Datos de la Solicitud</div></div>
    <div class="card-body" style="padding:24px;">
        <form method="POST" action="{{ route('compras.solicitudes.update', $solicitud) }}">
            @csrf @method('PUT')
            @if($errors->any())
            <div class="alert alert-danger" style="margin-bottom:16px;">
                <ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div class="form-group">
                    <label class="form-label required">Unidad Ejecutora</label>
                    <select name="unidad_ejecutora_id" class="form-control" required>
                        @foreach($unidades as $u)
                        <option value="{{ $u->id }}" {{ old('unidad_ejecutora_id', $solicitud->unidad_ejecutora_id) == $u->id ? 'selected' : '' }}>{{ $u->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label required">Tipo</label>
                    <select name="tipo" class="form-control" required>
                        @foreach(['compra','servicio','ambos'] as $t)
                        <option value="{{ $t }}" {{ old('tipo', $solicitud->tipo) === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label required">Prioridad</label>
                    <select name="prioridad" class="form-control" required>
                        @foreach(['baja','normal','alta','urgente'] as $p)
                        <option value="{{ $p }}" {{ old('prioridad', $solicitud->prioridad) === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha Requerida</label>
                    <input type="date" name="fecha_requerida" class="form-control" value="{{ old('fecha_requerida', $solicitud->fecha_requerida?->format('Y-m-d')) }}">
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label required">Motivo / Justificación</label>
                    <textarea name="motivo" class="form-control" rows="3" required>{{ old('motivo', $solicitud->motivo) }}</textarea>
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones', $solicitud->observaciones) }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('compras.solicitudes.show', $solicitud) }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
