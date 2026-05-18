@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <span class="current">Dashboard</span>
@endsection

@section('content')
{{-- 
  VISTA PRINCIPAL (DASHBOARD)
  Esta vista es el punto de entrada al sistema. 
  Consume las variables enviadas desde el DashboardController (__invoke).
  Muestra un resumen en tiempo real del estado financiero y operativo de la Contraloría.
--}}

<div class="page-header fade-up">
    <h1 class="page-title">Panel de Control</h1>
    <p class="page-subtitle">Bienvenido, {{ auth()->user()->name }} · {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</p>
</div>

{{-- ── Fila 1: Alertas operativas ─────────────────────────────────── --}}
{{-- 
  Estas alertas solo se muestran si hay tareas pendientes (Causaciones, Órdenes o Nóminas en borrador).
  Actúan como notificaciones push dentro de la interfaz para llamar la atención del administrador.
--}}
@if($causacionesPendientes > 0 || $ordenesPendientes > 0 || $nominasPendientes > 0)
<div class="fade-up" style="margin-bottom:20px;display:flex;gap:12px;flex-wrap:wrap;">
    @if($causacionesPendientes > 0)
    <a href="{{ route('presupuesto.causaciones.index') }}" style="text-decoration:none;flex:1;min-width:200px;">
        <div class="alert alert-warning" style="margin:0;display:flex;align-items:center;gap:10px;cursor:pointer;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span><strong>{{ $causacionesPendientes }}</strong> causación{{ $causacionesPendientes > 1 ? 'es' : '' }} en borrador pendiente{{ $causacionesPendientes > 1 ? 's' : '' }} de aprobar</span>
            <i class="fa-solid fa-chevron-right" style="margin-left:auto;opacity:.5;font-size:11px;"></i>
        </div>
    </a>
    @endif
    @if($nominasPendientes > 0)
    <a href="{{ route('nomina.nominas.index') }}" style="text-decoration:none;flex:1;min-width:200px;">
        <div class="alert alert-info" style="margin:0;display:flex;align-items:center;gap:10px;cursor:pointer;">
            <i class="fa-solid fa-money-check-dollar"></i>
            <span><strong>{{ $nominasPendientes }}</strong> nómina{{ $nominasPendientes > 1 ? 's' : '' }} pendiente{{ $nominasPendientes > 1 ? 's' : '' }} de aprobación</span>
            <i class="fa-solid fa-chevron-right" style="margin-left:auto;opacity:.5;font-size:11px;"></i>
        </div>
    </a>
    @endif
</div>
@endif

{{-- ── Fila 2: KPI Cards ─────────────────────────────────────────── --}}
{{-- Tarjetas de Indicadores Clave. Muestran los contadores principales del sistema. --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;margin-bottom:24px;">

    {{-- Ejercicio Activo --}}
    <div class="card fade-up" style="padding:20px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(34,211,166,0.15);color:var(--accent-3);display:grid;place-items:center;font-size:17px;">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <span class="badge {{ $ejercicioActivo ? 'badge-active' : 'badge-warn' }}">
                {{ $ejercicioActivo ? $ejercicioActivo->anio : 'Sin ejercicio' }}
            </span>
        </div>
        <div style="font-size:26px;font-weight:800;letter-spacing:-1px;" id="kpi-ejercicio">{{ $totalEjercicios }}</div>
        <div style="font-size:12px;color:var(--text-secondary);margin-top:3px;">Ejercicios Fiscales</div>
    </div>

    {{-- Causaciones pendientes --}}
    <a href="{{ route('presupuesto.causaciones.index') }}" style="text-decoration:none;">
    <div class="card fade-up" style="padding:20px;cursor:pointer;transition:border-color .25s;" onmouseover="this.style.borderColor='rgba(99,179,237,0.35)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.07)'">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(247,185,79,0.15);color:var(--accent-warn);display:grid;place-items:center;font-size:17px;">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            @if($causacionesPendientes > 0)
            <span class="badge badge-warn">{{ $causacionesPendientes }} pend.</span>
            @else
            <span class="badge badge-active">Al día</span>
            @endif
        </div>
        <div style="font-size:26px;font-weight:800;letter-spacing:-1px;" id="kpi-causaciones">{{ $causacionesAprobadas }}</div>
        <div style="font-size:12px;color:var(--text-secondary);margin-top:3px;">Causaciones Aprobadas</div>
    </div>
    </a>

    {{-- Pagos Pendientes --}}
    <a href="{{ route('presupuesto.pagos.index', ['estado' => 'pendiente']) }}" style="text-decoration:none;">
    <div class="card fade-up" style="padding:20px;cursor:pointer;transition:border-color .25s;" onmouseover="this.style.borderColor='rgba(99,179,237,0.35)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.07)'">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(34,211,166,0.15);color:var(--accent-3);display:grid;place-items:center;font-size:17px;">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            @if(isset($pagosPendientes) && $pagosPendientes > 0)
            <span class="badge badge-warn">{{ $pagosPendientes }} pend.</span>
            @else
            <span class="badge badge-active">Al día</span>
            @endif
        </div>
        <div style="font-size:26px;font-weight:800;letter-spacing:-1px;" id="kpi-pagos">{{ isset($pagosPendientes) ? $pagosPendientes : 0 }}</div>
        <div style="font-size:12px;color:var(--text-secondary);margin-top:3px;">Pagos Pendientes</div>
    </div>
    </a>

    {{-- Saldo bancario --}}
    <div class="card fade-up" style="padding:20px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(79,142,247,0.15);color:var(--accent);display:grid;place-items:center;font-size:17px;">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <span class="badge badge-blue">Bs.</span>
        </div>
        <div style="font-size:22px;font-weight:800;letter-spacing:-1px;" id="kpi-saldo">{{ number_format($saldoBancario, 2) }}</div>
        <div style="font-size:12px;color:var(--text-secondary);margin-top:3px;">Saldo Bancario Total</div>
    </div>

    {{-- Empleados activos --}}
    <a href="{{ route('nomina.empleados.index') }}" style="text-decoration:none;">
    <div class="card fade-up" style="padding:20px;cursor:pointer;transition:border-color .25s;" onmouseover="this.style.borderColor='rgba(99,179,237,0.35)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.07)'">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(79,142,247,0.12);color:var(--accent);display:grid;place-items:center;font-size:17px;">
                <i class="fa-solid fa-id-card"></i>
            </div>
            <span class="badge badge-active">Activos</span>
        </div>
        <div style="font-size:26px;font-weight:800;letter-spacing:-1px;" id="kpi-empleados">{{ $empleadosActivos }}</div>
        <div style="font-size:12px;color:var(--text-secondary);margin-top:3px;">Empleados Activos</div>
    </div>
    </a>

    {{-- Partidas presupuestarias --}}
    <div class="card fade-up" style="padding:20px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(124,92,252,0.12);color:var(--accent-2);display:grid;place-items:center;font-size:17px;">
                <i class="fa-solid fa-list-ol"></i>
            </div>
        </div>
        <div style="font-size:26px;font-weight:800;letter-spacing:-1px;">{{ $totalPartidas }}</div>
        <div style="font-size:12px;color:var(--text-secondary);margin-top:3px;">Partidas Presupuestarias</div>
    </div>

    {{-- Usuarios --}}
    <div class="card fade-up" style="padding:20px;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(247,185,79,0.12);color:var(--accent-warn);display:grid;place-items:center;font-size:17px;">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
        <div style="font-size:26px;font-weight:800;letter-spacing:-1px;" id="kpi-usuarios">{{ $totalUsuarios }}</div>
        <div style="font-size:12px;color:var(--text-secondary);margin-top:3px;">Usuarios Activos</div>
    </div>

</div>

{{-- ── Fila 4: Monitor del Servidor (solo admins) ─────────────────── --}}
{{-- 
  Esta sección realiza consultas asíncronas (AJAX) mediante JS a la ruta server.stats.
  Solo los usuarios con permisos de gestión de roles (Administradores) pueden verla.
--}}
@canany(['roles.gestionar','roles.ver'])
<div style="margin-top:20px" class="fade-up" id="server-monitor-section">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-server" style="color:#22d3a6;margin-right:8px"></i>
                Estado del Servidor
                <span id="srv-ts" style="font-size:.72rem;color:var(--text-secondary);font-weight:400;margin-left:10px"></span>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
                <span id="srv-status" class="badge badge-active" style="font-size:.72rem"><i class="fa-solid fa-circle" style="font-size:6px;animation:pulse-dot 2s infinite"></i> En vivo</span>
                <button class="btn btn-sm btn-outline" onclick="fetchStats()" title="Actualizar ahora">
                    <i class="fa-solid fa-rotate-right" id="srv-refresh-icon"></i>
                </button>
            </div>
        </div>

        <div style="padding:16px 20px">
            {{-- Grid de métricas --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-bottom:20px">

                {{-- CPU --}}
                <div class="srv-card" id="srv-cpu-card">
                    <div class="srv-gauge-wrap">
                        <svg class="srv-gauge" viewBox="0 0 80 80">
                            <circle cx="40" cy="40" r="32" fill="none" stroke="rgba(255,255,255,.06)" stroke-width="7"/>
                            <circle cx="40" cy="40" r="32" fill="none" stroke="#4f8ef7" stroke-width="7"
                                stroke-dasharray="201 201" stroke-dashoffset="201"
                                stroke-linecap="round" transform="rotate(-90 40 40)"
                                id="cpu-arc" style="transition:stroke-dashoffset 1s ease"/>
                        </svg>
                        <div class="srv-gauge-label">
                            <span class="srv-gauge-pct" id="cpu-pct">–</span>
                            <span class="srv-gauge-unit">%</span>
                        </div>
                    </div>
                    <div class="srv-metric-info">
                        <div class="srv-metric-title"><i class="fa-solid fa-microchip" style="color:#4f8ef7"></i> CPU</div>
                        <div class="srv-metric-sub" id="cpu-cores">cargando…</div>
                        <div class="srv-bar-wrap">
                            <div class="srv-bar" id="cpu-bar" style="--bar-color:#4f8ef7;width:0%"></div>
                        </div>
                    </div>
                </div>

                {{-- RAM --}}
                <div class="srv-card" id="srv-ram-card">
                    <div class="srv-gauge-wrap">
                        <svg class="srv-gauge" viewBox="0 0 80 80">
                            <circle cx="40" cy="40" r="32" fill="none" stroke="rgba(255,255,255,.06)" stroke-width="7"/>
                            <circle cx="40" cy="40" r="32" fill="none" stroke="#22d3a6" stroke-width="7"
                                stroke-dasharray="201 201" stroke-dashoffset="201"
                                stroke-linecap="round" transform="rotate(-90 40 40)"
                                id="ram-arc" style="transition:stroke-dashoffset 1s ease"/>
                        </svg>
                        <div class="srv-gauge-label">
                            <span class="srv-gauge-pct" id="ram-pct">–</span>
                            <span class="srv-gauge-unit">%</span>
                        </div>
                    </div>
                    <div class="srv-metric-info">
                        <div class="srv-metric-title"><i class="fa-solid fa-memory" style="color:#22d3a6"></i> RAM</div>
                        <div class="srv-metric-sub" id="ram-detail">cargando…</div>
                        <div class="srv-bar-wrap">
                            <div class="srv-bar" id="ram-bar" style="--bar-color:#22d3a6;width:0%"></div>
                        </div>
                    </div>
                </div>

                {{-- Disco --}}
                <div class="srv-card" id="srv-disk-card">
                    <div class="srv-gauge-wrap">
                        <svg class="srv-gauge" viewBox="0 0 80 80">
                            <circle cx="40" cy="40" r="32" fill="none" stroke="rgba(255,255,255,.06)" stroke-width="7"/>
                            <circle cx="40" cy="40" r="32" fill="none" stroke="#f7b94f" stroke-width="7"
                                stroke-dasharray="201 201" stroke-dashoffset="201"
                                stroke-linecap="round" transform="rotate(-90 40 40)"
                                id="disk-arc" style="transition:stroke-dashoffset 1s ease"/>
                        </svg>
                        <div class="srv-gauge-label">
                            <span class="srv-gauge-pct" id="disk-pct">–</span>
                            <span class="srv-gauge-unit">%</span>
                        </div>
                    </div>
                    <div class="srv-metric-info">
                        <div class="srv-metric-title"><i class="fa-solid fa-hard-drive" style="color:#f7b94f"></i> Disco</div>
                        <div class="srv-metric-sub" id="disk-detail">cargando…</div>
                        <div class="srv-bar-wrap">
                            <div class="srv-bar" id="disk-bar" style="--bar-color:#f7b94f;width:0%"></div>
                        </div>
                    </div>
                </div>

                {{-- PHP / Proceso --}}
                <div class="srv-card">
                    <div style="display:flex;align-items:center;justify-content:center;height:80px">
                        <div style="text-align:center">
                            <i class="fa-brands fa-php" style="font-size:2.5rem;color:#7c5cfc;opacity:.85"></i>
                        </div>
                    </div>
                    <div class="srv-metric-info">
                        <div class="srv-metric-title"><i class="fa-solid fa-code" style="color:#7c5cfc"></i> PHP</div>
                        <div class="srv-metric-sub" id="php-version">cargando…</div>
                        <div class="srv-metric-sub" id="php-mem" style="margin-top:4px">–</div>
                    </div>
                </div>

            </div>

            {{-- Info del sistema --}}
            <div id="srv-sysinfo" style="display:flex;flex-wrap:wrap;gap:8px">
                <div class="srv-pill" id="pill-os"><i class="fa-solid fa-display"></i> <span>–</span></div>
                <div class="srv-pill" id="pill-uptime"><i class="fa-solid fa-clock"></i> <span>–</span></div>
                <div class="srv-pill" id="pill-php-limit"><i class="fa-solid fa-gauge-simple"></i> <span>–</span></div>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Server Monitor ───────────────────────────────────── */
.srv-card { background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:12px; padding:16px; display:flex; flex-direction:column; gap:12px; transition:border-color .2s; }
.srv-card:hover { border-color:rgba(255,255,255,.12); }

.srv-gauge-wrap { position:relative; width:80px; height:80px; margin:0 auto; }
.srv-gauge { width:80px; height:80px; }
.srv-gauge-label { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.srv-gauge-pct { font-size:1.15rem; font-weight:800; color:var(--text-primary); line-height:1; }
.srv-gauge-unit { font-size:.6rem; color:var(--text-secondary); }

.srv-metric-info { display:flex; flex-direction:column; gap:4px; }
.srv-metric-title { font-size:.82rem; font-weight:600; color:var(--text-primary); display:flex; align-items:center; gap:5px; }
.srv-metric-sub { font-size:.75rem; color:var(--text-secondary); }
.srv-bar-wrap { height:4px; background:rgba(255,255,255,.06); border-radius:2px; overflow:hidden; margin-top:4px; }
.srv-bar { height:100%; border-radius:2px; background:var(--bar-color,var(--accent)); transition:width 1s ease; }

.srv-pill { display:flex; align-items:center; gap:6px; background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:20px; padding:.3rem .85rem; font-size:.75rem; color:var(--text-secondary); }
.srv-pill i { font-size:.7rem; opacity:.7; }

@keyframes pulse-dot { 0%,100%{opacity:1} 50%{opacity:.3} }

/* Colores de alerta según % */
.pct-warn circle[id$="-arc"] { stroke:#f7b94f !important; }
.pct-danger circle[id$="-arc"] { stroke:#f75f5f !important; }
</style>

<script>
const CIRCUM = 2 * Math.PI * 32; // 201

function setArc(id, pct) {
    const el = document.getElementById(id);
    if (!el) return;
    const offset = CIRCUM - (pct / 100) * CIRCUM;
    el.style.strokeDashoffset = offset;
    // Cambiar color según nivel
    const card = el.closest('.srv-card');
    if (card) {
        card.classList.remove('pct-warn','pct-danger');
        if (pct >= 90) card.classList.add('pct-danger');
        else if (pct >= 70) card.classList.add('pct-warn');
    }
}
function txt(id, val) { const el = document.getElementById(id); if (el) el.textContent = val; }
function bar(id, pct) { const el = document.getElementById(id); if (el) el.style.width = Math.max(0, Math.min(100, pct)) + '%'; }

let refreshing = false;
async function fetchStats() {
    if (refreshing) return;
    refreshing = true;
    const icon = document.getElementById('srv-refresh-icon');
    if (icon) icon.style.animation = 'spin .8s linear infinite';

    try {
        const r = await fetch('{{ route("server.stats") }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!r.ok) throw new Error('HTTP ' + r.status);
        const d = await r.json();

        // ── CPU
        if (d.cpu.avail) {
            const p = d.cpu.pct;
            txt('cpu-pct', p);
            setArc('cpu-arc', p);
            bar('cpu-bar', p);
            txt('cpu-cores', d.cpu.cores + (d.cpu.cores === 1 ? ' núcleo' : ' núcleos'));
        } else {
            txt('cpu-pct', 'N/D');
            txt('cpu-cores', 'No disponible');
        }

        // ── RAM
        if (d.ram.avail) {
            const p = d.ram.pct;
            txt('ram-pct', p);
            setArc('ram-arc', p);
            bar('ram-bar', p);
            txt('ram-detail', `${d.ram.used_gb} GB / ${d.ram.total_gb} GB`);
        } else {
            txt('ram-pct', 'N/D');
            txt('ram-detail', 'No disponible');
        }

        // ── Disco
        if (d.disk.avail) {
            const p = d.disk.pct;
            txt('disk-pct', p);
            setArc('disk-arc', p);
            bar('disk-bar', p);
            txt('disk-detail', `${d.disk.used_gb} GB / ${d.disk.total_gb} GB`);
        } else {
            txt('disk-pct', 'N/D');
            txt('disk-detail', 'No disponible');
        }

        // ── PHP
        txt('php-version', 'PHP ' + d.php.version);
        txt('php-mem', `Proceso: ${d.php.memory_used} MB / pico ${d.php.memory_peak} MB`);

        // ── Pills info
        document.querySelector('#pill-os span').textContent = d.php.os;
        document.querySelector('#pill-uptime span').textContent = d.uptime ? 'Activo: ' + d.uptime : 'Uptime N/D';
        document.querySelector('#pill-php-limit span').textContent = 'Límite PHP: ' + d.php.memory_limit;

        txt('srv-ts', 'Actualizado: ' + d.ts);

    } catch (e) {
        txt('srv-ts', 'Error al obtener datos');
        const status = document.getElementById('srv-status');
        if (status) { status.textContent = 'Sin conexión'; status.className = 'badge badge-danger'; }
    } finally {
        refreshing = false;
        if (icon) icon.style.animation = '';
    }
}

// Iniciar y programar refresco cada 8 segundos
fetchStats();
setInterval(fetchStats, 8000);
</script>
<style>
@keyframes spin { to { transform:rotate(360deg); } }
</style>
@endcanany

{{-- ── Fila 3: Actividad Reciente + Accesos rápidos ─────────────── --}}
<div style="display:grid;grid-template-columns:1fr 320px;gap:16px;" class="fade-up">

    {{-- Últimas causaciones pendientes --}}
    {{-- Muestra un listado rápido de las últimas 5 causaciones generadas para revisión --}}
    <div class="card" style="animation-delay:.15s">
        <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-clock-rotate-left" style="color:var(--accent);margin-right:8px;"></i>Causaciones Recientes</div>
            <a href="{{ route('presupuesto.causaciones.index') }}" class="btn btn-sm btn-outline">Ver todas</a>
        </div>
        @if($ultimasCausaciones->isEmpty())
        <div class="empty-state" style="padding:30px;">
            <div class="empty-icon">📄</div>
            <div class="empty-title">Sin causaciones recientes</div>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>N°</th>
                    <th>Beneficiario</th>
                    <th>Unidad</th>
                    <th>Monto</th>
                    <th>Estado</th>
                </tr></thead>
                <tbody>
                @foreach($ultimasCausaciones as $c)
                <tr>
                    <td style="font-size:12px;font-family:monospace;">{{ $c->numero }}</td>
                    <td style="font-size:12px;">{{ Str::limit($c->beneficiario, 25) }}</td>
                    <td style="font-size:11px;color:var(--text-secondary);">{{ Str::limit($c->unidadEjecutora?->siglas ?? $c->unidadEjecutora?->nombre, 12) }}</td>
                    <td style="font-size:12px;font-weight:600;">Bs. {{ number_format($c->monto_causado, 2) }}</td>
                    <td><span class="badge {{ $c->getEstadoBadgeClass() }}">{{ $c->getEstadoLabel() }}</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Accesos rápidos --}}
    {{-- Botones de acción directa basados en los permisos del usuario activo --}}
    <div class="card" style="animation-delay:.2s">
        <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-bolt" style="color:var(--accent-warn);margin-right:8px;"></i>Accesos Rápidos</div>
        </div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:8px;padding:16px;">

            @can('causaciones.crear')
            <a href="{{ route('presupuesto.causaciones.create') }}" style="text-decoration:none;">
                <div style="background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:9px;padding:12px 16px;display:flex;align-items:center;gap:12px;cursor:pointer;transition:all .25s;" onmouseover="this.style.borderColor='rgba(99,179,237,0.35)';this.style.background='rgba(79,142,247,0.07)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.07)';this.style.background='rgba(255,255,255,0.03)'">
                    <i class="fa-solid fa-file-invoice-dollar" style="color:var(--accent-warn);width:16px;"></i>
                    <span style="font-size:13px;font-weight:500;">Nueva Causación</span>
                </div>
            </a>
            @endcan

            @can('pagos.crear')
            <a href="{{ route('presupuesto.pagos.create') }}" style="text-decoration:none;">
                <div style="background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:9px;padding:12px 16px;display:flex;align-items:center;gap:12px;cursor:pointer;transition:all .25s;" onmouseover="this.style.borderColor='rgba(99,179,237,0.35)';this.style.background='rgba(79,142,247,0.07)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.07)';this.style.background='rgba(255,255,255,0.03)'">
                    <i class="fa-solid fa-money-bill-wave" style="color:var(--accent-3);width:16px;"></i>
                    <span style="font-size:13px;font-weight:500;">Nuevo Pago</span>
                </div>
            </a>
            @endcan

            @can('nomina.nominas.crear')
            <a href="{{ route('nomina.nominas.create') }}" style="text-decoration:none;">
                <div style="background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:9px;padding:12px 16px;display:flex;align-items:center;gap:12px;cursor:pointer;transition:all .25s;" onmouseover="this.style.borderColor='rgba(99,179,237,0.35)';this.style.background='rgba(79,142,247,0.07)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.07)';this.style.background='rgba(255,255,255,0.03)'">
                    <i class="fa-solid fa-money-check-dollar" style="color:var(--accent);width:16px;"></i>
                    <span style="font-size:13px;font-weight:500;">Nueva Nómina</span>
                </div>
            </a>
            @endcan

            @can('compras.solicitudes.crear')
            <a href="{{ route('compras.solicitudes.create') }}" style="text-decoration:none;">
                <div style="background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:9px;padding:12px 16px;display:flex;align-items:center;gap:12px;cursor:pointer;transition:all .25s;" onmouseover="this.style.borderColor='rgba(99,179,237,0.35)';this.style.background='rgba(79,142,247,0.07)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.07)';this.style.background='rgba(255,255,255,0.03)'">
                    <i class="fa-solid fa-clipboard-list" style="color:var(--accent-warn);width:16px;"></i>
                    <span style="font-size:13px;font-weight:500;">Solicitud de Compra</span>
                </div>
            </a>
            @endcan

            @can('usuarios.crear')
            <a href="{{ route('admin.usuarios.create') }}" style="text-decoration:none;">
                <div style="background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:9px;padding:12px 16px;display:flex;align-items:center;gap:12px;cursor:pointer;transition:all .25s;" onmouseover="this.style.borderColor='rgba(99,179,237,0.35)';this.style.background='rgba(79,142,247,0.07)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.07)';this.style.background='rgba(255,255,255,0.03)'">
                    <i class="fa-solid fa-user-plus" style="color:var(--accent-2);width:16px;"></i>
                    <span style="font-size:13px;font-weight:500;">Nuevo Usuario</span>
                </div>
            </a>
            @endcan

        </div>
    </div>

</div>
@endsection
