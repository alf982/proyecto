{{--
    Partial: selector de ícono y color para formularios de rol.
    Variables esperadas: $iconoActual (string FA), $colorActual (hex), $descActual (string)
--}}
@php
$iconosDisponibles = [
    // Sistema / Admin
    'fa-user-shield','fa-user-tie','fa-crown','fa-gear','fa-gears','fa-screwdriver-wrench',
    'fa-lock','fa-key','fa-shield-halved','fa-fingerprint',
    // Finanzas / Presupuesto
    'fa-chart-bar','fa-chart-line','fa-chart-pie','fa-coins','fa-money-bill-wave',
    'fa-calculator','fa-receipt','fa-file-invoice','fa-file-invoice-dollar',
    // Tesorería / Pagos
    'fa-piggy-bank','fa-vault','fa-landmark','fa-credit-card','fa-money-bill-transfer',
    'fa-hand-holding-dollar','fa-cash-register',
    // Compras / Almacén
    'fa-cart-shopping','fa-truck','fa-boxes-stacked','fa-warehouse','fa-barcode',
    'fa-clipboard-list','fa-magnifying-glass-dollar',
    // Bienes / Inventario
    'fa-box-archive','fa-box','fa-cubes','fa-cube','fa-tags','fa-tag',
    // Nómina / RRHH
    'fa-id-badge','fa-id-card','fa-users','fa-user-group','fa-user-clock',
    'fa-briefcase','fa-building',
    // Ingresos
    'fa-fb7185','fa-hand-holding-dollar','fa-sack-dollar',
    // Reportes
    'fa-file-pdf','fa-print','fa-database','fa-table-cells',
    // Generales
    'fa-eye','fa-bell','fa-envelope','fa-star','fa-circle-info','fa-flag',
];

$coloresPaleta = [
    '#7c5cfc','#4f8ef7','#22d3a6','#f7b94f','#f97316',
    '#a78bfa','#34d399','#fb7185','#8a91a8','#f43f5e',
    '#06b6d4','#10b981','#f59e0b','#3b82f6','#ec4899',
    '#6366f1','#14b8a6','#84cc16','#ef4444','#d946ef',
];
@endphp

<div class="card" style="padding:1.25rem;margin-bottom:1rem">
    <h3 style="font-size:.85rem;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:1rem">
        <i class="fa-solid fa-palette"></i> Apariencia del Rol
    </h3>

    {{-- Preview --}}
    <div style="display:flex;align-items:center;gap:1rem;padding:1rem;background:var(--bg-body);border-radius:10px;margin-bottom:1.25rem;border:1px solid var(--border)">
        <div id="preview-avatar"
             style="width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;
                    background:color-mix(in srgb, {{ $colorActual }} 15%, transparent);
                    color:{{ $colorActual }}">
            <i class="fa-solid {{ $iconoActual }}" id="preview-icono"></i>
        </div>
        <div>
            <div style="font-size:.75rem;color:var(--text-muted)">Vista previa</div>
            <div id="preview-nombre" style="font-size:.9rem;font-weight:700;color:var(--text-primary)">—</div>
            <div id="preview-color-label" style="font-size:.72rem;color:var(--text-muted)">{{ $colorActual }}</div>
        </div>
        <div id="preview-strip" style="margin-left:auto;width:40px;height:40px;border-radius:8px;background:{{ $colorActual }}"></div>
    </div>

    {{-- Descripción --}}
    <div class="form-group" style="margin-bottom:1.25rem">
        <label class="form-label" style="font-size:.8rem">Descripción del rol</label>
        <input type="text" name="descripcion" class="form-control" style="font-size:.85rem"
               value="{{ old('descripcion', $descActual ?? '') }}"
               placeholder="Ej: Gestión de compras y almacén institucional" maxlength="200">
    </div>

    {{-- Selector de color --}}
    <div style="margin-bottom:1.25rem">
        <label class="form-label" style="font-size:.8rem;margin-bottom:.5rem">Color del rol</label>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem;align-items:center">
            @foreach($coloresPaleta as $c)
            <button type="button" class="color-dot {{ ($colorActual === $c) ? 'color-dot-active' : '' }}"
                    style="background:{{ $c }}" data-color="{{ $c }}"
                    onclick="seleccionarColor('{{ $c }}', this)" title="{{ $c }}"></button>
            @endforeach
            {{-- Input hex personalizado --}}
            <input type="color" id="color-custom" value="{{ $colorActual }}"
                   style="width:32px;height:32px;border-radius:50%;border:2px solid var(--border);cursor:pointer;padding:1px"
                   title="Color personalizado"
                   onchange="seleccionarColor(this.value, null)">
        </div>
        <input type="hidden" name="color" id="color-input" value="{{ old('color', $colorActual) }}">
    </div>

    {{-- Selector de ícono --}}
    <div>
        <label class="form-label" style="font-size:.8rem;margin-bottom:.5rem">Ícono del rol</label>
        <input type="text" id="icono-search" class="form-control"
               style="font-size:.82rem;margin-bottom:.6rem" placeholder="Buscar ícono...">
        <div id="icono-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(42px,1fr));gap:.35rem;max-height:220px;overflow-y:auto;padding:.25rem">
            @foreach($iconosDisponibles as $ic)
            <button type="button"
                    class="icono-btn {{ ($iconoActual === $ic) ? 'icono-active' : '' }}"
                    data-icono="{{ $ic }}"
                    onclick="seleccionarIcono('{{ $ic }}', this)"
                    title="{{ str_replace(['fa-','-'], ['','  '], $ic) }}">
                <i class="fa-solid {{ $ic }}"></i>
            </button>
            @endforeach
        </div>
        <input type="hidden" name="icono" id="icono-input" value="{{ old('icono', $iconoActual) }}">
    </div>
</div>

<style>
.color-dot {
    width:28px;height:28px;border-radius:50%;border:2px solid transparent;
    cursor:pointer;transition:transform .15s,border-color .15s;flex-shrink:0;
}
.color-dot:hover { transform:scale(1.2); }
.color-dot-active { border-color:var(--text-primary) !important; transform:scale(1.2); box-shadow:0 0 0 3px rgba(255,255,255,.3); }

.icono-btn {
    width:42px;height:42px;border-radius:8px;border:1px solid var(--border);
    background:transparent;color:var(--text-secondary);cursor:pointer;
    display:flex;align-items:center;justify-content:center;font-size:.95rem;
    transition:all .15s;
}
.icono-btn:hover { background:var(--bg-body); color:var(--text-primary); border-color:var(--accent); }
.icono-active { background:var(--accent) !important; color:#fff !important; border-color:var(--accent) !important; }
</style>

<script>
(function() {
    // Preview inicial
    const nombreInput = document.querySelector('input[name="name"]');
    if (nombreInput) {
        nombreInput.addEventListener('input', () => {
            document.getElementById('preview-nombre').textContent =
                nombreInput.value.replace(/-/g,' ').replace(/\b\w/g,c=>c.toUpperCase()) || '—';
        });
        // Inicializa con valor actual
        document.getElementById('preview-nombre').textContent =
            nombreInput.value.replace(/-/g,' ').replace(/\b\w/g,c=>c.toUpperCase()) || '—';
    }

    window.seleccionarColor = function(hex, btn) {
        document.getElementById('color-input').value = hex;
        document.getElementById('color-custom').value = hex;
        document.getElementById('preview-color-label').textContent = hex;
        document.getElementById('preview-strip').style.background = hex;

        // Actualizar avatar
        const avatar = document.getElementById('preview-avatar');
        avatar.style.background = `color-mix(in srgb, ${hex} 15%, transparent)`;
        avatar.style.color = hex;

        // Actualizar ícono activo
        document.querySelectorAll('.icono-active').forEach(b => {
            b.style.background = hex;
            b.style.borderColor = hex;
        });

        // Marca el dot activo
        document.querySelectorAll('.color-dot').forEach(d => d.classList.remove('color-dot-active'));
        if (btn) btn.classList.add('color-dot-active');
    };

    window.seleccionarIcono = function(fa, btn) {
        document.getElementById('icono-input').value = fa;
        document.getElementById('preview-icono').className = `fa-solid ${fa}`;

        const color = document.getElementById('color-input').value;
        document.querySelectorAll('.icono-btn').forEach(b => {
            b.classList.remove('icono-active');
            b.style.background = '';
            b.style.borderColor = '';
            b.style.color = '';
        });
        if (btn) {
            btn.classList.add('icono-active');
            btn.style.background = color;
            btn.style.borderColor = color;
            btn.style.color = '#fff';
        }
    };

    // Buscador de íconos
    document.getElementById('icono-search').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.icono-btn').forEach(btn => {
            btn.style.display = btn.dataset.icono.includes(q) ? '' : 'none';
        });
    });

    // Aplica color actual al botón activo (para edit)
    const colorActual = document.getElementById('color-input').value;
    const iconoActual = document.getElementById('icono-input').value;
    if (iconoActual) {
        const btn = document.querySelector(`.icono-btn[data-icono="${iconoActual}"]`);
        if (btn) seleccionarIcono(iconoActual, btn);
    }
})();
</script>
