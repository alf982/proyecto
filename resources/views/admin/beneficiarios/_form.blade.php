{{-- Formulario compartido para create y edit --}}
@if($errors->any())
<div class="alert alert-danger" style="margin-bottom:16px;">
    <ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    {{-- Datos principales --}}
    <div>
        <h3 style="font-size:14px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:14px;">Datos Principales</h3>
        <div style="display:grid;gap:14px;">
            <div>
                <label class="form-label">RIF <span style="color:var(--accent-danger);">*</span></label>
                <input type="text" name="rif" id="inp-rif" class="form-control" placeholder="J-12345678-9"
                    value="{{ old('rif', $beneficiario?->rif) }}" required maxlength="12" autocomplete="off">
            </div>
            <div>
                <label class="form-label">Razón Social <span style="color:var(--accent-danger);">*</span></label>
                <input type="text" name="razon_social" class="form-control" placeholder="Nombre legal del beneficiario"
                    value="{{ old('razon_social', $beneficiario?->razon_social) }}" required>
            </div>
            <div>
                <label class="form-label">Nombre Comercial</label>
                <input type="text" name="nombre_comercial" class="form-control" placeholder="Nombre comercial (opcional)"
                    value="{{ old('nombre_comercial', $beneficiario?->nombre_comercial) }}">
            </div>
            <div>
                <label class="form-label">Tipo <span style="color:var(--accent-danger);">*</span></label>
                <select name="tipo" class="form-control" required>
                    @foreach(['proveedor'=>'Proveedor','contratista'=>'Contratista','funcionario'=>'Funcionario','otro'=>'Otro'] as $val => $label)
                        <option value="{{ $val }}" {{ old('tipo', $beneficiario?->tipo) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" id="inp-tel" class="form-control" placeholder="0414-1234567"
                        value="{{ old('telefono', $beneficiario?->telefono) }}" maxlength="12" autocomplete="off">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com"
                        value="{{ old('email', $beneficiario?->email) }}">
                </div>
            </div>
            <div>
                <label class="form-label">Dirección</label>
                <textarea name="direccion" class="form-control" rows="2" placeholder="Dirección fiscal del beneficiario">{{ old('direccion', $beneficiario?->direccion) }}</textarea>
            </div>
        </div>
    </div>

    {{-- Datos bancarios + observaciones --}}
    <div>
        <h3 style="font-size:14px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:14px;">Datos Bancarios</h3>
        <div style="display:grid;gap:14px;">
            <div>
                <label class="form-label">Banco</label>
                <select name="banco_nombre" class="form-control">
                    <option value="">— Seleccionar banco —</option>
                    @foreach(['Banco de Venezuela','Banesco','Mercantil','BBVA Provincial','Bancaribe','BOD','Fondo Común','Banco Activo','Bicentenario','Banco Agrícola','Otro'] as $banco)
                        <option value="{{ $banco }}" {{ old('banco_nombre', $beneficiario?->banco_nombre) === $banco ? 'selected' : '' }}>{{ $banco }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Número de Cuenta</label>
                <input type="text" name="banco_cuenta" id="inp-cuenta" class="form-control"
                    placeholder="0102-0000-00-0000000000"
                    value="{{ old('banco_cuenta', $beneficiario?->banco_cuenta) }}"
                    maxlength="22" autocomplete="off" style="font-family:monospace;letter-spacing:.5px">
            </div>
            <div>
                <label class="form-label">Tipo de Cuenta</label>
                <select name="banco_tipo_cuenta" class="form-control">
                    <option value="">— Seleccionar —</option>
                    <option value="corriente" {{ old('banco_tipo_cuenta', $beneficiario?->banco_tipo_cuenta) === 'corriente' ? 'selected' : '' }}>Corriente</option>
                    <option value="ahorro"    {{ old('banco_tipo_cuenta', $beneficiario?->banco_tipo_cuenta) === 'ahorro'    ? 'selected' : '' }}>Ahorro</option>
                    <option value="otro"      {{ old('banco_tipo_cuenta', $beneficiario?->banco_tipo_cuenta) === 'otro'      ? 'selected' : '' }}>Otro</option>
                </select>
            </div>
            <div>
                <label class="form-label">Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="4" placeholder="Notas adicionales...">{{ old('observaciones', $beneficiario?->observaciones) }}</textarea>
            </div>
        </div>
    </div>
</div>
<script>
// ── Máscara: Número de Cuenta Bancaria  0102-0000-00-0000000000 ──────────
// Grupos: 4 - 4 - 2 - 10
const cuentaInput = document.getElementById('inp-cuenta');
if (cuentaInput) {
    function formatCuenta(raw) {
        const d = raw.replace(/\D/g, '').slice(0, 20);
        let out = '';
        if (d.length > 0)  out += d.slice(0,  4);
        if (d.length > 4)  out += '-' + d.slice(4, 8);
        if (d.length > 8)  out += '-' + d.slice(8, 10);
        if (d.length > 10) out += '-' + d.slice(10, 20);
        return out;
    }
    cuentaInput.addEventListener('input', function (e) {
        const pos = this.selectionStart;
        const raw = this.value;
        const formatted = formatCuenta(raw);
        this.value = formatted;
        // Mantener posición del cursor
        const diff = formatted.length - raw.length;
        this.setSelectionRange(pos + diff, pos + diff);
    });
    cuentaInput.addEventListener('keydown', function (e) {
        // Permitir backspace eliminar separadores
        if (e.key === 'Backspace') {
            const pos = this.selectionStart;
            if (pos > 0 && this.value[pos - 1] === '-') {
                e.preventDefault();
                this.value = this.value.slice(0, pos - 2) + this.value.slice(pos);
                this.setSelectionRange(pos - 2, pos - 2);
            }
        }
    });
    // Formatear valor inicial si viene de old()
    if (cuentaInput.value) cuentaInput.value = formatCuenta(cuentaInput.value);
}

// ── Máscara: RIF  J-12345678-9 ───────────────────────────────────────────
const rifInput = document.getElementById('inp-rif');
if (rifInput) {
    function formatRif(raw) {
        // Tipo: J V E G P
        const upper = raw.toUpperCase();
        const tipo  = upper.match(/^[JVEGP]/) ? upper[0] : '';
        const nums  = upper.replace(/[^0-9]/g, '').slice(0, 9);
        const verif = upper.match(/([0-9])-([0-9])$/) ? '' : '';
        // Separar dígito verificador (último)
        if (!tipo) return nums.slice(0, 9);
        let out = tipo + '-' + nums.slice(0, 8);
        if (nums.length >= 9) out += '-' + nums[8];
        else if (nums.length > 0 && nums.length === 9) out += '-' + nums.slice(8);
        return out;
    }
    rifInput.addEventListener('input', function () {
        const pos  = this.selectionStart;
        const prev = this.value;
        const next = formatRif(this.value);
        this.value = next;
        const diff = next.length - prev.length;
        const newPos = Math.max(0, pos + diff);
        this.setSelectionRange(newPos, newPos);
    });
    if (rifInput.value) rifInput.value = formatRif(rifInput.value);
}

// ── Máscara: Teléfono  0414-1234567 ─────────────────────────────────────
const telInput = document.getElementById('inp-tel');
if (telInput) {
    function formatTel(raw) {
        const d = raw.replace(/\D/g, '').slice(0, 11);
        if (d.length <= 4) return d;
        return d.slice(0, 4) + '-' + d.slice(4, 11);
    }
    telInput.addEventListener('input', function () {
        const pos  = this.selectionStart;
        const prev = this.value;
        const next = formatTel(this.value);
        this.value = next;
        const diff = next.length - prev.length;
        this.setSelectionRange(Math.max(0, pos + diff), Math.max(0, pos + diff));
    });
    if (telInput.value) telInput.value = formatTel(telInput.value);
}
</script>
