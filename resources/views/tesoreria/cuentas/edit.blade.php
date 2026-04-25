@extends('layouts.app')
@section('title','Editar Cuenta')
@section('breadcrumb')
    <span>Tesorería</span><i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <a href="{{ route('tesoreria.cuentas.index') }}">Cuentas</a><i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Editar</span>
@endsection
@section('content')
<div class="page-header fade-up"><h1 class="page-title">Editar: {{ $cuenta->nombre }}</h1></div>
<div class="card fade-up">
    <div class="card-body">
        <form method="POST" action="{{ route('tesoreria.cuentas.update', $cuenta) }}">
            @csrf @method('PUT')
            @if($errors->any())
            <div class="alert alert-danger" style="margin-bottom:16px;"><ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div style="display:grid;gap:14px;">
                    <div>
                        <label class="form-label">Nombre <span style="color:var(--accent-danger);">*</span></label>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $cuenta->nombre) }}" required>
                    </div>
                    <div>
                        <label class="form-label">Banco <span style="color:var(--accent-danger);">*</span></label>
                        <input type="text" name="banco" class="form-control" value="{{ old('banco', $cuenta->banco) }}" required>
                    </div>
                    <div>
                        <label class="form-label">Número de Cuenta <span style="color:var(--accent-danger);">*</span></label>
                        <input type="text" name="numero_cuenta" id="inp-nro-cuenta"
                            class="form-control"
                            value="{{ old('numero_cuenta', $cuenta->numero_cuenta) }}" required
                            maxlength="22" autocomplete="off"
                            style="font-family:monospace;letter-spacing:.5px">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label class="form-label">Tipo</label>
                            <select name="tipo" class="form-control">
                                @foreach(['corriente','ahorro','fondo','otro'] as $t)
                                    <option value="{{ $t }}" {{ old('tipo',$cuenta->tipo)===$t?'selected':'' }}>{{ ucfirst($t) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Moneda</label>
                            <select name="moneda" class="form-control">
                                @foreach(['VES','USD','EUR'] as $m)
                                    <option value="{{ $m }}" {{ old('moneda',$cuenta->moneda)===$m?'selected':'' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div style="display:grid;gap:14px;">
                    <div>
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-control">
                            @foreach(['activa','inactiva','bloqueada'] as $e)
                                <option value="{{ $e }}" {{ old('estado',$cuenta->estado)===$e?'selected':'' }}>{{ ucfirst($e) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Firmante Principal</label>
                        <input type="text" name="firmante_1" class="form-control" value="{{ old('firmante_1', $cuenta->firmante_1) }}">
                    </div>
                    <div>
                        <label class="form-label">Segundo Firmante</label>
                        <input type="text" name="firmante_2" class="form-control" value="{{ old('firmante_2', $cuenta->firmante_2) }}">
                    </div>
                    <div>
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3">{{ old('observaciones', $cuenta->observaciones) }}</textarea>
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:24px;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Actualizar</button>
                <a href="{{ route('tesoreria.cuentas.show', $cuenta) }}" class="btn btn-outline">Cancelar</a>
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
