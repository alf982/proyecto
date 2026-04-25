@extends('layouts.app')
@section('title','Nueva Cuenta Bancaria')
@section('breadcrumb')
    <span>Tesorería</span><i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <a href="{{ route('tesoreria.cuentas.index') }}">Cuentas Bancarias</a><i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Nueva</span>
@endsection
@section('content')
<div class="page-header fade-up"><h1 class="page-title">Nueva Cuenta Bancaria</h1></div>
<div class="card fade-up">
    <div class="card-body">
        <form method="POST" action="{{ route('tesoreria.cuentas.store') }}">
            @csrf
            @if($errors->any())
            <div class="alert alert-danger" style="margin-bottom:16px;">
                <ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div style="display:grid;gap:14px;">
                    <div>
                        <label class="form-label">Nombre Identificador <span style="color:var(--accent-danger);">*</span></label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Cuenta Principal BDV" value="{{ old('nombre') }}" required>
                    </div>
                    <div>
                        <label class="form-label">Banco <span style="color:var(--accent-danger);">*</span></label>
                        <select name="banco" class="form-control" required>
                            <option value="">— Seleccionar —</option>
                            @foreach(['Banco de Venezuela','Banesco','Mercantil','BBVA Provincial','Bancaribe','BOD','Fondo Común','Bicentenario','Banco Activo','Banco Agrícola','Otro'] as $banco)
                                <option value="{{ $banco }}" {{ old('banco') === $banco ? 'selected' : '' }}>{{ $banco }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Número de Cuenta <span style="color:var(--accent-danger);">*</span></label>
                        <input type="text" name="numero_cuenta" id="inp-nro-cuenta"
                            class="form-control" placeholder="0102-0000-00-0000000000"
                            value="{{ old('numero_cuenta') }}" required
                            maxlength="22" autocomplete="off"
                            style="font-family:monospace;letter-spacing:.5px">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label class="form-label">Tipo <span style="color:var(--accent-danger);">*</span></label>
                            <select name="tipo" class="form-control" required>
                                <option value="corriente" {{ old('tipo')==='corriente'?'selected':'' }}>Corriente</option>
                                <option value="ahorro"    {{ old('tipo')==='ahorro'   ?'selected':'' }}>Ahorro</option>
                                <option value="fondo"     {{ old('tipo')==='fondo'    ?'selected':'' }}>Fondo</option>
                                <option value="otro"      {{ old('tipo')==='otro'     ?'selected':'' }}>Otro</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Moneda</label>
                            <select name="moneda" class="form-control">
                                <option value="VES" {{ old('moneda','VES')==='VES'?'selected':'' }}>VES (Bolívar)</option>
                                <option value="USD" {{ old('moneda')==='USD'?'selected':'' }}>USD (Dólar)</option>
                                <option value="EUR" {{ old('moneda')==='EUR'?'selected':'' }}>EUR (Euro)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div style="display:grid;gap:14px;">
                    <div>
                        <label class="form-label">Saldo Inicial <span style="color:var(--accent-danger);">*</span></label>
                        <input type="number" name="saldo_inicial" class="form-control" step="0.01" min="0" placeholder="0.00" value="{{ old('saldo_inicial', 0) }}" required>
                    </div>
                    <div>
                        <label class="form-label">Fecha de Apertura</label>
                        <input type="date" name="fecha_apertura" class="form-control" value="{{ old('fecha_apertura') }}">
                    </div>
                    <div>
                        <label class="form-label">Firmante Principal</label>
                        <input type="text" name="firmante_1" class="form-control" placeholder="Nombre del firmante" value="{{ old('firmante_1') }}">
                    </div>
                    <div>
                        <label class="form-label">Segundo Firmante</label>
                        <input type="text" name="firmante_2" class="form-control" placeholder="Co-firmante (opcional)" value="{{ old('firmante_2') }}">
                    </div>
                    <div>
                        <label class="form-label">Ejercicio Fiscal</label>
                        <select name="ejercicio_fiscal_id" class="form-control">
                            <option value="">— Ninguno —</option>
                            @foreach($ejercicios as $ej)
                                <option value="{{ $ej->id }}" {{ old('ejercicio_fiscal_id') == $ej->id ? 'selected' : '' }}>{{ $ej->anio }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="2" placeholder="Notas...">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:24px;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Registrar Cuenta</button>
                <a href="{{ route('tesoreria.cuentas.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    function formatCuentaBancaria(raw) {
        const d = raw.replace(/\D/g, '').slice(0, 20);
        let out = '';
        if (d.length > 0)  out += d.slice(0, 4);
        if (d.length > 4)  out += '-' + d.slice(4, 8);
        if (d.length > 8)  out += '-' + d.slice(8, 10);
        if (d.length > 10) out += '-' + d.slice(10, 20);
        return out;
    }
    const inp = document.getElementById('inp-nro-cuenta');
    if (!inp) return;
    inp.addEventListener('input', function() {
        const pos = this.selectionStart;
        const prev = this.value;
        const next = formatCuentaBancaria(this.value);
        this.value = next;
        const diff = next.length - prev.length;
        this.setSelectionRange(Math.max(0, pos + diff), Math.max(0, pos + diff));
    });
    inp.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace') {
            const pos = this.selectionStart;
            if (pos > 0 && this.value[pos - 1] === '-') {
                e.preventDefault();
                this.value = this.value.slice(0, pos - 2) + this.value.slice(pos);
                this.setSelectionRange(pos - 2, pos - 2);
            }
        }
    });
    if (inp.value) inp.value = formatCuentaBancaria(inp.value);
})();
</script>
@endpush
