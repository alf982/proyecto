@extends('layouts.app')
@section('title', 'Nueva Partida')
@section('breadcrumb')
    <a href="{{ route('presupuesto.partidas.index') }}" style="color:var(--text-secondary);text-decoration:none;">Partidas</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Nueva</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Nueva Partida Presupuestaria</h1>
        <p class="page-subtitle">Registra una nueva partida en el clasificador presupuestario</p>
    </div>
    <a href="{{ route('presupuesto.partidas.index') }}" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

<div class="card fade-up" style="animation-delay:.05s;max-width:680px;">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-list-ol" style="color:var(--accent);margin-right:8px;"></i>Datos de la Partida</div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('presupuesto.partidas.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="codigo">Código ONAPRE *</label>
                <input type="text" id="codigo" name="codigo" class="form-control"
                    value="{{ old('codigo', '4.') }}" required
                    placeholder="4.01.00.00.00"
                    maxlength="14"
                    style="font-family:monospace;font-size:18px;letter-spacing:3px;">
                <div style="font-size:11px;color:var(--text-secondary);margin-top:5px;">
                    Formato: <code style="color:var(--accent);">4.XX.XX.XX.XX</code>
                    — ejemplo: <code style="color:var(--accent);">4.01.03.01.00</code>
                    &nbsp;|&nbsp; Partida → Genérica → Específica → Sub-específica
                </div>
                @error('codigo')
                    <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="descripcion">Descripción *</label>
                <input type="text" id="descripcion" name="descripcion" class="form-control"
                    value="{{ old('descripcion') }}" required
                    placeholder="Descripción oficial de la partida">
                @error('descripcion')
                    <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="saldo_actual">Saldo Inicial (Bs.)</label>
                <input type="number" id="saldo_actual" name="saldo_actual" class="form-control"
                    value="{{ old('saldo_actual', 0) }}" min="0" step="0.01"
                    style="font-size:16px;font-weight:700;font-family:monospace;">
                <div style="font-size:11px;color:var(--text-secondary);margin-top:4px;">Crédito presupuestario inicial asignado a esta partida.</div>
                @error('saldo_actual')<div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="cuenta_bancaria_id">
                    <i class="fa-solid fa-building-columns" style="color:var(--accent);margin-right:6px;"></i>
                    Cuenta Bancaria Receptora
                </label>
                <select id="cuenta_bancaria_id" name="cuenta_bancaria_id" class="form-control">
                    <option value="">— Sin asignar —</option>
                    @foreach($cuentas as $cuenta)
                        <option value="{{ $cuenta->id }}" {{ old('cuenta_bancaria_id') == $cuenta->id ? 'selected' : '' }}>
                            {{ $cuenta->nombre }} — {{ $cuenta->banco }} ({{ $cuenta->numero_cuenta }})
                        </option>
                    @endforeach
                </select>
                <div style="font-size:11px;color:var(--text-secondary);margin-top:5px;">
                    Cuenta donde el Estado deposita los fondos asignados a esta partida.
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="observaciones">Observaciones</label>
                <textarea id="observaciones" name="observaciones" class="form-control" rows="3"
                    placeholder="Notas adicionales...">{{ old('observaciones') }}</textarea>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:16px;border-top:1px solid var(--border);">
                <a href="{{ route('presupuesto.partidas.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Formato ONAPRE: 4.XX.XX.XX.XX
    // Siempre empieza con '4', luego 4 grupos de 2 dígitos separados por punto
    const inp = document.getElementById('codigo');

    function formatCodigo(raw) {
        // Extraer solo dígitos, siempre forzar primer dígito = 4
        let digits = raw.replace(/\D/g, '');
        if (!digits.startsWith('4')) digits = '4' + digits.replace(/^4*/, '');
        digits = digits.slice(0, 9); // máx 9 dígitos: 4 + 8

        // Construir código con puntos
        const parts = [digits.slice(0, 1)]; // '4'
        if (digits.length > 1) parts.push(digits.slice(1, 3).padEnd(2, '_'));
        if (digits.length > 3) parts.push(digits.slice(3, 5).padEnd(2, '_'));
        if (digits.length > 5) parts.push(digits.slice(5, 7).padEnd(2, '_'));
        if (digits.length > 7) parts.push(digits.slice(7, 9).padEnd(2, '_'));

        // Solo incluir partes que tengan dígitos reales
        const result = [];
        result.push(digits.slice(0, 1) || '4');
        if (digits.length > 1) result.push(digits.slice(1, 3));
        if (digits.length > 3) result.push(digits.slice(3, 5));
        if (digits.length > 5) result.push(digits.slice(5, 7));
        if (digits.length > 7) result.push(digits.slice(7, 9));

        return result.join('.');
    }

    inp.addEventListener('input', function () {
        const selStart = this.selectionStart;
        const prevLen  = this.value.length;
        this.value = formatCodigo(this.value);
        const delta = this.value.length - prevLen;
        this.setSelectionRange(selStart + delta, selStart + delta);
    });

    inp.addEventListener('keydown', function (e) {
        // Permitir control keys
        if (['Backspace','Delete','ArrowLeft','ArrowRight','Tab','Home','End'].includes(e.key)) return;
        // Solo dígitos
        if (!/^\d$/.test(e.key)) e.preventDefault();
        // No permitir cambiar el primer '4'
        if (this.selectionStart === 0 && e.key !== '4') e.preventDefault();
    });

    inp.addEventListener('focus', function () {
        if (!this.value || this.value === '') this.value = '4.';
    });
})();
</script>
@endpush
