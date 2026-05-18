@extends('layouts.app')
@section('title','Beneficiarios')
@section('breadcrumb')
    <span>Administración</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Beneficiarios</span>
@endsection
@section('content')

{{-- 
  VISTA INDEX DE BENEFICIARIOS
  Catálogo de proveedores, contratistas, y empleados. 
  Contiene filtros múltiples (por nombre/rif, tipo, y estado).
--}}

<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Beneficiarios</h1>
        <p class="page-subtitle">Proveedores, contratistas y funcionarios registrados</p>
    </div>
    @can('beneficiarios.crear')
    <a href="{{ route('admin.beneficiarios.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nuevo Beneficiario</a>
    @endcan
</div>

<!-- Barra de Filtros -->
<div class="card fade-up" style="margin-bottom:18px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:1;min-width:220px;">
                <label class="form-label">Buscar</label>
                <input type="text" name="search" class="form-control" placeholder="RIF, razón social..." value="{{ request('search') }}">
            </div>
            <div style="min-width:150px;">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-control">
                    <option value="">Todos</option>
                    <option value="proveedor"   {{ request('tipo')==='proveedor'   ?'selected':'' }}>Proveedor</option>
                    <option value="contratista" {{ request('tipo')==='contratista' ?'selected':'' }}>Contratista</option>
                    <option value="funcionario" {{ request('tipo')==='funcionario' ?'selected':'' }}>Funcionario</option>
                    <option value="otro"        {{ request('tipo')==='otro'        ?'selected':'' }}>Otro</option>
                </select>
            </div>
            <div style="min-width:120px;">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-control">
                    <option value="">Todos</option>
                    <option value="1" {{ request('estado')==='1'?'selected':'' }}>Activo</option>
                    <option value="0" {{ request('estado')==='0'?'selected':'' }}>Inactivo</option>
                </select>
            </div>
            <div style="display:flex;gap:6px;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Filtrar</button>
                <a href="{{ route('admin.beneficiarios.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
            </div>
        </form>
    </div>
</div>

@include('components.alert')

<!-- Tabla de Beneficiarios -->
<div class="card fade-up" style="animation-delay:.05s;">
    <div class="table-wrap">
        <table>
            <thead><tr>
                <th>RIF</th><th>Razón Social</th><th>Tipo</th>
                <th>Teléfono</th><th>Banco</th><th>Estado</th>
                <th style="text-align:right;">Acciones</th>
            </tr></thead>
            <tbody>
            @forelse($q as $b)
            <tr>
                <td><code style="background:rgba(79,142,247,0.1);color:var(--accent);padding:3px 7px;border-radius:5px;font-size:12px;font-weight:600;">{{ $b->rif }}</code></td>
                <td>
                    <div style="font-weight:600;font-size:13px;">{{ $b->razon_social }}</div>
                    @if($b->nombre_comercial)<div style="font-size:11px;color:var(--text-secondary);">{{ $b->nombre_comercial }}</div>@endif
                </td>
                <td><span class="badge badge-blue" style="font-size:11px;">{{ $b->getTipoLabel() }}</span></td>
                <td style="font-size:12px;color:var(--text-secondary);">{{ $b->telefono ?? '—' }}</td>
                <td style="font-size:12px;">
                    @if($b->banco_nombre)
                        <div>{{ $b->banco_nombre }}</div>
                        <div style="font-size:11px;color:var(--text-secondary);">{{ $b->banco_cuenta }}</div>
                    @else—@endif
                </td>
                <td>
                    <span class="badge {{ $b->activo ? 'badge-active' : 'badge-danger' }}">
                        {{ $b->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </td>
                <td style="text-align:right;">
                    <div style="display:flex;gap:6px;justify-content:flex-end;">
                        @can('beneficiarios.ver')
                        <a href="{{ route('admin.beneficiarios.show', $b) }}" class="btn btn-outline btn-sm" title="Ver Detalles"><i class="fa-solid fa-eye"></i></a>
                        @endcan
                        
                        @can('beneficiarios.editar')
                        <a href="{{ route('admin.beneficiarios.edit', $b) }}" class="btn btn-outline btn-sm" title="Editar"><i class="fa-solid fa-pen-to-square"></i></a>
                        <form method="POST" action="{{ route('admin.beneficiarios.toggle', $b) }}" style="margin:0;">
                            @csrf
                            <button type="submit" class="btn btn-sm" title="{{ $b->activo ? 'Desactivar para bloquear pagos' : 'Activar' }}" style="background:rgba(250,189,0,0.15);border:1px solid rgba(250,189,0,.35);color:var(--accent-warn);">
                                <i class="fa-solid {{ $b->activo ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                            </button>
                        </form>
                        @endcan

                        @can('usuarios.eliminar') {{-- FIXME: Chequear si es usuarios o beneficiarios en permisos --}}
                        <form method="POST" action="{{ route('admin.beneficiarios.destroy', $b) }}" style="margin:0;" onsubmit="return confirm('¿Eliminar beneficiario {{ addslashes($b->razon_social) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm" title="Eliminar (Soft Delete)" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,.3);color:var(--accent-danger);">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7">
                <div class="empty-state">
                    <div class="empty-icon">🏢</div>
                    <div class="empty-title">Sin beneficiarios registrados</div>
                    <div class="empty-desc">Registra el primer proveedor o contratista.</div>
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
