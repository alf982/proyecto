@extends('layouts.app')
@section('title','Editar Compromiso')
@section('breadcrumb')
    <a href="{{ route('presupuesto.compromisos.index') }}" style="color:var(--text-secondary);text-decoration:none;">Compromisos</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <a href="{{ route('presupuesto.compromisos.show', $compromiso) }}" style="color:var(--text-secondary);text-decoration:none;">{{ $compromiso->numero }}</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Editar</span>
@endsection
@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div><h1 class="page-title">Editar Compromiso</h1><p class="page-subtitle">{{ $compromiso->numero }}</p></div>
    <a href="{{ route('presupuesto.compromisos.show', $compromiso) }}" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

<div class="card fade-up" style="margin-bottom:18px;">
    <div class="card-header"><div class="card-title">Imputación (no editable)</div></div>
    <div class="card-body">
        <div class="form-row form-row-3">
            <div><div class="form-label">Ejercicio</div><strong>{{ $compromiso->ejercicioFiscal->anio }}</strong></div>
            <div><div class="form-label">Unidad</div><strong>{{ $compromiso->unidadEjecutora->nombre }}</strong></div>
            <div><div class="form-label">Partida</div><code style="color:var(--accent);">{{ $compromiso->partida->codigo }}</code></div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('presupuesto.compromisos.update', $compromiso) }}">
@csrf @method('PUT')
<div class="card fade-up" style="margin-bottom:18px;animation-delay:.05s;">
    <div class="card-header"><div class="card-title">Beneficiario</div></div>
    <div class="card-body">
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Nombre *</label>
                <input type="text" name="beneficiario" class="form-control" value="{{ old('beneficiario',$compromiso->beneficiario) }}" required>
                @error('beneficiario')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">RIF / Cédula</label>
                <input type="text" name="rif_beneficiario" class="form-control" value="{{ old('rif_beneficiario',$compromiso->rif_beneficiario) }}">
            </div>
        </div>
    </div>
</div>
<div class="card fade-up" style="margin-bottom:18px;animation-delay:.08s;">
    <div class="card-header"><div class="card-title">Concepto y Monto</div></div>
    <div class="card-body">
        <div class="form-group">
            <label class="form-label">Concepto *</label>
            <textarea name="concepto" class="form-control" rows="2" required>{{ old('concepto',$compromiso->concepto) }}</textarea>
            @error('concepto')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label">Monto (Bs.) *</label>
                <input type="number" name="monto" class="form-control" value="{{ old('monto',$compromiso->monto) }}" required min="0.01" step="0.01" style="font-size:18px;font-weight:700;">
                @error('monto')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Fecha *</label>
                <input type="date" name="fecha_compromiso" class="form-control" value="{{ old('fecha_compromiso',$compromiso->fecha_compromiso->format('Y-m-d')) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Vencimiento</label>
                <input type="date" name="fecha_vencimiento" class="form-control" value="{{ old('fecha_vencimiento',$compromiso->fecha_vencimiento?->format('Y-m-d')) }}">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones',$compromiso->observaciones) }}</textarea>
        </div>
    </div>
</div>

<div class="card fade-up" style="margin-bottom:18px;animation-delay:.10s;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-file-invoice" style="color:var(--accent-3);margin-right:8px;"></i>Documento Soporte (Factura / Contrato)
        </div>
    </div>
    <div class="card-body">
        <div class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label">Tipo de Documento</label>
                <select name="tipo_documento" class="form-control">
                    <option value="">— Sin documento aún —</option>
                    @foreach(['factura'=>'Factura','contrato'=>'Contrato','recibo'=>'Recibo','planilla'=>'Planilla','otro'=>'Otro'] as $v => $l)
                    <option value="{{ $v }}" {{ old('tipo_documento',$compromiso->tipo_documento) === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
                @error('tipo_documento')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">N° Documento</label>
                <input type="text" name="numero_documento" class="form-control"
                       value="{{ old('numero_documento',$compromiso->numero_documento) }}" placeholder="FAC-001-0045">
                @error('numero_documento')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Fecha del Documento</label>
                <input type="date" name="fecha_documento" class="form-control"
                       value="{{ old('fecha_documento',$compromiso->fecha_documento?->format('Y-m-d')) }}">
                @error('fecha_documento')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Descripción del Documento</label>
            <input type="text" name="descripcion_documento" class="form-control"
                   value="{{ old('descripcion_documento',$compromiso->descripcion_documento) }}"
                   placeholder="Ej: Factura por suministro de materiales">
        </div>
    </div>
</div>
<div style="display:flex;gap:10px;justify-content:flex-end;padding-bottom:28px;">
    <a href="{{ route('presupuesto.compromisos.show', $compromiso) }}" class="btn btn-outline">Cancelar</a>
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
</div>
</form>
@endsection
