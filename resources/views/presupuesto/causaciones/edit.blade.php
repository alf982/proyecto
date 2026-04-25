@extends('layouts.app')
@section('title', 'Editar Causación')
@section('breadcrumb')
    <a href="{{ route('presupuesto.causaciones.index') }}" style="color:var(--text-secondary);text-decoration:none;">Causaciones</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <a href="{{ route('presupuesto.causaciones.show', $causacion) }}" style="color:var(--text-secondary);text-decoration:none;">{{ $causacion->numero }}</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Editar</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Editar Causación</h1>
        <p class="page-subtitle">{{ $causacion->numero }} — Solo editable en estado Borrador</p>
    </div>
    <a href="{{ route('presupuesto.causaciones.show', $causacion) }}" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
</div>

<!-- Info fija -->
<div class="card fade-up" style="margin-bottom:20px;">
    <div class="card-header"><div class="card-title">Imputación Presupuestaria (no editable)</div></div>
    <div class="card-body">
        <div class="form-row form-row-3">
            <div><div class="form-label">Ejercicio</div><strong>{{ $causacion->ejercicioFiscal?->anio ?? '—' }}</strong></div>
            <div><div class="form-label">Unidad Ejecutora</div><strong>{{ $causacion->unidadEjecutora?->nombre ?? '—' }}</strong></div>
            <div><div class="form-label">Partida</div><code style="color:var(--accent);">{{ $causacion->partida?->codigo ?? 'Sin código' }}</code></div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('presupuesto.causaciones.update', $causacion) }}">
@csrf @method('PUT')

<div class="card fade-up" style="margin-bottom:20px;animation-delay:.05s;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-building" style="color:var(--accent-2);margin-right:8px;"></i>Beneficiario</div></div>
    <div class="card-body">
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label" for="beneficiario">Beneficiario *</label>
                <input type="text" id="beneficiario" name="beneficiario" class="form-control"
                    value="{{ old('beneficiario', $causacion->beneficiario) }}" required>
                @error('beneficiario')<div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="rif_beneficiario">RIF / Cédula</label>
                <input type="text" id="rif_beneficiario" name="rif_beneficiario" class="form-control"
                    value="{{ old('rif_beneficiario', $causacion->rif_beneficiario) }}">
            </div>
        </div>
    </div>
</div>

<div class="card fade-up" style="margin-bottom:20px;animation-delay:.08s;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-file-invoice" style="color:var(--accent-3);margin-right:8px;"></i>Documento Soporte</div></div>
    <div class="card-body">
        <div class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label" for="tipo_documento">Tipo *</label>
                <select id="tipo_documento" name="tipo_documento" class="form-control" required>
                    @foreach(['factura'=>'Factura','contrato'=>'Contrato','recibo'=>'Recibo','planilla'=>'Planilla','otro'=>'Otro'] as $v => $l)
                        <option value="{{ $v }}" {{ old('tipo_documento', $causacion->tipo_documento) === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="numero_documento">N° Documento</label>
                <input type="text" id="numero_documento" name="numero_documento" class="form-control"
                    value="{{ old('numero_documento', $causacion->numero_documento) }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="fecha_documento">Fecha</label>
                <input type="date" id="fecha_documento" name="fecha_documento" class="form-control"
                    value="{{ old('fecha_documento', $causacion->fecha_documento?->format('Y-m-d')) }}">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label" for="descripcion_documento">Descripción</label>
            <input type="text" id="descripcion_documento" name="descripcion_documento" class="form-control"
                value="{{ old('descripcion_documento', $causacion->descripcion_documento) }}">
        </div>
    </div>
</div>

<div class="card fade-up" style="margin-bottom:20px;animation-delay:.10s;">
    <div class="card-header"><div class="card-title"><i class="fa-solid fa-calculator" style="color:var(--accent-warn);margin-right:8px;"></i>Concepto y Montos</div></div>
    <div class="card-body">
        <div class="form-group">
            <label class="form-label" for="concepto">Concepto *</label>
            <textarea id="concepto" name="concepto" class="form-control" rows="2" required>{{ old('concepto', $causacion->concepto) }}</textarea>
            @error('concepto')<div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
        </div>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label" for="fecha_causacion">Fecha de Causación *</label>
                <input type="date" id="fecha_causacion" name="fecha_causacion" class="form-control"
                    value="{{ old('fecha_causacion', $causacion->fecha_causacion?->format('Y-m-d')) }}" required>
            </div>
            <div></div>
        </div>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label" for="monto_causado">Monto Causado (Bs.) *</label>
                <input type="number" id="monto_causado" name="monto_causado" class="form-control"
                    value="{{ old('monto_causado', $causacion->monto_causado) }}"
                    required min="0.01" step="0.01" style="font-size:18px;font-weight:700;">
                @error('monto_causado')<div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="monto_retencion">Retenciones (Bs.)</label>
                <input type="number" id="monto_retencion" name="monto_retencion" class="form-control"
                    value="{{ old('monto_retencion', $causacion->monto_retencion) }}" min="0" step="0.01">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label" for="observaciones">Observaciones</label>
            <textarea id="observaciones" name="observaciones" class="form-control" rows="2">{{ old('observaciones', $causacion->observaciones) }}</textarea>
        </div>
    </div>
</div>

<div style="display:flex;gap:10px;justify-content:flex-end;padding-bottom:28px;">
    <a href="{{ route('presupuesto.causaciones.show', $causacion) }}" class="btn btn-outline">Cancelar</a>
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar Cambios</button>
</div>

</form>
@endsection
