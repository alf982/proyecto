@extends('layouts.app')
@section('title', 'Editar Pago ' . $pago->numero)
@section('breadcrumb')
    <a href="{{ route('presupuesto.pagos.index') }}" style="color:var(--text-secondary);text-decoration:none;">Pagos</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">{{ $pago->numero }}</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;gap:12px;">
    <a href="{{ route('presupuesto.pagos.show', $pago) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <h1 class="page-title">Editar Pago</h1>
        <p class="page-subtitle">{{ $pago->numero }} · {{ $pago->beneficiario }}</p>
    </div>
</div>

@if($pago->estado !== 'pendiente')
<div class="alert alert-warning fade-up">
    <i class="fa-solid fa-triangle-exclamation"></i>
    Este pago está en estado <strong>{{ ucfirst($pago->estado) }}</strong> y no puede editarse.
</div>
@else
<div class="card fade-up" style="max-width:700px;">
    <div class="card-header"><div class="card-title">Datos del Pago</div></div>
    <div class="card-body" style="padding:24px;">
        <form method="POST" action="{{ route('presupuesto.pagos.update', $pago) }}">
            @csrf @method('PUT')
            @if($errors->any())
            <div class="alert alert-danger" style="margin-bottom:16px;">
                <ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div class="form-group">
                    <label class="form-label">N° Causación</label>
                    <input type="text" class="form-control" value="{{ $pago->causacion?->numero }}" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label required">Tipo de Pago</label>
                    <select name="tipo_pago" class="form-control" required>
                        @foreach(['transferencia','cheque','efectivo'] as $tipo)
                        <option value="{{ $tipo }}" {{ old('tipo_pago', $pago->tipo_pago) === $tipo ? 'selected' : '' }}>{{ ucfirst($tipo) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label required">Fecha de Pago</label>
                    <input type="date" name="fecha_pago" class="form-control" value="{{ old('fecha_pago', $pago->fecha_pago?->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">N° Referencia</label>
                    <input type="text" name="numero_referencia" class="form-control" value="{{ old('numero_referencia', $pago->numero_referencia) }}" placeholder="TRF-0000...">
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label">Banco</label>
                    <input type="text" name="banco" class="form-control" value="{{ old('banco', $pago->banco) }}" placeholder="Nombre del banco">
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones', $pago->observaciones) }}</textarea>
                </div>
            </div>
            <div class="form-actions">
                <a href="{{ route('presupuesto.pagos.show', $pago) }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
