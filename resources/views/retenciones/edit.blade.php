@extends('layouts.app')

@section('title', 'Editar Retención')

@section('breadcrumb')
    <span>Configuración Fiscal</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <a href="{{ route('retenciones.index') }}" style="color:var(--text-secondary);text-decoration:none;">Retenciones</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Editar: {{ $retencion->codigo }}</span>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fa-solid fa-pen" style="color:var(--accent-warn);margin-right:8px;"></i>Editar Retención: {{ $retencion->codigo }}</h1>
    <p class="page-subtitle">Modifica los datos de esta retención fiscal. Los cambios aplican a futuros registros.</p>
</div>

<div class="card fade-up" style="max-width:760px;">
    <div class="card-header">
        <span class="card-title"><i class="fa-solid fa-pen-to-square" style="margin-right:6px;color:var(--accent-warn)"></i>Datos de la Retención</span>
        <span class="badge {{ $retencion->estado_badge }}">{{ $retencion->activo ? 'Activa' : 'Inactiva' }}</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('retenciones.update', $retencion) }}" id="form-retencion">
            @csrf
            @method('PUT')

            {{-- Código y Nombre --}}
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="codigo">Código <span style="color:var(--accent-danger)">*</span></label>
                    <input type="text" id="codigo" name="codigo" class="form-control" required
                        maxlength="20" placeholder="Ej: ISLR, IVA, MUN"
                        value="{{ old('codigo', $retencion->codigo) }}" style="text-transform:uppercase;">
                    @error('codigo') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre Completo <span style="color:var(--accent-danger)">*</span></label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required
                        maxlength="150" placeholder="Ej: Retención de ISLR 1%"
                        value="{{ old('nombre', $retencion->nombre) }}">
                    @error('nombre') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Tipo y Valor --}}
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="tipo">Tipo de Retención <span style="color:var(--accent-danger)">*</span></label>
                    <select id="tipo" name="tipo" class="form-control" required onchange="toggleTipoValor(this.value)">
                        <option value="porcentaje" {{ old('tipo', $retencion->tipo) === 'porcentaje' ? 'selected' : '' }}>Porcentaje (%)</option>
                        <option value="monto_fijo" {{ old('tipo', $retencion->tipo) === 'monto_fijo' ? 'selected' : '' }}>Monto Fijo (Bs.)</option>
                    </select>
                    @error('tipo') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group" id="wrap-porcentaje" style="display:{{ old('tipo', $retencion->tipo) === 'monto_fijo' ? 'none' : 'block' }}">
                    <label class="form-label" for="porcentaje">Porcentaje (%)</label>
                    <input type="number" id="porcentaje" name="porcentaje" class="form-control"
                        min="0" max="100" step="0.0001" placeholder="Ej: 1.00 — 16.00"
                        value="{{ old('porcentaje', $retencion->porcentaje) }}">
                    @error('porcentaje') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group" id="wrap-monto-fijo" style="display:{{ old('tipo', $retencion->tipo) === 'monto_fijo' ? 'block' : 'none' }}">
                    <label class="form-label" for="monto_fijo">Monto Fijo (Bs.)</label>
                    <input type="number" id="monto_fijo" name="monto_fijo" class="form-control"
                        min="0" step="0.01" placeholder="Ej: 50.00"
                        value="{{ old('monto_fijo', $retencion->monto_fijo) }}">
                    @error('monto_fijo') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Base de cálculo --}}
            <div class="form-group">
                <label class="form-label" for="base_calculo">Base de Cálculo <span style="color:var(--accent-danger)">*</span></label>
                <select id="base_calculo" name="base_calculo" class="form-control" required>
                    <option value="monto_bruto" {{ old('base_calculo', $retencion->base_calculo) === 'monto_bruto' ? 'selected' : '' }}>
                        Monto Bruto (antes de otras retenciones)
                    </option>
                    <option value="monto_neto" {{ old('base_calculo', $retencion->base_calculo) === 'monto_neto' ? 'selected' : '' }}>
                        Monto Neto (después de retenciones previas)
                    </option>
                </select>
                @error('base_calculo') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            {{-- Módulos donde aplica --}}
            <div class="form-group">
                <label class="form-label">Aplica a <span style="color:var(--accent-danger)">*</span></label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:14px;background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:9px;">
                    @foreach($modulosDisponibles as $key => $label)
                    <label style="display:flex;align-items:center;gap:9px;cursor:pointer;padding:8px 10px;border-radius:7px;transition:background .2s;"
                        onmouseover="this.style.background='rgba(79,142,247,0.06)'"
                        onmouseout="this.style.background='transparent'">
                        <input type="checkbox" name="aplica_a[]" value="{{ $key }}" style="width:15px;height:15px;accent-color:var(--accent);"
                            {{ in_array($key, old('aplica_a', $retencion->aplica_a ?? [])) ? 'checked' : '' }}>
                        <span style="font-size:13px;">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
                @error('aplica_a') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            {{-- Obligatoria --}}
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 14px;background:rgba(247,185,79,0.06);border:1px solid rgba(247,185,79,0.2);border-radius:9px;">
                    <input type="checkbox" name="obligatoria" value="1" id="obligatoria" style="width:15px;height:15px;accent-color:var(--accent-warn);"
                        {{ old('obligatoria', $retencion->obligatoria) ? 'checked' : '' }}>
                    <div>
                        <div style="font-size:13px;font-weight:500;color:var(--accent-warn);">
                            <i class="fa-solid fa-lock" style="margin-right:5px;"></i>Retención Obligatoria
                        </div>
                        <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">
                            Se preseleccionará automáticamente en todos los formularios de pago.
                        </div>
                    </div>
                </label>
            </div>

            {{-- Descripción --}}
            <div class="form-group">
                <label class="form-label" for="descripcion">Descripción / Notas</label>
                <textarea id="descripcion" name="descripcion" class="form-control" rows="3"
                    placeholder="Descripción opcional..." maxlength="500">{{ old('descripcion', $retencion->descripcion) }}</textarea>
                @error('descripcion') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            {{-- Info aplicaciones --}}
            @php $totalAplicaciones = $retencion->aplicaciones()->count(); @endphp
            @if($totalAplicaciones > 0)
            <div style="padding:12px 14px;background:rgba(79,142,247,0.07);border:1px solid rgba(79,142,247,0.2);border-radius:9px;margin-bottom:20px;font-size:12px;color:var(--text-secondary);">
                <i class="fa-solid fa-info-circle" style="color:var(--accent);margin-right:6px;"></i>
                Esta retención ha sido aplicada en <strong style="color:var(--text-primary);">{{ $totalAplicaciones }}</strong> transacción(es). Los cambios aplican solo a futuros registros.
            </div>
            @endif

            <div style="display:flex;gap:12px;margin-top:8px;">
                <button type="submit" class="btn btn-primary" id="btn-actualizar-retencion">
                    <i class="fa-solid fa-save"></i> Actualizar Retención
                </button>
                <a href="{{ route('retenciones.index') }}" class="btn btn-outline">
                    <i class="fa-solid fa-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleTipoValor(tipo) {
    const wrapPorc = document.getElementById('wrap-porcentaje');
    const wrapFijo = document.getElementById('wrap-monto-fijo');
    if (tipo === 'monto_fijo') {
        wrapPorc.style.display = 'none';
        wrapFijo.style.display = 'block';
    } else {
        wrapPorc.style.display = 'block';
        wrapFijo.style.display = 'none';
    }
}
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('codigo').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
});
</script>
@endpush
