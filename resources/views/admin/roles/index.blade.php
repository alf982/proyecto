@extends('layouts.app')
@section('title', 'Roles de Usuario')

@section('content')
{{-- 
  VISTA INDEX DE ROLES
  Muestra los perfiles de seguridad disponibles en el sistema (RBAC).
  Presenta tarjetas (Cards) con información sobre cuántos usuarios tienen el rol,
  y el nivel de acceso (cantidad de permisos).
--}}

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-shield-halved"></i> Roles de Usuario</h1>
        <p class="page-subtitle">Controla qué puede hacer cada tipo de usuario en el sistema</p>
    </div>
    @can('roles.gestionar')
    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Nuevo Rol
    </a>
    @endcan
</div>

@if(session('success'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger"><i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}</div>
@endif

{{-- Estadística rápida --}}
<div class="stats-strip">
    <div class="stat-item">
        <span class="stat-num">{{ $roles->count() }}</span>
        <span class="stat-label">Roles definidos</span>
    </div>
    <div class="stat-item">
        <span class="stat-num">{{ $roles->sum('users_count') }}</span>
        <span class="stat-label">Usuarios asignados</span>
    </div>
    <div class="stat-item">
        <span class="stat-num">{{ $roles->max(fn($r) => $r->permissions->count()) }}</span>
        <span class="stat-label">Máx. permisos por rol</span>
    </div>
</div>

<div class="roles-grid">
@foreach($roles as $rol)
@php
    $esSistema = in_array($rol->name, ['super-admin', 'administrador']);
    $color = $rol->color ?: '#4f8ef7';
    $icono = $rol->icono ?: 'fa-user-shield';
    $desc  = $rol->descripcion ?: 'Rol personalizado';
@endphp
<div class="rol-card" style="--rol-color:{{ $color }}">
    <div class="rol-card-top">
        <div class="rol-avatar">
            <i class="fa-solid {{ $icono }}"></i>
        </div>
        <div class="rol-card-info">
            <h3 class="rol-title">{{ ucwords(str_replace('-', ' ', $rol->name)) }}</h3>
            <p class="rol-desc">{{ $desc }}</p>
        </div>
    </div>

    <div class="rol-stats">
        <div class="rol-stat">
            <i class="fa-solid fa-users"></i>
            <strong>{{ $rol->users_count }}</strong>
            <span>{{ $rol->users_count === 1 ? 'usuario' : 'usuarios' }}</span>
        </div>
        @if($rol->name !== 'super-admin')
        <div class="rol-stat">
            <i class="fa-solid fa-key"></i>
            <strong>{{ $rol->permissions->count() }}</strong>
            <span>{{ $rol->permissions->count() === 1 ? 'permiso' : 'permisos' }}</span>
        </div>
        @else
        <div class="rol-stat">
            <i class="fa-solid fa-infinity"></i>
            <span>Acceso total</span>
        </div>
        @endif
    </div>

    <div class="rol-actions-bar">
        @can('roles.gestionar')
        <a href="{{ route('admin.roles.edit', $rol) }}" class="rol-btn rol-btn-edit">
            <i class="fa-solid fa-sliders"></i> Configurar
        </a>
        @if(!$esSistema && $rol->users_count === 0)
        <form method="POST" action="{{ route('admin.roles.destroy', $rol) }}"
              onsubmit="return confirm('¿Eliminar el rol « {{ ucwords(str_replace('-',' ',$rol->name)) }} »? Esta acción no se puede deshacer.')">
            @csrf @method('DELETE')
            <button class="rol-btn rol-btn-del" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
        </form>
        @elseif($esSistema)
        <span class="rol-locked" title="Rol del sistema — no eliminable"><i class="fa-solid fa-lock"></i></span>
        @endif
        @endcan
    </div>
</div>
@endforeach
</div>

<style>
.stats-strip { display:flex; gap:1rem; margin-bottom:1.5rem; }
.stat-item { flex:1; background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:1rem 1.25rem; display:flex; flex-direction:column; align-items:center; }
.stat-num { font-size:1.8rem; font-weight:800; color:var(--text-primary); line-height:1; }
.stat-label { font-size:.75rem; color:var(--text-secondary); margin-top:.3rem; }

.roles-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:1.25rem; }

.rol-card {
    background:var(--bg-card);
    border:1px solid var(--border);
    border-top:3px solid var(--rol-color, var(--accent));
    border-radius:14px;
    padding:1.5rem;
    display:flex; flex-direction:column; gap:1rem;
    transition:transform .2s, box-shadow .2s;
}
.rol-card:hover { transform:translateY(-2px); box-shadow:0 8px 30px rgba(0,0,0,.3); }

.rol-card-top { display:flex; align-items:flex-start; gap:1rem; }
.rol-avatar {
    width:48px; height:48px; flex-shrink:0; border-radius:12px;
    background:color-mix(in srgb, var(--rol-color) 15%, transparent);
    display:flex; align-items:center; justify-content:center;
    color:var(--rol-color); font-size:1.2rem;
}
.rol-card-info { flex:1; min-width:0; }
.rol-title { font-size:.95rem; font-weight:700; color:var(--text-primary); margin-bottom:.25rem; }
.rol-desc { font-size:.78rem; color:var(--text-secondary); line-height:1.4; }

.rol-stats { display:flex; gap:1.25rem; padding:.75rem 0; border-top:1px solid var(--border); border-bottom:1px solid var(--border); }
.rol-stat { display:flex; align-items:center; gap:.4rem; font-size:.82rem; color:var(--text-secondary); }
.rol-stat i { color:var(--rol-color); font-size:.75rem; }
.rol-stat strong { color:var(--text-primary); }

.rol-actions-bar { display:flex; align-items:center; gap:.6rem; }
.rol-btn { display:flex; align-items:center; gap:.4rem; padding:.45rem .9rem; border-radius:8px; font-size:.82rem; font-weight:500; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
.rol-btn-edit { flex:1; justify-content:center; background:color-mix(in srgb, var(--rol-color) 12%, transparent); color:var(--rol-color); border:1px solid color-mix(in srgb, var(--rol-color) 30%, transparent); }
.rol-btn-edit:hover { background:color-mix(in srgb, var(--rol-color) 25%, transparent); }
.rol-btn-del { background:rgba(247,95,95,.1); color:#f75f5f; border:1px solid rgba(247,95,95,.25); padding:.45rem .7rem; }
.rol-btn-del:hover { background:rgba(247,95,95,.2); }
.rol-locked { display:flex; align-items:center; padding:.45rem .7rem; color:var(--text-secondary); opacity:.4; font-size:.82rem; }
</style>
@endsection

