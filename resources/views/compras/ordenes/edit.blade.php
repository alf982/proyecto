@extends('layouts.app')
@section('title', 'Editar Orden de Compra ' . $orden->numero)
@section('breadcrumb')
    <a href="{{ route('compras.ordenes.index') }}" style="color:var(--text-secondary);text-decoration:none;">Órdenes</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Editar {{ $orden->numero }}</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;gap:12px;">
    <a href="{{ route('compras.ordenes.show', $orden) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <h1 class="page-title">Editar Orden de Compra</h1>
        <p class="page-subtitle">{{ $orden->numero }}</p>
    </div>
</div>

@if(!in_array($orden->estado, ['borrador','enviada']))
<div class="alert alert-warning fade-up">
    <i class="fa-solid fa-triangle-exclamation"></i>
    Esta orden está en estado <strong>{{ ucfirst($orden->estado) }}</strong> y no puede editarse.
</div>
@else
<div class="card fade-up" style="max-width:800px;">
    <div class="card-header"><div class="card-title">Datos de la Orden</div></div>
    <div class="card-body" style="padding:24px;">
        <form method="POST" action="{{ route('compras.ordenes.update', $orden) }}">
            @csrf @method('PUT')
            @if($errors->any())
            <div class="alert alert-danger" style="margin-bottom:16px;">
                <ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div class="form-group">
                    <label class="form-label">Proveedor</label>
                    <input type="text" name="proveedor_nombre" class="form-control" value="{{ old('proveedor_nombre', $orden->proveedor_nombre) }}" placeholder="Nombre del proveedor">
                </div>
                <div class="form-group">
                    <label class="form-label">RIF Proveedor</label>
                    <input type="text" name="proveedor_rif" class="form-control" value="{{ old('proveedor_rif', $orden->proveedor_rif) }}" placeholder="J-00000000-0">
                </div>
                <div class="form-group">
                    <label class="form-label required">Tipo de Compra</label>
                    <select name="tipo_compra" class="form-control" required>
                        @foreach(['directa','licitacion','concurso'] as $t)
                        <option value="{{ $t }}" {{ old('tipo_compra', $orden->tipo_compra) === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label required">Fecha Estimada de Entrega</label>
                    <input type="date" name="fecha_entrega_estimada" class="form-control" value="{{ old('fecha_entrega_estimada', $orden->fecha_entrega_estimada?->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label">Condiciones de Pago</label>
                    <input type="text" name="condiciones_pago" class="form-control" value="{{ old('condiciones_pago', $orden->condiciones_pago) }}" placeholder="Contado, 15 días, etc.">
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3">{{ old('observaciones', $orden->observaciones) }}</textarea>
                </div>
            </div>
            <div class="form-actions">
                <a href="{{ route('compras.ordenes.show', $orden) }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
