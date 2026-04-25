{{--
    Componente reutilizable de selección de retenciones.

    Uso:
        @include('components.retenciones-selector', [
            'modulo'           => 'pago',           // causacion|pago|orden_compra|nomina
            'inputMonto'       => 'monto_pagado',   // name del campo con el TOTAL (con IVA)
            'inputMontoBase'   => 'monto_sin_iva',  // name del campo con la BASE (sin IVA)
                                                    // Si no se pasa, se usa inputMonto para todo
            'retSeleccionadas' => [],               // IDs pre-seleccionados (para editar)
        ])
--}}
@php
    $modulo           = $modulo ?? 'causacion';
    $inputMonto       = $inputMonto ?? 'monto';       // total con IVA
    $inputMontoBase   = $inputMontoBase ?? null;      // base sin IVA (para ISLR etc.)
    $retSeleccionadas = $retSeleccionadas ?? [];
    $retenciones = \App\Models\Retencion::activas()
        ->paraModulo($modulo)
        ->orderBy('obligatoria', 'desc')
        ->orderBy('codigo')
        ->get();
@endphp

<div class="card" id="ret-selector-card" style="margin-top:20px;">
    <div class="card-header" style="cursor:pointer;user-select:none;" onclick="toggleRetCard()">
        <span class="card-title">
            <i class="fa-solid fa-percent" style="margin-right:6px;color:var(--accent-warn)"></i>
            Retenciones
            <span id="ret-count-badge" style="display:none;margin-left:8px;"></span>
        </span>
        <i class="fa-solid fa-chevron-down" id="ret-chevron" style="color:var(--text-secondary);transition:transform .25s;"></i>
    </div>

    <div id="ret-body" style="display:none;">
        <div class="card-body" style="padding-bottom:14px;">

            @if($retenciones->isNotEmpty())

            {{-- Buscador por código / nombre --}}
            <div style="margin-bottom:14px;">
                <div style="position:relative;">
                    <i class="fa-solid fa-magnifying-glass"
                       style="position:absolute;left:12px;top:50%;transform:translateY(-50%);
                              color:var(--text-secondary);font-size:13px;pointer-events:none;"></i>
                    <input type="text" id="ret-buscar"
                           placeholder="Buscar por código o nombre…"
                           oninput="filtrarRetenciones()"
                           autocomplete="off"
                           style="width:100%;padding:9px 12px 9px 36px;border-radius:9px;
                                  border:1px solid var(--border);background:var(--bg-input, rgba(255,255,255,0.05));
                                  color:var(--text);font-size:13px;outline:none;
                                  transition:border-color .2s;"
                           onfocus="this.style.borderColor='rgba(79,142,247,0.5)'"
                           onblur="this.style.borderColor='var(--border)'">
                    <button type="button" id="ret-buscar-clear"
                            onclick="document.getElementById('ret-buscar').value='';filtrarRetenciones()"
                            style="display:none;position:absolute;right:10px;top:50%;transform:translateY(-50%);
                                   background:none;border:none;color:var(--text-secondary);
                                   cursor:pointer;padding:4px;font-size:13px;"
                            title="Limpiar búsqueda">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div id="ret-buscar-info"
                     style="font-size:11px;color:var(--text-secondary);margin-top:4px;display:none;">
                </div>
            </div>

            {{-- Lista de retenciones --}}
            <div style="display:grid;gap:8px;margin-bottom:12px;" id="ret-lista">
                @foreach($retenciones as $ret)
                <label id="ret-row-{{ $ret->id }}"
                    data-codigo="{{ strtolower($ret->codigo) }}"
                    data-nombre="{{ strtolower($ret->nombre) }}"
                    style="display:flex;align-items:center;justify-content:space-between;gap:10px;
                           padding:10px 14px;border-radius:9px;cursor:pointer;
                           border:1px solid {{ $ret->obligatoria ? 'rgba(247,185,79,0.25)' : 'var(--border)' }};
                           background:{{ $ret->obligatoria ? 'rgba(247,185,79,0.05)' : 'rgba(255,255,255,0.02)' }};
                           transition:background .2s,border-color .2s;"
                    onmouseover="this.style.borderColor='rgba(79,142,247,0.35)';this.style.background='rgba(79,142,247,0.05)'"
                    onmouseout="this.style.borderColor='{{ $ret->obligatoria ? 'rgba(247,185,79,0.25)' : 'var(--border)' }}';this.style.background='{{ $ret->obligatoria ? 'rgba(247,185,79,0.05)' : 'rgba(255,255,255,0.02)' }}'">

                    <div style="display:flex;align-items:center;gap:10px;flex:1;">
                        <input type="checkbox"
                            name="retenciones[]"
                            value="{{ $ret->id }}"
                            id="ret-chk-{{ $ret->id }}"
                            data-ret-id="{{ $ret->id }}"
                            data-ret-tipo="{{ $ret->tipo }}"
                            data-ret-porcentaje="{{ $ret->porcentaje ?? 0 }}"
                            data-ret-alicuota-iva="{{ $ret->alicuota_iva ?? 0 }}"
                            data-ret-monto-fijo="{{ $ret->monto_fijo ?? 0 }}"
                            data-ret-base="{{ $ret->base_calculo }}"
                            style="width:15px;height:15px;accent-color:var(--accent);flex-shrink:0;"
                            {{ (in_array($ret->id, $retSeleccionadas) || $ret->obligatoria) ? 'checked' : '' }}
                            {{ $ret->obligatoria ? 'title="Retención obligatoria"' : '' }}
                            onchange="calcularRetenciones()">

                        <div>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="font-family:monospace;font-size:12px;background:rgba(79,142,247,0.1);color:var(--accent);padding:1px 6px;border-radius:4px;">{{ $ret->codigo }}</span>
                                <span style="font-size:13px;font-weight:500;">{{ $ret->nombre }}</span>
                                @if($ret->obligatoria)
                                    <span style="font-size:10px;color:var(--accent-warn);"><i class="fa-solid fa-lock"></i></span>
                                @endif
                            </div>
                            @if($ret->descripcion)
                            <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">{{ Str::limit($ret->descripcion, 80) }}</div>
                            @endif
                        </div>
                    </div>

                    <div style="text-align:right;flex-shrink:0;">
                        <div style="font-size:13px;font-weight:600;color:var(--accent-warn);">{{ $ret->tipo_label }}</div>
                        <div id="ret-monto-{{ $ret->id }}" style="font-size:11px;color:var(--text-secondary);margin-top:2px;">Bs. 0.00</div>
                    </div>
                </label>
                @endforeach
            </div>

            {{-- Paginación --}}
            <div id="ret-paginacion"
                 style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;gap:10px;">
                <div style="display:flex;align-items:center;gap:6px;">
                    <button type="button" id="ret-pag-prev"
                            onclick="retCambiarPagina(-1)"
                            style="padding:5px 12px;border-radius:7px;border:1px solid var(--border);
                                   background:rgba(79,142,247,0.06);color:var(--accent);font-size:12px;
                                   cursor:pointer;transition:background .2s;"
                            onmouseover="this.style.background='rgba(79,142,247,0.14)'"
                            onmouseout="this.style.background='rgba(79,142,247,0.06)'">
                        <i class="fa-solid fa-chevron-left"></i> Anterior
                    </button>
                    <button type="button" id="ret-pag-next"
                            onclick="retCambiarPagina(1)"
                            style="padding:5px 12px;border-radius:7px;border:1px solid var(--border);
                                   background:rgba(79,142,247,0.06);color:var(--accent);font-size:12px;
                                   cursor:pointer;transition:background .2s;"
                            onmouseover="this.style.background='rgba(79,142,247,0.14)'"
                            onmouseout="this.style.background='rgba(79,142,247,0.06)'">
                        Siguiente <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
                <div id="ret-pag-info" style="font-size:12px;color:var(--text-secondary);"></div>
            </div>

            {{-- Resumen --}}
            <div style="padding:12px 14px;background:rgba(247,185,79,0.07);border:1px solid rgba(247,185,79,0.2);border-radius:9px;display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:12px;color:var(--text-secondary);">Total Retenciones:</span>
                <strong id="ret-total" style="font-size:15px;color:var(--accent-warn);">Bs. 0.00</strong>
            </div>

            @else

            {{-- Estado vacío --}}
            <div id="ret-lista"></div>
            <div style="text-align:center;padding:20px 10px;">
                <i class="fa-solid fa-percent" style="font-size:28px;color:var(--text-secondary);opacity:.3;margin-bottom:10px;display:block;"></i>
                <div style="font-size:13px;color:var(--text-secondary);margin-bottom:12px;">
                    No hay retenciones configuradas para este módulo.<br>
                    <span style="font-size:11px;opacity:.7;">Módulo: <code>{{ $modulo }}</code></span>
                </div>
                @auth
                <a href="{{ route('retenciones.create') }}"
                   target="_blank"
                   style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--accent);text-decoration:none;
                          padding:6px 14px;border:1px solid rgba(79,142,247,0.3);border-radius:7px;
                          background:rgba(79,142,247,0.06);transition:background .2s;"
                   onmouseover="this.style.background='rgba(79,142,247,0.12)'"
                   onmouseout="this.style.background='rgba(79,142,247,0.06)'">
                    <i class="fa-solid fa-plus"></i> Configurar retenciones
                </a>
                @endauth
            </div>
            <strong id="ret-total" style="display:none;">0</strong>

            @endif

        </div>
    </div>
</div>

<script>
(function(){
    const POR_PAGINA = 6;
    let retPaginaActual  = 1;
    let retFilasVisibles = []; // filas que pasan el filtro de búsqueda

    // ─── Toggle card ─────────────────────────────────────────────────
    window.toggleRetCard = function() {
        const body    = document.getElementById('ret-body');
        const chevron = document.getElementById('ret-chevron');
        const visible = body.style.display !== 'none';
        body.style.display      = visible ? 'none' : 'block';
        chevron.style.transform = visible ? 'rotate(0deg)' : 'rotate(180deg)';
    };

    // ─── Obtener todas las filas del listado ─────────────────────────
    function obtenerFilas() {
        return Array.from(document.querySelectorAll('#ret-lista > label[id^="ret-row-"]'));
    }

    // ─── Filtrar por búsqueda y repaginar ────────────────────────────
    window.filtrarRetenciones = function() {
        const q      = (document.getElementById('ret-buscar')?.value || '').toLowerCase().trim();
        const clearBtn = document.getElementById('ret-buscar-clear');
        if (clearBtn) clearBtn.style.display = q ? 'block' : 'none';

        const filas = obtenerFilas();
        retFilasVisibles = filas.filter(f => {
            if (!q) return true;
            return f.dataset.codigo.includes(q) || f.dataset.nombre.includes(q);
        });

        // Ocultar todas primero
        filas.forEach(f => f.style.display = 'none');

        const info = document.getElementById('ret-buscar-info');
        if (q && info) {
            info.style.display = 'block';
            if (retFilasVisibles.length === 0) {
                info.textContent = 'Sin resultados para "' + q + '"';
                info.style.color = 'var(--accent-danger)';
            } else {
                info.textContent = retFilasVisibles.length + ' retención(es) encontrada(s)';
                info.style.color = 'var(--accent-3)';
            }
        } else if (info) {
            info.style.display = 'none';
        }

        retPaginaActual = 1;
        retMostrarPagina();
    };

    // ─── Mostrar página actual ────────────────────────────────────────
    function retMostrarPagina() {
        const desde   = (retPaginaActual - 1) * POR_PAGINA;
        const hasta   = desde + POR_PAGINA;
        const total   = retFilasVisibles.length;
        const totalPg = Math.ceil(total / POR_PAGINA) || 1;

        retFilasVisibles.forEach((f, i) => {
            f.style.display = (i >= desde && i < hasta) ? 'flex' : 'none';
        });

        // Botones
        const prev = document.getElementById('ret-pag-prev');
        const next = document.getElementById('ret-pag-next');
        if (prev) prev.disabled = retPaginaActual <= 1;
        if (next) next.disabled = retPaginaActual >= totalPg;
        if (prev) prev.style.opacity = retPaginaActual <= 1 ? '.4' : '1';
        if (next) next.style.opacity = retPaginaActual >= totalPg ? '.4' : '1';

        // Info de página
        const info = document.getElementById('ret-pag-info');
        if (info) {
            if (total <= POR_PAGINA) {
                info.textContent = total + ' retención(es)';
            } else {
                const desdeMost = Math.min(desde + 1, total);
                const hastaMost = Math.min(hasta, total);
                info.textContent = 'Mostrando ' + desdeMost + '–' + hastaMost + ' de ' + total + ' · Página ' + retPaginaActual + '/' + totalPg;
            }
        }

        // Ocultar paginación si caben todas
        const panelPag = document.getElementById('ret-paginacion');
        if (panelPag) panelPag.style.display = total <= POR_PAGINA ? 'none' : 'flex';
    }

    // ─── Cambiar página ───────────────────────────────────────────────
    window.retCambiarPagina = function(delta) {
        const total   = retFilasVisibles.length;
        const totalPg = Math.ceil(total / POR_PAGINA) || 1;
        retPaginaActual = Math.max(1, Math.min(totalPg, retPaginaActual + delta));
        retMostrarPagina();
    };

    // ─── Inicializar paginación al cargar ─────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        // Abrir si hay marcadas
        const hayMarcadas = document.querySelectorAll('#ret-lista input[type=checkbox]:checked').length > 0;
        if (hayMarcadas) {
            document.getElementById('ret-body').style.display    = 'block';
            document.getElementById('ret-chevron').style.transform = 'rotate(180deg)';
        }

        // Iniciar paginación (todas visibles)
        retFilasVisibles = obtenerFilas();
        retMostrarPagina();
        calcularRetenciones();
    });

    // ─── Cálculo de retenciones ───────────────────────────────────────
    window.calcularRetenciones = function() {
        // Monto TOTAL con IVA (para retenciones tipo porcentaje_iva)
        const montoInput = document.querySelector('[name="{{ $inputMonto }}"]');
        const montoTotal = montoInput ? parseFloat(montoInput.value.replace(',','.')) || 0 : 0;

        // Monto BASE sin IVA (para retenciones ISLR y similares tipo porcentaje)
        @if($inputMontoBase)
        const montoBaseInput = document.querySelector('[name="{{ $inputMontoBase }}"]');
        const montoBase      = montoBaseInput ? parseFloat(montoBaseInput.value.replace(',','.')) || 0 : montoTotal;
        @else
        const montoBase      = montoTotal;
        @endif

        let totalRetenido  = 0;
        let montoAcumulado = montoBase;
        let seleccionadas  = 0;

        // Iterar sobre TODOS los checkboxes (incluyendo los ocultos por paginación)
        document.querySelectorAll('#ret-lista input[type=checkbox]').forEach(function(chk) {
            const id         = chk.dataset.retId;
            const tipo       = chk.dataset.retTipo;
            const base       = chk.dataset.retBase;
            const alicuota   = parseFloat(chk.dataset.retAlicuotaIva || 0);
            const porcentaje = parseFloat(chk.dataset.retPorcentaje || 0);
            let monto        = 0;

            if (chk.checked) {
                if (tipo === 'porcentaje_iva') {
                    const baseImponible = montoTotal / (1 + alicuota / 100);
                    const montoIva      = baseImponible * (alicuota / 100);
                    monto = Math.round((montoIva * porcentaje / 100) * 100) / 100;
                } else if (tipo === 'porcentaje') {
                    const baseCal = base === 'monto_neto' ? montoAcumulado : montoBase;
                    monto = Math.round((baseCal * porcentaje / 100) * 100) / 100;
                    montoAcumulado -= monto;
                } else {
                    monto = parseFloat(chk.dataset.retMontoFijo || 0);
                }
                totalRetenido += monto;
                seleccionadas++;
            }

            const span = document.getElementById('ret-monto-' + id);
            if (span) span.textContent = chk.checked ? 'Bs. ' + monto.toFixed(2) : 'Bs. 0.00';
        });

        // Total visible
        const retTotalEl = document.getElementById('ret-total');
        if (retTotalEl && retTotalEl.style.display !== 'none') {
            retTotalEl.textContent = 'Bs. ' + totalRetenido.toFixed(2);
            retTotalEl.setAttribute('data-valor', totalRetenido.toFixed(2));
        }

        // Badge en header
        const badge = document.getElementById('ret-count-badge');
        if (badge) {
            if (seleccionadas > 0) {
                badge.style.display = 'inline-block';
                badge.innerHTML = '<span class="badge badge-warn">' + seleccionadas + ' aplicada' + (seleccionadas > 1 ? 's' : '') + '</span>';
            } else {
                badge.style.display = 'none';
            }
        }

        // Actualizar campo monto_retencion si existe en el formulario padre
        const campoRetencion = document.querySelector('[name="monto_retencion"]');
        if (campoRetencion) campoRetencion.value = totalRetenido.toFixed(2);
    };

    // Escuchar cambios en el campo de monto bruto del padre
    document.addEventListener('DOMContentLoaded', function() {
        const montoInput = document.querySelector('[name="{{ $inputMonto }}"]');
        if (montoInput) {
            montoInput.addEventListener('input',  calcularRetenciones);
            montoInput.addEventListener('change', calcularRetenciones);
        }
    });
})();
</script>
