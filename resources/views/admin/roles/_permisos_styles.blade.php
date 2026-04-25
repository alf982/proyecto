<style>
/* ── Layout dos columnas ─── */
.edit-layout { display:grid; grid-template-columns:260px 1fr; gap:1.5rem; align-items:start; }
@media(max-width:900px) { .edit-layout { grid-template-columns:1fr; } }

/* ── Resumen ─────────────── */
.summary-box { display:grid; grid-template-columns:1fr 1fr; gap:.5rem; }
.summary-item { background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:10px; padding:.75rem; text-align:center; }
.summary-num { display:block; font-size:1.5rem; font-weight:800; color:var(--text-primary); }
.summary-label { font-size:.72rem; color:var(--text-secondary); }

/* ── Tarjeta de módulo ────── */
.modulo-card { background:var(--bg-card); border:1px solid var(--border); border-radius:14px; margin-bottom:1rem; overflow:hidden; }
.modulo-card-header { display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; cursor:pointer; }
.modulo-card-title { display:flex; align-items:center; gap:.85rem; }
.modulo-icon-badge { width:36px; height:36px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:.85rem; flex-shrink:0; }
.modulo-card-title strong { font-size:.9rem; color:var(--text-primary); display:block; }
.modulo-progress-text { font-size:.75rem; color:var(--text-secondary); }
.mini-progress { width:60px; height:5px; background:rgba(255,255,255,.08); border-radius:3px; overflow:hidden; }
.mini-progress-bar { height:100%; width:0; border-radius:3px; transition:width .3s; }
.modulo-caret { font-size:.7rem; color:var(--text-secondary); transition:transform .2s; }
.modulo-caret.caret-up { transform:rotate(-90deg); }

/* ── Toggle módulo completo ─ */
.mod-toggle { position:relative; display:inline-flex; cursor:pointer; }
.mod-toggle input { opacity:0; width:0; height:0; position:absolute; }
.mod-toggle-slider { width:34px; height:19px; background:rgba(255,255,255,.08); border-radius:10px; position:relative; transition:background .2s; }
.mod-toggle-slider::after { content:''; position:absolute; left:3px; top:3px; width:13px; height:13px; background:#fff; border-radius:50%; transition:transform .2s; }
.mod-toggle input:checked ~ .mod-toggle-slider { background:var(--tc, var(--accent)); }
.mod-toggle input:checked ~ .mod-toggle-slider::after { transform:translateX(15px); }

/* ── Lista de permisos ──────── */
.modulo-perms-grid { display:none; padding:1rem 1.25rem; border-top:1px solid var(--border); background:rgba(0,0,0,.15); }
.modulo-perms-grid.show { display:block; }
.perm-category { margin-bottom:1rem; }
.perm-cat-label { font-size:.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--text-secondary); margin-bottom:.5rem; padding-bottom:.35rem; border-bottom:1px solid var(--border); }
.perm-toggles { display:flex; flex-direction:column; gap:.2rem; }

/* ── Fila permiso individual ─ */
.perm-toggle-row { display:flex; align-items:center; justify-content:space-between; padding:.55rem .85rem; border-radius:9px; cursor:pointer; transition:background .15s; }
.perm-toggle-row:hover { background:rgba(255,255,255,.04); }
.perm-toggle-row.active { background:rgba(79,142,247,.05); }
.perm-row-info { flex:1; min-width:0; padding-right:1rem; }
.perm-row-desc { font-size:.83rem; color:var(--text-secondary); display:block; }
.perm-toggle-row.active .perm-row-desc { color:var(--text-primary); }

/* ── Toggle switch individual ─ */
.toggle-switch { position:relative; flex-shrink:0; }
.toggle-switch input { opacity:0; width:0; height:0; position:absolute; }
.toggle-knob { display:block; width:40px; height:22px; background:rgba(255,255,255,.08); border-radius:11px; position:relative; transition:background .2s; cursor:pointer; }
.toggle-knob::after { content:''; position:absolute; left:3px; top:3px; width:16px; height:16px; background:#fff; border-radius:50%; transition:transform .2s; }
.toggle-switch input:checked ~ .toggle-knob { background:var(--tc, var(--accent)); }
.toggle-switch input:checked ~ .toggle-knob::after { transform:translateX(18px); }
</style>
