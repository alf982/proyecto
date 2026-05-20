@extends('layouts.app')
@section('title', 'Crear Nuevo Rol')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-user-shield"></i> Crear Nuevo Rol</h1>
        <p class="page-subtitle">Define el nombre del rol y sus permisos de acceso</p>
    </div>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
</div>

<form method="POST" action="{{ route('admin.roles.store') }}" id="form-rol">
@csrf
<input type="hidden" name="permisos_json" id="permisos_json">
<div class="edit-layout">
    {{-- Panel izquierdo --}}
    <div class="edit-sidebar">
        <div class="card" style="padding:1.25rem;position:sticky;top:80px">
            <h3 style="font-size:.85rem;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:1rem">Datos del Rol</h3>

            <div class="form-group" style="margin-bottom:1rem">
                <label class="form-label">Nombre del rol <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" placeholder="ej: jefe-almacen"
                       style="font-family:monospace;font-size:.9rem">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="form-text">Solo minúsculas y guiones. Ej: <code>analista-rrhh</code></small>
            </div>

            @include('admin.roles._icono_picker', [
                'iconoActual' => old('icono', 'fa-user-shield'),
                'colorActual' => old('color', '#4f8ef7'),
                'descActual'  => old('descripcion', ''),
            ])

            <div class="summary-box" style="margin-bottom:1rem">
                <div class="summary-item">
                    <span class="summary-num" id="n-marcados">0</span>
                    <span class="summary-label">Permisos seleccionados</span>
                </div>
                <div class="summary-item">
                    <span class="summary-num">{{ collect($grupos)->flatten(1)->count() }}</span>
                    <span class="summary-label">Total disponibles</span>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:1.25rem">
                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleTodo(true)">
                    <i class="fa-solid fa-check-double"></i> Activar todo
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleTodo(false)">
                    <i class="fa-solid fa-xmark"></i> Quitar todo
                </button>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%">
                <i class="fa-solid fa-floppy-disk"></i> Crear Rol
            </button>
        </div>
    </div>

    {{-- Panel derecho: permisos --}}
    <div class="edit-main">
        @foreach($modulos as $key => $mod)
        @if(isset($grupos[$key]) && count($grupos[$key]))
        @php $permsModulo = $grupos[$key]; $total = count($permsModulo); @endphp
        <div class="modulo-card" id="mod-{{ $key }}">
            <div class="modulo-card-header" onclick="toggleModulo('{{ $key }}')">
                <div class="modulo-card-title">
                    <span class="modulo-icon-badge" style="background:{{ $mod['color'] }}1a;color:{{ $mod['color'] }}">
                        <i class="fa-solid {{ $mod['icono'] }}"></i>
                    </span>
                    <div>
                        <strong>{{ $mod['nombre'] }}</strong>
                        <span class="modulo-progress-text" id="prog-{{ $key }}">0/{{ $total }} activos</span>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:.75rem">
                    <div class="mini-progress">
                        <div class="mini-progress-bar" id="pbar-{{ $key }}" style="background:{{ $mod['color'] }};width:0%"></div>
                    </div>
                    <label class="mod-toggle" onclick="event.stopPropagation()">
                        <input type="checkbox" class="mod-all-cb" data-mod="{{ $key }}" onchange="toggleModulo_cb(this)">
                        <span class="mod-toggle-slider" style="--tc:{{ $mod['color'] }}"></span>
                    </label>
                    <i class="fa-solid fa-chevron-down modulo-caret caret-up" id="caret-{{ $key }}"></i>
                </div>
            </div>

            <div class="modulo-perms-grid" id="perms-{{ $key }}">
                @foreach(collect($permsModulo)->groupBy('categoria') as $cat => $catPerms)
                <div class="perm-category">
                    <div class="perm-cat-label">{{ $cat }}</div>
                    <div class="perm-toggles">
                        @foreach($catPerms as $perm)
                        <label class="perm-toggle-row" id="row-{{ $perm['id'] }}">
                            <div class="perm-row-info">
                                <span class="perm-row-desc">{{ $perm['descripcion'] }}</span>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" name="permisos[]" value="{{ $perm['id'] }}"
                                       class="perm-cb mod-perm-{{ $key }}"
                                       {{ in_array($perm['id'], old('permisos', [])) ? 'checked' : '' }}
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

@include('admin.roles._permisos_styles')

<script>
function toggleModulo(key) {
    const grid  = document.getElementById('perms-' + key);
    const caret = document.getElementById('caret-' + key);
    grid.classList.toggle('show');
    caret.classList.toggle('caret-up');
}
function toggleModulo_cb(cb) {
    const key    = cb.dataset.mod;
    const checks = document.querySelectorAll('.mod-perm-' + key);
    const grid   = document.getElementById('perms-' + key);
    const caret  = document.getElementById('caret-' + key);
    checks.forEach(c => { c.checked = cb.checked; c.closest('.perm-toggle-row').classList.toggle('active', cb.checked); });
    if (cb.checked && !grid.classList.contains('show')) { grid.classList.add('show'); caret.classList.remove('caret-up'); }
    updateProgreso(key); updateConteoGlobal();
}
function onPermChange(cb, key) {
    cb.closest('.perm-toggle-row').classList.toggle('active', cb.checked);
    updateProgreso(key); updateConteoGlobal();
    const checks = document.querySelectorAll('.mod-perm-' + key);
    document.querySelector('.mod-all-cb[data-mod="' + key + '"]').checked = [...checks].every(c => c.checked);
}
function updateProgreso(key) {
    const checks = document.querySelectorAll('.mod-perm-' + key);
    const n = [...checks].filter(c=>c.checked).length, t = checks.length;
    document.getElementById('prog-' + key).textContent = n + '/' + t + ' activos';
    document.getElementById('pbar-' + key).style.width = (t>0?Math.round(n/t*100):0)+'%';
}
function updateConteoGlobal() {
    document.getElementById('n-marcados').textContent = document.querySelectorAll('input[name="permisos[]"]:checked').length;
}
function toggleTodo(e) {
    document.querySelectorAll('input[name="permisos[]"]').forEach(c => { c.checked=e; c.closest('.perm-toggle-row').classList.toggle('active',e); });
    document.querySelectorAll('.mod-all-cb').forEach(c => c.checked=e);
    document.querySelectorAll('[id^="mod-"]').forEach(m => { const k=m.id.replace('mod-',''); updateProgreso(k); });
    updateConteoGlobal();
}
// Inicializa old() values
document.querySelectorAll('input[name="permisos[]"]:checked').forEach(c => {
    c.closest('.perm-toggle-row').classList.add('active');
    onPermChange(c, c.className.match(/mod-perm-(\S+)/)?.[1] || '');
});

// Serializa los IDs marcados en un campo JSON antes de enviar
document.getElementById('form-rol').addEventListener('submit', function() {
    const ids = [...document.querySelectorAll('input[name="permisos[]"]:checked')].map(c => parseInt(c.value));
    document.getElementById('permisos_json').value = JSON.stringify(ids);
});
</script>
@endsection
