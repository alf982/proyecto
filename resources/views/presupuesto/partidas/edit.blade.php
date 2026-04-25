@extends('layouts.app')
@section('title', 'Editar Partida')
@section('breadcrumb')
    <a href="{{ route('presupuesto.partidas.index') }}" style="color:var(--text-secondary);text-decoration:none;">Partidas</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Editar</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Editar Partida</h1>
        <p class="page-subtitle">
            <code style="background:rgba(79,142,247,0.12);color:var(--accent);padding:2px 10px;border-radius:5px;font-size:15px;">
                {{ $partida->codigo }}
            </code>
        </p>
    </div>
    <a href="{{ route('presupuesto.partidas.index') }}" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

<div class="card fade-up" style="animation-delay:.05s;max-width:680px;">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-list-ol" style="color:var(--accent);margin-right:8px;"></i>Datos de la Partida</div>
        <span class="badge {{ $partida->activo ? 'badge-active' : 'badge-danger' }}">
            {{ $partida->activo ? 'Activa' : 'Inactiva' }}
        </span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('presupuesto.partidas.update', $partida) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label" for="codigo">Código ONAPRE *</label>
                <input type="text" id="codigo" name="codigo" class="form-control"
                    value="{{ old('codigo', $partida->codigo) }}" required
                    placeholder="4.01.00.00.00"
                    maxlength="14"
                    style="font-family:monospace;font-size:18px;letter-spacing:3px;">
                <div style="font-size:11px;color:var(--text-secondary);margin-top:5px;">
                    Formato: <code style="color:var(--accent);">4.XX.XX.XX.XX</code>
                    — ejemplo: <code style="color:var(--accent);">4.01.03.01.00</code>
                </div>
                @error('codigo')
                    <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="descripcion">Descripción *</label>
                <input type="text" id="descripcion" name="descripcion" class="form-control"
                    value="{{ old('descripcion', $partida->descripcion) }}" required>
                @error('descripcion')
                    <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="saldo_actual">Saldo Actual (Bs.)</label>
                <input type="number" id="saldo_actual" name="saldo_actual" class="form-control"
                    value="{{ old('saldo_actual', $partida->saldo_actual) }}" min="0" step="0.01"
                    style="font-size:16px;font-weight:700;font-family:monospace;">
                <div style="font-size:11px;color:var(--accent-warn);margin-top:4px;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Solo modificar si hay un ajuste manual. Los movimientos actualizan este valor automáticamente.
                </div>
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
                        <option value="{{ $cuenta->id }}"
                            {{ old('cuenta_bancaria_id', $partida->cuenta_bancaria_id) == $cuenta->id ? 'selected' : '' }}>
                            {{ $cuenta->nombre }} — {{ $cuenta->banco }} ({{ $cuenta->numero_cuenta }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="observaciones">Observaciones</label>
                <textarea id="observaciones" name="observaciones" class="form-control" rows="3">{{ old('observaciones', $partida->observaciones) }}</textarea>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;padding-top:16px;border-top:1px solid var(--border);">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;">
                    <input type="checkbox" name="activo" value="1"
                        {{ old('activo', $partida->activo) ? 'checked' : '' }}
                        style="accent-color:var(--accent);width:16px;height:16px;">
                    <span>Partida activa</span>
                </label>
                <div style="display:flex;gap:10px;">
                    <a href="{{ route('presupuesto.partidas.index') }}" class="btn btn-outline">Cancelar</a>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar Cambios</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Formato ONAPRE: 4.XX.XX.XX.XX
    const inp = document.getElementById('codigo');

    function formatCodigo(raw) {
        let digits = raw.replace(/\D/g, '');
        if (!digits.startsWith('4')) digits = '4' + digits.replace(/^4*/, '');
        digits = digits.slice(0, 9);

        const result = [digits.slice(0, 1) || '4'];
        if (digits.length > 1) result.push(digits.slice(1, 3));
        if (digits.length > 3) result.push(digits.slice(3, 5));
        if (digits.length > 5) result.push(digits.slice(5, 7));
        if (digits.length > 7) result.push(digits.slice(7, 9));

        return result.join('.');
    }

    // Formatear el valor existente al cargar la página
    inp.value = formatCodigo(inp.value);

    inp.addEventListener('input', function () {
        const selStart = this.selectionStart;
        const prevLen  = this.value.length;
        this.value = formatCodigo(this.value);
        const delta = this.value.length - prevLen;
        this.setSelectionRange(selStart + delta, selStart + delta);
    });

    inp.addEventListener('keydown', function (e) {
        if (['Backspace','Delete','ArrowLeft','ArrowRight','Tab','Home','End'].includes(e.key)) return;
        if (!/^\d$/.test(e.key)) e.preventDefault();
        if (this.selectionStart === 0 && e.key !== '4') e.preventDefault();
    });
})();
</script>
@endpush
