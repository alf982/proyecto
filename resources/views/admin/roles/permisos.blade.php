@extends('layouts.app')
@section('title', 'Permisos del Sistema')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-key"></i> Permisos del Sistema</h1>
        <p class="page-subtitle">{{ collect($permisos)->flatten()->count() }} permisos registrados en {{ count($permisos) }} módulos</p>
    </div>
    <div style="display:flex;gap:.75rem">
        @can('roles.gestionar')
        <a href="{{ route('admin.permisos.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Nuevo Permiso
        </a>
        @endcan
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Volver a Roles
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger"><i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}</div>
@endif

@foreach($permisos as $modulo => $lista)
<div class="card" style="margin-bottom:1rem">
    <div class="modulo-perm-header">
        <div style="display:flex;align-items:center;gap:.75rem">
            <span class="modulo-icon-badge">
                <i class="fa-solid fa-{{ match($modulo) {
                    'usuarios'      => 'users',
                    'roles'         => 'shield-halved',
                    'unidades'      => 'building',
                    'beneficiarios' => 'address-book',
                    'ejercicios'    => 'calendar-days',
                    'partidas'      => 'list-ol',
                    'proyectos'     => 'diagram-project',
                    'creditos'      => 'coins',
                    'compromisos'   => 'handshake',
                    'causaciones'   => 'file-invoice',
                    'pagos'         => 'money-bill-wave',
                    'modificaciones'=> 'arrows-rotate',
                    'presupuesto'   => 'chart-bar',
                    'tesoreria'     => 'piggy-bank',
                    'contabilidad'  => 'calculator',
                    'compras'       => 'cart-shopping',
                    'bienes'        => 'box-archive',
                    'nomina'        => 'id-badge',
                    'ingresos'      => 'cash-register',
                    'dashboard'     => 'gauge',
                    default         => 'circle-dot',
                } }}"></i>
            </span>
            <div>
                <h3 style="font-size:1rem;font-weight:600;color:var(--text-primary)">{{ ucfirst($modulo) }}</h3>
                <span style="font-size:.78rem;color:var(--text-secondary)">{{ $lista->count() }} permiso(s)</span>
            </div>
        </div>
    </div>

    <div class="permisos-table-grid">
        @foreach($lista->sortBy('name') as $perm)
        <div class="perm-row">
            <div class="perm-name-cell">
                <code class="perm-code">{{ $perm->name }}</code>
                @if($perm->roles_count > 0)
                <span class="perm-roles-badge">{{ $perm->roles_count }} rol(es)</span>
                @else
                <span class="perm-roles-badge perm-unused">sin asignar</span>
                @endif
            </div>
            @can('roles.gestionar')
            <div class="perm-actions-cell">
                <a href="{{ route('admin.permisos.edit', $perm) }}" class="btn btn-xs btn-outline" title="Renombrar">
                    <i class="fa-solid fa-pen"></i>
                </a>
                @if($perm->roles_count === 0)
                <form method="POST" action="{{ route('admin.permisos.destroy', $perm) }}"
                      onsubmit="return confirm('¿Eliminar permiso « {{ $perm->name }} »?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-xs btn-danger-outline" title="Eliminar">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
                @else
                <button class="btn btn-xs btn-danger-outline" disabled title="Está en uso — quítalo de todos los roles primero" style="opacity:.4;cursor:not-allowed">
                    <i class="fa-solid fa-lock"></i>
                </button>
                @endif
            </div>
            @endcan
        </div>
        @endforeach
    </div>
</div>
@endforeach

<style>
.modulo-perm-header { padding:1rem 1.25rem; border-bottom:1px solid var(--border); }
.modulo-icon-badge { width:38px;height:38px;background:rgba(79,142,247,.12);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#4f8ef7;font-size:.9rem; }
.permisos-table-grid { display:flex;flex-direction:column; }
.perm-row { display:flex;align-items:center;justify-content:space-between;padding:.6rem 1.25rem;border-bottom:1px solid var(--border);transition:background var(--transition); }
.perm-row:last-child { border-bottom:none; }
.perm-row:hover { background:var(--bg-card-hover); }
.perm-name-cell { display:flex;align-items:center;gap:.75rem; }
.perm-code { font-size:.82rem;color:var(--text-primary);background:rgba(255,255,255,.04);padding:.15rem .5rem;border-radius:5px; }
.perm-roles-badge { font-size:.7rem;padding:.15rem .5rem;border-radius:20px;background:rgba(34,211,166,.1);color:#22d3a6;border:1px solid rgba(34,211,166,.2); }
.perm-unused { background:rgba(138,145,168,.1);color:var(--text-secondary);border-color:var(--border); }
.perm-actions-cell { display:flex;gap:.4rem; }
.btn-xs { padding:.25rem .55rem;font-size:.75rem;border-radius:6px; }
.btn-outline { background:transparent;border:1px solid var(--border);color:var(--text-secondary);cursor:pointer;transition:var(--transition); }
.btn-outline:hover { border-color:var(--accent);color:var(--accent); }
.btn-danger-outline { background:transparent;border:1px solid rgba(247,95,95,.3);color:#f75f5f;cursor:pointer;transition:var(--transition); }
.btn-danger-outline:not([disabled]):hover { background:rgba(247,95,95,.1); }
</style>
@endsection
