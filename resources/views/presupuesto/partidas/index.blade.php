@extends('layouts.app')
@section('title', 'Catálogo de Partidas')
@section('breadcrumb')
    <span>Presupuesto</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Catálogo de Partidas</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Catálogo de Partidas Presupuestarias</h1>
        <p class="page-subtitle">Clasificador oficial de partidas presupuestarias</p>
    </div>
    @can('partidas.crear')
    <a href="{{ route('presupuesto.partidas.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Nueva Partida
    </a>
    @endcan
</div>

<div class="card fade-up" style="margin-bottom:20px;">
    <div class="card-body" style="padding:16px 22px;">
        <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:1;min-width:200px;">
                <label class="form-label">Buscar</label>
                <div style="position:relative;">
                    <input type="text" name="search" class="form-control" placeholder="Código o descripción..." value="{{ request('search') }}" style="padding-left:38px;">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--text-secondary);font-size:13px;"></i>
                </div>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Filtrar</button>
                <a href="{{ route('presupuesto.partidas.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
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
                    <th>Descripción</th>
                    <th><i class="fa-solid fa-building-columns" style="margin-right:5px;"></i>Cuenta Bancaria</th>
                    <th>Saldo Actual</th>
                    <th>Estado</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($partidas as $partida)
                <tr>
                    <td>
                        <code style="background:rgba(79,142,247,0.1);color:var(--accent);padding:3px 8px;border-radius:5px;font-size:13px;font-weight:600;">{{ $partida->codigo }}</code>
                    </td>
                    <td style="font-size:13.5px;max-width:300px;">{{ $partida->descripcion }}</td>
                    <td>
                        @if($partida->cuentaBancaria)
                            <div style="font-size:12.5px;line-height:1.4;">
                                <div style="font-weight:600;color:var(--text-primary);">{{ $partida->cuentaBancaria->nombre }}</div>
                                <div style="color:var(--text-secondary);font-size:11px;">
                                    {{ $partida->cuentaBancaria->banco }}
                                    &nbsp;·&nbsp;
                                    <code style="font-size:10px;">{{ $partida->cuentaBancaria->numero_cuenta }}</code>
                                </div>
                            </div>
                        @else
                            <span style="color:var(--text-secondary);font-size:12px;font-style:italic;">Sin cuenta asignada</span>
                        @endif
                    </td>
                    <td style="text-align:right;font-weight:700;font-size:13px;">
                        Bs. {{ number_format($partida->saldo_actual, 2) }}
                    </td>
                    <td>
                        <span class="badge {{ $partida->activo ? 'badge-active' : 'badge-danger' }}">
                            {{ $partida->activo ? 'Activa' : 'Inactiva' }}
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;align-items:center;">
                            @can('partidas.editar')
                            <a href="{{ route('presupuesto.partidas.edit', $partida) }}" class="btn btn-outline btn-sm">
                                <i class="fa-solid fa-pen-to-square"></i> Editar
                            </a>
                            @endcan
                            @can('partidas.eliminar')
                            <form method="POST" action="{{ route('presupuesto.partidas.destroy', $partida) }}" style="margin:0;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6">
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <div class="empty-title">No hay partidas registradas</div>
                        <div class="empty-desc">Carga el clasificador presupuestario para comenzar.</div>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($partidas->hasPages())
    <div class="pagination">
        {{ $partidas->links() }}
        <span class="page-info">{{ $partidas->firstItem() }}–{{ $partidas->lastItem() }} de {{ $partidas->total() }}</span>
    </div>
    @endif
</div>
@endsection
