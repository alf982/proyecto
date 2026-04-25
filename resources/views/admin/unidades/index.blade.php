@extends('layouts.app')
@section('title', 'Unidades Ejecutoras')
@section('breadcrumb')
    <span>Administración</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Unidades Ejecutoras</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div><h1 class="page-title">Unidades Ejecutoras</h1><p class="page-subtitle">Estructura organizativa del ente</p></div>
    <a href="{{ route('admin.unidades.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nueva Unidad</a>
</div>

<div class="card fade-up" style="margin-bottom:20px;">
    <div class="card-body" style="padding:16px 22px;">
        <form method="GET" style="display:flex;gap:12px;align-items:flex-end;">
            <div style="flex:1;min-width:200px;">
                <label class="form-label">Buscar</label>
                <div style="position:relative;">
                    <input type="text" name="search" class="form-control" placeholder="Código o nombre..." value="{{ request('search') }}" style="padding-left:38px;">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--text-secondary);font-size:13px;"></i>
                </div>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Filtrar</button>
                <a href="{{ route('admin.unidades.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="card fade-up" style="animation-delay:.05s">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Unidad Superior</th>
                    <th>Estado</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($unidades as $unidad)
                <tr>
                    <td><code style="background:rgba(34,211,166,0.1);color:var(--accent-3);padding:3px 8px;border-radius:5px;font-size:13px;font-weight:600;">{{ $unidad->codigo }}</code></td>
                    <td style="font-size:13.5px;font-weight:500;">{{ $unidad->nombre }}</td>
                    <td style="font-size:12px;color:var(--text-secondary);">{{ $unidad->parent?->nombre ?? '—' }}</td>
                    <td><span class="badge {{ $unidad->activo ? 'badge-active' : 'badge-danger' }}">{{ $unidad->activo ? 'Activa' : 'Inactiva' }}</span></td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;align-items:center;">
                            <a href="{{ route('admin.unidades.edit', $unidad) }}" class="btn btn-outline btn-sm">
                                <i class="fa-solid fa-pen-to-square"></i> Editar
                            </a>
                            <form method="POST"
                                  action="{{ route('admin.unidades.destroy', $unidad) }}"
                                  style="margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5">
                    <div class="empty-state">
                        <div class="empty-icon">🏢</div>
                        <div class="empty-title">No hay unidades ejecutoras</div>
                        <div class="empty-desc">Define la estructura organizativa del ente.</div>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
