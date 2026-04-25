@extends('layouts.app')
@section('title', 'Nueva Nómina')
@section('breadcrumb')
    <a href="{{ route('nomina.nominas.index') }}">Nóminas</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Nueva</span>
@endsection
@section('content')
<div class="page-header fade-up"><h1 class="page-title">Crear Nueva Nómina</h1></div>
<div style="max-width:640px;">
@if(!$ejercicio)
<div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation"></i> No hay ejercicio fiscal activo. Active uno antes de crear una nómina.</div>
@endif
<form method="POST" action="{{ route('nomina.nominas.store') }}">
@csrf
<div class="card fade-up">
    <div class="card-header"><div class="card-title">Datos de la Nómina</div></div>
    <div class="card-body">
        <div style="padding:12px;background:rgba(34,211,166,0.07);border:1px solid rgba(34,211,166,0.2);border-radius:10px;margin-bottom:18px;">
            <div style="font-size:12px;color:var(--text-secondary);margin-bottom:2px;">ℹ️ Al crear la nómina, el sistema calculará automáticamente todos los empleados activos aplicando los conceptos configurados (IVSS, FAOV, bonos, etc.).</div>
        </div>
        <div class="form-group">
            <label class="form-label">Tipo de Nómina *</label>
            <select name="tipo_nomina" class="form-control" required>
                @foreach(['ordinaria'=>'Ordinaria (mensual)','vacacional'=>'Vacacional','utilidades'=>'Utilidades','bono'=>'Bono especial','liquidacion'=>'Liquidación'] as $v=>$l)
                <option value="{{ $v }}" {{ old('tipo_nomina','ordinaria')==$v?'selected':'' }}>{{ $l }}</option>@endforeach
            </select>
        </div>

        {{-- Partida presupuestaria (opcional pero recomendada) --}}
        <div class="form-group">
            <label class="form-label">
                Partida Presupuestaria
                <span style="font-size:11px;color:var(--text-muted);font-weight:400;margin-left:6px;">(opcional — puede asignarse al registrar el pago)</span>
            </label>
            <select name="partida_presupuestaria_id" id="sel-partida-nomina" class="form-control" onchange="mostrarSaldoPartida(this)">
                <option value="">— Sin partida asignada —</option>
                @foreach($partidas as $p)
                <option value="{{ $p->id }}"
                    data-saldo="{{ $p->saldo_actual }}"
                    {{ old('partida_presupuestaria_id') == $p->id ? 'selected' : '' }}>
                    {{ $p->codigo }} — {{ Str::limit($p->descripcion, 55) }}
                    (Bs. {{ number_format($p->saldo_actual, 2) }})
                </option>
                @endforeach
            </select>
            @error('partida_presupuestaria_id')<div class="form-error">{{ $message }}</div>@enderror
            <div id="info-partida-nomina" style="display:none;margin-top:6px;padding:7px 12px;background:rgba(79,142,247,0.07);border:1px solid rgba(79,142,247,0.2);border-radius:6px;font-size:.8rem;">
                Saldo disponible: <strong id="txt-saldo-partida" style="font-family:monospace;color:var(--accent-3);"></strong>
            </div>
        </div>
        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Período Inicio *</label>
                <input type="date" name="periodo_inicio" value="{{ old('periodo_inicio') }}" class="form-control" required>
                @error('periodo_inicio')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Período Fin *</label>
                <input type="date" name="periodo_fin" value="{{ old('periodo_fin') }}" class="form-control" required>
                @error('periodo_fin')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
        @if($ejercicio)
        <div style="padding:10px;background:rgba(79,142,247,0.07);border-radius:8px;font-size:12px;margin-bottom:12px;">
            <span style="color:var(--text-secondary);">Ejercicio fiscal activo:</span> <strong>{{ $ejercicio->anio }}</strong>
        </div>
        @endif
        <div class="form-group">
            <label class="form-label">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones') }}</textarea>
        </div>
    </div>
</div>

{{-- ── RETENCIONES APLICABLES ──────────────────────────────── --}}
<div style="padding:10px 14px;background:rgba(247,185,79,0.07);border:1px solid rgba(247,185,79,0.2);border-radius:9px;margin-bottom:12px;font-size:12px;color:var(--text-secondary);">
    <i class="fa-solid fa-info-circle" style="color:var(--accent-warn);margin-right:5px;"></i>
    Las retenciones seleccionadas se registrarán cuando se apruebe y pague la nómina.
    El cálculo de montos se actualizará al conocerse el total neto.
</div>
{{-- Monto de referencia oculto (se llenará desde el saldo de partida como estimado) --}}
<input type="hidden" name="total_nomina_ref" id="total-nomina-ref" value="0">
@include('components.retenciones-selector', [
    'modulo'     => 'nomina',
    'inputMonto' => 'total_nomina_ref',
])

<div style="display:flex;gap:10px;margin-top:16px;">
    <button type="submit" class="btn btn-primary" {{ !$ejercicio ? 'disabled' : '' }}><i class="fa-solid fa-calculator"></i> Calcular Nómina</button>
    <a href="{{ route('nomina.nominas.index') }}" class="btn btn-outline">Cancelar</a>
</div>
</form>
</div>
@endsection

@push('scripts')
<script>
function mostrarSaldoPartida(sel) {
    const panel = document.getElementById('info-partida-nomina');
    if (!sel.value) { panel.style.display = 'none'; return; }
    const saldo = parseFloat(sel.options[sel.selectedIndex].dataset.saldo || 0);
    document.getElementById('txt-saldo-partida').textContent =
        'Bs. ' + saldo.toLocaleString('es-VE', {minimumFractionDigits: 2});
    panel.style.display = 'block';
}
window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('sel-partida-nomina');
    if (sel?.value) mostrarSaldoPartida(sel);
});
</script>
@endpush
