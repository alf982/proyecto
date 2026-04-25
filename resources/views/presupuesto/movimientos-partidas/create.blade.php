@extends('layouts.app')
@section('title', 'Nuevo Movimiento de Partida')
@section('breadcrumb')
    <span>Presupuesto</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <a href="{{ route('presupuesto.movimientos-partidas.index') }}" style="color:var(--text-secondary);text-decoration:none;">Movimientos</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Nuevo</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Nuevo Movimiento de Partida</h1>
        <p class="page-subtitle">Registra un ingreso, egreso o modificación de fondos presupuestarios</p>
    </div>
    <a href="{{ route('presupuesto.movimientos-partidas.index') }}" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
</div>

<div class="card fade-up" style="animation-delay:.05s;max-width:820px;">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-arrows-left-right" style="color:var(--accent);margin-right:8px;"></i>Datos del Movimiento</div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('presupuesto.movimientos-partidas.store') }}" id="form-mov">
            @csrf

            @if($errors->any())
            <div class="alert alert-danger" style="margin-bottom:16px;">
                <ul style="margin:0;padding-left:18px;">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            {{-- FILA 1: Partida + Tipo --}}
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="partida_presupuestaria_id">Partida Presupuestaria *</label>
                    <select id="partida_presupuestaria_id" name="partida_presupuestaria_id" class="form-control" required>
                        <option value="">— Seleccionar partida —</option>
                        @foreach($partidas as $p)
                            <option value="{{ $p->id }}"
                                data-saldo="{{ $p->saldo_actual }}"
                                data-cuenta="{{ $p->cuenta_bancaria_id }}"
                                {{ old('partida_presupuestaria_id', $partidaSeleccionada?->id) == $p->id ? 'selected' : '' }}>
                                {{ $p->codigo }} — {{ Str::limit($p->descripcion, 50) }}
                            </option>
                        @endforeach
                    </select>
                    @error('partida_presupuestaria_id')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                    {{-- Saldo actual de la partida seleccionada --}}
                    <div id="info-saldo" style="margin-top:8px;padding:10px 14px;background:rgba(79,142,247,0.08);border:1px solid rgba(79,142,247,0.2);border-radius:8px;display:none;">
                        <span style="font-size:11px;color:var(--text-secondary);">Saldo actual de la partida</span>
                        <div style="font-size:18px;font-weight:700;color:var(--accent);" id="txt-saldo">Bs. 0.00</div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="tipo">Tipo de Movimiento *</label>
                    <select id="tipo" name="tipo" class="form-control" required>
                        <option value="">— Seleccionar tipo —</option>
                        <optgroup label="Ingresos de fondos">
                            <option value="asignacion"           {{ old('tipo')=='asignacion'           ? 'selected':'' }}>➕ Asignación Inicial</option>
                            <option value="credito_adicional"    {{ old('tipo')=='credito_adicional'    ? 'selected':'' }}>➕ Crédito Adicional</option>
                            <option value="modificacion_entrada" {{ old('tipo')=='modificacion_entrada' ? 'selected':'' }}>⇄ Modificación (esta partida RECIBE)</option>
                            <option value="reintegro"            {{ old('tipo')=='reintegro'            ? 'selected':'' }}>➕ Reintegro</option>
                            <option value="nota_credito"         {{ old('tipo')=='nota_credito'         ? 'selected':'' }}>➕ Nota de Crédito</option>
                        </optgroup>
                        <optgroup label="Egresos de fondos">
                            <option value="ejecucion"  {{ old('tipo')=='ejecucion'  ? 'selected':'' }}>➖ Ejecución</option>
                            <option value="nota_debito" {{ old('tipo')=='nota_debito' ? 'selected':'' }}>➖ Nota de Débito</option>
                        </optgroup>
                    </select>
                    @error('tipo')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- CAMPO CONDICIONAL: Partida Contrapartida (solo para modificacion_entrada) --}}
            <div id="wrap-contrapartida" class="form-group" style="display:none;">
                <div style="padding:14px;background:rgba(255,180,0,0.07);border:1px solid rgba(255,180,0,0.25);border-radius:10px;">
                    <div style="font-size:12px;font-weight:600;color:var(--accent-warn);margin-bottom:10px;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Doble afectación presupuestaria — El monto se RESTARÁ de la partida que selecciones aquí
                    </div>
                    <label class="form-label" for="partida_contrapartida_id">Partida que cede los fondos *</label>
                    <select id="partida_contrapartida_id" name="partida_contrapartida_id" class="form-control">
                        <option value="">— Seleccionar partida contrapartida —</option>
                        @foreach($partidas as $p)
                            <option value="{{ $p->id }}"
                                data-saldo="{{ $p->saldo_actual }}"
                                {{ old('partida_contrapartida_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->codigo }} — {{ Str::limit($p->descripcion, 50) }}
                                (Saldo: Bs. {{ number_format($p->saldo_actual, 2) }})
                            </option>
                        @endforeach
                    </select>
                    @error('partida_contrapartida_id')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- FILA 2: Monto + Fecha --}}
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="monto">Monto *</label>
                    <input type="number" id="monto" name="monto" class="form-control" step="0.01" min="0.01"
                        placeholder="0.00" value="{{ old('monto') }}" required>
                    @error('monto')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="fecha_movimiento">Fecha *</label>
                    <input type="date" id="fecha_movimiento" name="fecha_movimiento" class="form-control"
                        value="{{ old('fecha_movimiento', date('Y-m-d')) }}" required>
                    @error('fecha_movimiento')
                        <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Concepto --}}
            <div class="form-group">
                <label class="form-label" for="concepto">Concepto *</label>
                <input type="text" id="concepto" name="concepto" class="form-control"
                    placeholder="Descripción del movimiento" value="{{ old('concepto') }}" required>
                @error('concepto')
                    <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                @enderror
            </div>

            {{-- FILA 3: Referencia + Ejercicio --}}
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label" for="referencia">N° de Referencia / Documento</label>
                    <input type="text" id="referencia" name="referencia" class="form-control"
                        placeholder="N° resolución, cheque, transferencia..." value="{{ old('referencia') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="ejercicio_fiscal_id">Ejercicio Fiscal</label>
                    <select id="ejercicio_fiscal_id" name="ejercicio_fiscal_id" class="form-control">
                        <option value="">— Sin asociar —</option>
                        @foreach($ejercicios as $ej)
                            <option value="{{ $ej->id }}" {{ old('ejercicio_fiscal_id') == $ej->id ? 'selected' : '' }}>
                                {{ $ej->anio }} — {{ $ej->descripcion ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Observaciones --}}
            <div class="form-group">
                <label class="form-label" for="observaciones">Observaciones</label>
                <textarea id="observaciones" name="observaciones" class="form-control" rows="3"
                    placeholder="Notas adicionales...">{{ old('observaciones') }}</textarea>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:16px;border-top:1px solid var(--border);">
                <a href="{{ route('presupuesto.movimientos-partidas.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Registrar Movimiento
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const selPartida    = document.getElementById('partida_presupuestaria_id');
    const selTipo       = document.getElementById('tipo');
    const wrapContra    = document.getElementById('wrap-contrapartida');
    const selContra     = document.getElementById('partida_contrapartida_id');
    const infoSaldo     = document.getElementById('info-saldo');
    const txtSaldo      = document.getElementById('txt-saldo');

    // Mostrar saldo de la partida seleccionada
    function actualizarSaldo() {
        const opt = selPartida.selectedOptions[0];
        if (!opt || !opt.value) { infoSaldo.style.display = 'none'; return; }
        const saldo = parseFloat(opt.dataset.saldo || 0);
        txtSaldo.textContent = 'Bs. ' + saldo.toLocaleString('es-VE', {minimumFractionDigits:2, maximumFractionDigits:2});
        infoSaldo.style.display = 'block';

        // Ocultar la partida seleccionada del selector de contrapartida
        Array.from(selContra.options).forEach(o => {
            o.hidden = (o.value && o.value === opt.value);
        });
    }

    // Mostrar/ocultar campo de contrapartida según tipo
    function toggleContrapartida() {
        const esModif = selTipo.value === 'modificacion_entrada';
        wrapContra.style.display = esModif ? 'block' : 'none';
        selContra.required = esModif;
        if (!esModif) selContra.value = '';
    }

    selPartida.addEventListener('change', actualizarSaldo);
    selTipo.addEventListener('change', toggleContrapartida);

    // Inicializar
    actualizarSaldo();
    toggleContrapartida();
})();
</script>
@endpush
