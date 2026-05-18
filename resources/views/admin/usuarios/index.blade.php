@extends('layouts.app')
@section('title', 'Usuarios')
@section('breadcrumb')
    <span>Administración</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Usuarios</span>
@endsection

@section('content')
{{-- 
  VISTA INDEX DE USUARIOS
  Muestra el listado de todos los usuarios registrados en el sistema.
  Incluye barra de búsqueda (por nombre/email) y filtro por Rol.
--}}

<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Usuarios del Sistema</h1>
        <p class="page-subtitle">Gestión de cuentas y roles de acceso</p>
    </div>
    @can('usuarios.crear')
    <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-user-plus"></i> Nuevo Usuario
    </a>
    @endcan
</div>

<!-- Filtros de Búsqueda -->
<div class="card fade-up" style="margin-bottom:20px;">
    <div class="card-body" style="padding:16px 22px;">
        <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:1;min-width:200px;">
                <label class="form-label">Buscar</label>
                <div style="position:relative;">
                    <input type="text" name="search" class="form-control" placeholder="Nombre o correo..." value="{{ request('search') }}" style="padding-left:38px;">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--text-secondary);font-size:13px;"></i>
                </div>
            </div>
            <div style="min-width:180px;">
                <label class="form-label">Rol</label>
                <select name="rol" class="form-control">
                    <option value="">Todos los roles</option>
                    @foreach($roles as $rol)
                        <option value="{{ $rol->name }}" {{ request('rol') == $rol->name ? 'selected' : '' }}>{{ $rol->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Filtrar</button>
                <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Resultados -->
<div class="card fade-up" style="animation-delay:.05s">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Cédula</th>
                    <th>Rol</th>
                    <th>Unidad Ejecutora</th>
                    <th>Estado</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($q as $usuario)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:11px;">
                            <div style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,var(--accent-2),var(--accent));display:grid;place-items:center;font-weight:700;font-size:13px;flex-shrink:0;">
                                {{ strtoupper(substr($usuario->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600;font-size:13.5px;">{{ $usuario->name }}</div>
                                <div style="font-size:12px;color:var(--text-secondary);">{{ $usuario->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:13px;font-family:monospace;">
                        @if($usuario->cedula)
                            @php
                                preg_match('/^([VEJGvejg])-?(\d+)$/', $usuario->cedula, $m);
                                $pref = isset($m[1]) ? strtoupper($m[1]) . '-' : '';
                                $num  = $m[2] ?? $usuario->cedula;
                            @endphp
                            <span style="color:var(--accent);font-weight:700;">{{ $pref }}</span><span>{{ $num }}</span>
                        @else
                            <span style="color:var(--text-secondary);">—</span>
                        @endif
                    </td>
                    <td>
                        @if($usuario->roles->isNotEmpty())
                            <span class="badge badge-blue">{{ $usuario->roles->first()->name }}</span>
                        @else
                            <span style="color:var(--text-secondary);font-size:12px;">Sin rol</span>
                        @endif
                    </td>
                    <td style="font-size:13px;">{{ $usuario->unidadEjecutora?->nombre ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $usuario->activo ? 'badge-active' : 'badge-danger' }}">
                            {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;">
                            <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="btn btn-outline btn-sm">
                                <i class="fa-solid fa-pen-to-square"></i> Editar
                            </a>
                            @if($usuario->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.usuarios.destroy', $usuario) }}"
                                  onsubmit="return confirm('¿Desactivar al usuario {{ addslashes($usuario->name) }}?')"
                                  style="margin:0">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="btn btn-sm {{ $usuario->activo ? 'btn-danger' : '' }}"
                                    style="{{ !$usuario->activo ? 'background:rgba(34,211,166,0.12);border:1px solid rgba(34,211,166,0.3);color:var(--accent-3);' : '' }}"
                                    title="{{ $usuario->activo ? 'Desactivar' : 'Activar' }}">
                                    <i class="fa-solid {{ $usuario->activo ? 'fa-ban' : 'fa-circle-check' }}"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6">
                    <div class="empty-state">
                        <div class="empty-icon">👤</div>
                        <div class="empty-title">No hay usuarios</div>
                        <div class="empty-desc">Comienza creando el primer usuario del sistema.</div>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Componente reutilizable de paginación --}}
    <x-pagination :paginator="$q" />
</div>
@endsection
