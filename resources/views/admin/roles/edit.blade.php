@extends('layouts.app')
@section('title', 'Configurar permisos: ' . $rol->name)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-sliders"></i> Configurar: {{ ucwords(str_replace('-', ' ', $rol->name)) }}</h1>
        <p class="page-subtitle">Ajusta apariencia y permisos de este rol</p>
    </div>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
</div>

@if($rol->name === 'super-admin')
<div class="alert" style="background:rgba(247,185,79,.08);border:1px solid rgba(247,185,79,.25);border-radius:12px;padding:1.25rem 1.5rem;color:#f7b94f;margin-bottom:1.5rem">
    <i class="fa-solid fa-crown" style="font-size:1.1rem"></i>
    <strong style="margin-left:.5rem">Super Administrador</strong> — Este rol tiene acceso total al sistema sin importar los permisos marcados.
    No es necesario configurar permisos aquí.
</div>
@endif

<form method="POST" action="{{ route('admin.roles.update', $rol) }}">
@csrf @method('PUT')


<div class="edit-layout">
    {{-- Panel izquierdo: info del rol --}}
    <div class="edit-sidebar">
        <div class="card" style="padding:1.25rem;position:sticky;top:80px">
            <h3 style="font-size:.85rem;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:1rem">Información del Rol</h3>

            <div class="form-group" style="margin-bottom:1rem">
                <label class="form-label" style="font-size:.82rem">Nombre del rol</label>
                @if($rol->name === 'super-admin')
                <input type="hidden" name="name" value="{{ $rol->name }}">
                <input type="text" class="form-control" value="{{ $rol->name }}" disabled style="opacity:.5;font-family:monospace">
                @else
                <input type="text" name="name" class="form-control"
                       value="{{ old('name', $rol->name) }}" placeholder="ej: jefe-almacen"
                       style="font-family:monospace;font-size:.9rem">
                <small class="form-text" style="font-size:.75rem">Solo minúsculas y guiones.</small>
                @endif
            </div>

            @include('admin.roles._icono_picker', [
                'iconoActual' => old('icono', $rol->icono ?: 'fa-user-shield'),
                'colorActual' => old('color', $rol->color ?: '#4f8ef7'),
                'descActual'  => old('descripcion', $rol->descripcion ?? ''),
            ])

            <div class="summary-box">
                <div class="summary-item" id="cnt-marcados">
                    <span class="summary-num" id="n-marcados">{{ count($permisosAsignados) }}</span>
                    <span class="summary-label">Permisos activos</span>
                </div>
                <div class="summary-item">
                    <span class="summary-num">{{ collect($grupos)->flatten(1)->count() }}</span>
                    <span class="summary-label">Total disponibles</span>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:.5rem;margin-top:1rem">
                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleTodo(true)">
                    <i class="fa-solid fa-check-double"></i> Activar todo
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleTodo(false)">
                    <i class="fa-solid fa-xmark"></i> Quitar todo
                </button>
            </div>

            <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border)">
                <button type="submit" class="btn btn-primary" style="width:100%">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                </button>
            </div>
        </div>
    </div>

    {{-- Panel derecho: permisos por módulo --}}
    <div class="edit-main">
        @foreach($modulos as $key => $mod)
        @if(isset($grupos[$key]) && count($grupos[$key]))
        @php
            $permsModulo = $grupos[$key];
            $activos = collect($permsModulo)->filter(fn($p) => in_array($p['id'], $permisosAsignados))->count();
            $total   = count($permsModulo);
        @endphp
        <div class="modulo-card" id="mod-{{ $key }}">
            <div class="modulo-card-header" onclick="toggleModulo('{{ $key }}')">
                <div class="modulo-card-title">
                    <span class="modulo-icon-badge" style="background:{{ $mod['color'] }}1a;color:{{ $mod['color'] }}">
                        <i class="fa-solid {{ $mod['icono'] }}"></i>
                    </span>
                    <div>
                        <strong>{{ $mod['nombre'] }}</strong>
                        <span class="modulo-progress-text" id="prog-{{ $key }}">{{ $activos }}/{{ $total }} activos</span>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:.75rem">
                    <div class="mini-progress" style="--pct:{{ $total > 0 ? round($activos/$total*100) : 0 }}%">
                        <div class="mini-progress-bar" style="background:{{ $mod['color'] }}"></div>
                    </div>
                    <label class="mod-toggle" onclick="event.stopPropagation()" title="Activar/desactivar módulo completo">
                        <input type="checkbox" class="mod-all-cb" data-mod="{{ $key }}"
                               {{ $activos === $total && $total > 0 ? 'checked' : '' }} onchange="toggleModulo_cb(this)">
                        <span class="mod-toggle-slider" style="--tc:{{ $mod['color'] }}"></span>
                    </label>
                    <i class="fa-solid fa-chevron-down modulo-caret {{ $activos > 0 ? '' : 'caret-up' }}" id="caret-{{ $key }}"></i>
                </div>
            </div>

            <div class="modulo-perms-grid {{ $activos > 0 ? 'show' : '' }}" id="perms-{{ $key }}">
                @foreach(collect($permsModulo)->groupBy('categoria') as $cat => $catPerms)
                <div class="perm-category">
                    <div class="perm-cat-label">{{ $cat }}</div>
                    <div class="perm-toggles">
                        @foreach($catPerms as $perm)
                        <label class="perm-toggle-row {{ in_array($perm['id'], $permisosAsignados) ? 'active' : '' }}" id="row-{{ $perm['id'] }}">
                            <div class="perm-row-info">
                                <span class="perm-row-desc">{{ $perm['descripcion'] }}</span>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" name="permisos[]" value="{{ $perm['id'] }}"
                                       class="perm-cb mod-perm-{{ $key }}"
                                       {{ in_array($perm['id'], $permisosAsignados) ? 'checked' : '' }}
                                       onchange="onPermChange(this, '{{ $key }}')">
                                <span class="toggle-knob" style="--tc:{{ $mod['color'] }}"></span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>
</form>

<style>
.edit-layout { display:grid; grid-template-columns:260px 1fr; gap:1.5rem; align-items:start; }
@media(max-width:768px) { .edit-layout { grid-template-columns:1fr; } }

.summary-box { display:grid; grid-template-columns:1fr 1fr; gap:.5rem; }
.summary-item { background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:10px; padding:.75rem; text-align:center; }
.summary-num { display:block; font-size:1.5rem; font-weight:800; color:var(--text-primary); }
.summary-label { font-size:.72rem; color:var(--text-secondary); }

.modulo-card { background:var(--bg-card); border:1px solid var(--border); border-radius:14px; margin-bottom:1rem; overflow:hidden; transition:border-color .2s; }
.modulo-card:hover { border-color:rgba(255,255,255,.12); }
.modulo-card-header { display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; cursor:pointer; }
.modulo-card-title { display:flex; align-items:center; gap:.85rem; }
.modulo-icon-badge { width:36px; height:36px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:.85rem; flex-shrink:0; }
.modulo-card-title strong { font-size:.9rem; color:var(--text-primary); display:block; }
.modulo-progress-text { font-size:.75rem; color:var(--text-secondary); }
.mini-progress { width:60px; height:5px; background:rgba(255,255,255,.08); border-radius:3px; overflow:hidden; }
.mini-progress-bar { height:100%; width:var(--pct); border-radius:3px; transition:width .3s; }
.modulo-caret { font-size:.7rem; color:var(--text-secondary); transition:transform .2s; }
.modulo-caret.caret-up { transform:rotate(-90deg); }

/* Toggle global del módulo */
.mod-toggle { position:relative; display:inline-flex; cursor:pointer; }
.mod-toggle input { opacity:0; width:0; height:0; position:absolute; }
.mod-toggle-slider { width:34px; height:19px; background:rgba(255,255,255,.08); border-radius:10px; position:relative; transition:background .2s; }
.mod-toggle-slider::after { content:''; position:absolute; left:3px; top:3px; width:13px; height:13px; background:#fff; border-radius:50%; transition:transform .2s; }
.mod-toggle input:checked ~ .mod-toggle-slider { background:var(--tc, var(--accent)); }
.mod-toggle input:checked ~ .mod-toggle-slider::after { transform:translateX(15px); }

.modulo-perms-grid { display:none; padding:1rem 1.25rem; border-top:1px solid var(--border); background:rgba(0,0,0,.15); }
.modulo-perms-grid.show { display:block; }

.perm-category { margin-bottom:1rem; }
.perm-cat-label { font-size:.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--text-secondary); margin-bottom:.5rem; padding-bottom:.35rem; border-bottom:1px solid var(--border); }
.perm-toggles { display:flex; flex-direction:column; gap:.25rem; }

.perm-toggle-row { display:flex; align-items:center; justify-content:space-between; padding:.6rem .85rem; border-radius:9px; cursor:pointer; transition:background .15s; }
.perm-toggle-row:hover { background:rgba(255,255,255,.04); }
.perm-toggle-row.active { background:rgba(79,142,247,.05); }
.perm-row-info { flex:1; min-width:0; padding-right:1rem; }
.perm-row-desc { font-size:.83rem; color:var(--text-secondary); display:block; }
.perm-toggle-row.active .perm-row-desc { color:var(--text-primary); }

/* Toggle individual */
.toggle-switch { position:relative; flex-shrink:0; }
.toggle-switch input { opacity:0; width:0; height:0; position:absolute; }
.toggle-knob { display:block; width:40px; height:22px; background:rgba(255,255,255,.08); border-radius:11px; position:relative; transition:background .2s; cursor:pointer; }
.toggle-knob::after { content:''; position:absolute; left:3px; top:3px; width:16px; height:16px; background:#fff; border-radius:50%; transition:transform .2s; }
.toggle-switch input:checked ~ .toggle-knob { background:var(--tc, var(--accent)); }
.toggle-switch input:checked ~ .toggle-knob::after { transform:translateX(18px); }
</style>

<script>
// Abrir/cerrar módulo
function toggleModulo(key) {
    const grid  = document.getElementById('perms-' + key);
    const caret = document.getElementById('caret-' + key);
    grid.classList.toggle('show');
    caret.classList.toggle('caret-up');
}

// Toggle switch del módulo completo
function toggleModulo_cb(cb) {
    const key    = cb.dataset.mod;
    const checks = document.querySelectorAll('.mod-perm-' + key);
    const grid   = document.getElementById('perms-' + key);
    const caret  = document.getElementById('caret-' + key);
    checks.forEach(c => {
        c.checked = cb.checked;
        c.closest('.perm-toggle-row').classList.toggle('active', cb.checked);
    });
    if (cb.checked && !grid.classList.contains('show')) {
        grid.classList.add('show');
        caret.classList.remove('caret-up');
    }
    updateProgreso(key);
    updateConteoGlobal();
}

// Cambio individual
function onPermChange(cb, key) {
    const row = cb.closest('.perm-toggle-row');
    row.classList.toggle('active', cb.checked);
    updateProgreso(key);
    updateConteoGlobal();

    // Sincroniza el checkbox "todo el módulo"
    const checks = document.querySelectorAll('.mod-perm-' + key);
    const allOn  = [...checks].every(c => c.checked);
    document.querySelector('.mod-all-cb[data-mod="' + key + '"]').checked = allOn;
}

function updateProgreso(key) {
    const checks  = document.querySelectorAll('.mod-perm-' + key);
    const activos = [...checks].filter(c => c.checked).length;
    const total   = checks.length;
    document.getElementById('prog-' + key).textContent = activos + '/' + total + ' activos';
    const bar = document.querySelector('#mod-' + key + ' .mini-progress-bar');
    if (bar) bar.style.width = (total > 0 ? Math.round(activos/total*100) : 0) + '%';
}

function updateConteoGlobal() {
    const total = document.querySelectorAll('input[name="permisos[]"]:checked').length;
    document.getElementById('n-marcados').textContent = total;
}

function toggleTodo(estado) {
    document.querySelectorAll('input[name="permisos[]"]').forEach(c => {
        c.checked = estado;
        c.closest('.perm-toggle-row').classList.toggle('active', estado);
    });
    document.querySelectorAll('.mod-all-cb').forEach(c => c.checked = estado);
    @foreach(array_keys($modulos) as $key)
    updateProgreso('{{ $key }}');
    @endforeach
    updateConteoGlobal();
}
</script>
@endsection
