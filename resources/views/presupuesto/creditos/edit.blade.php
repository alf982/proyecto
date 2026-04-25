@extends('layouts.app')
@section('title', 'Editar Crédito')
@section('breadcrumb')
    <a href="{{ route('presupuesto.creditos.index') }}" style="color:var(--text-secondary);text-decoration:none;">Créditos</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Editar</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Editar Crédito Presupuestario</h1>
        <p class="page-subtitle">
            Partida <code style="background:rgba(79,142,247,0.12);color:var(--accent);padding:2px 8px;border-radius:5px;">{{ $credito->partida?->codigo ?? 'Sin código' }}</code>
            · {{ $credito->ejercicioFiscal?->anio ?? '—' }}
        </p>
    </div>
    <a href="{{ route('presupuesto.creditos.index') }}" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
</div>

<!-- Info readonly -->
<div class="card fade-up" style="margin-bottom:20px;">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-circle-info" style="color:var(--accent-3);margin-right:8px;"></i>Información del Crédito (no editable)</div>
    </div>
    <div class="card-body">
        <div class="form-row form-row-3">
            <div>
                <div class="form-label">Ejercicio</div>
                <div style="font-size:20px;font-weight:800;">{{ $credito->ejercicioFiscal?->anio ?? '—' }}</div>
            </div>
            <div>
                <div class="form-label">Partida</div>
                <div style="font-size:14px;font-weight:600;">{{ $credito->partida?->codigo ?? 'Sin código' }}</div>
                <div style="font-size:12px;color:var(--text-secondary);">{{ $credito->partida?->descripcion ? mb_strimwidth($credito->partida->descripcion, 0, 50, '...') : '—' }}</div>
            </div>
            <div>
                <div class="form-label">Unidad Ejecutora</div>
                <div style="font-size:13px;font-weight:600;">{{ $credito->unidadEjecutora?->nombre ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Montos actuales -->
<div class="card fade-up" style="margin-bottom:20px;animation-delay:.05s;">
    <div class="card-header"><div class="card-title">Montos Actuales</div></div>
    <div class="card-body">
        <div class="form-row" style="grid-template-columns:repeat(auto-fill, minmax(150px, 1fr));gap:16px;">
            <div style="text-align:center;padding:16px;background:rgba(79,142,247,0.06);border-radius:10px;border:1px solid rgba(79,142,247,0.15);">
                <div style="font-size:11px;font-weight:600;color:var(--text-secondary);margin-bottom:6px;text-transform:uppercase;">Aprobado</div>
                <div style="font-size:17px;font-weight:800;color:var(--accent);">{{ number_format($credito->monto_aprobado, 2) }}</div>
            </div>
            <div style="text-align:center;padding:16px;background:rgba(247,185,79,0.06);border-radius:10px;border:1px solid rgba(247,185,79,0.15);">
                <div style="font-size:11px;font-weight:600;color:var(--text-secondary);margin-bottom:6px;text-transform:uppercase;">Comprometido</div>
                <div style="font-size:17px;font-weight:800;color:var(--accent-warn);">{{ number_format($credito->monto_comprometido, 2) }}</div>
            </div>
            <div style="text-align:center;padding:16px;background:rgba(34,211,166,0.06);border-radius:10px;border:1px solid rgba(34,211,166,0.15);">
                <div style="font-size:11px;font-weight:600;color:var(--text-secondary);margin-bottom:6px;text-transform:uppercase;">Disponible</div>
                <div style="font-size:17px;font-weight:800;color:var(--accent-3);">{{ number_format($credito->monto_vigente - $credito->monto_comprometido, 2) }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Editar monto aprobado -->
<div class="card fade-up" style="animation-delay:.10s;max-width:500px;">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-pen-to-square" style="color:var(--accent);margin-right:8px;"></i>Modificar Monto</div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('presupuesto.creditos.update', $credito) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label" for="monto_aprobado">Monto Aprobado (Bs.) *</label>
                <input type="number" id="monto_aprobado" name="monto_aprobado" class="form-control"
                    value="{{ old('monto_aprobado', $credito->monto_aprobado) }}"
                    required min="0" step="0.01"
                    style="font-size:18px;font-weight:700;">
                @error('monto_aprobado')
                    <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="observaciones">Observaciones</label>
                <textarea id="observaciones" name="observaciones" class="form-control" rows="3">{{ old('observaciones', $credito->observaciones) }}</textarea>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:16px;border-top:1px solid var(--border);">
                <a href="{{ route('presupuesto.creditos.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
