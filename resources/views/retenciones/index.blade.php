@extends('layouts.app')

@section('title', 'Retenciones Fiscales')

@section('breadcrumb')
    <span>Configuración Fiscal</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Retenciones</span>
@endsection

@section('content')
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-percent" style="color:var(--accent);margin-right:8px;"></i>Retenciones Fiscales</h1>
        <p class="page-subtitle">Catálogo de impuestos y retenciones aplicables a las transacciones del sistema.</p>
    </div>
    @can('retenciones.crear')
    <a href="{{ route('retenciones.create') }}" class="btn btn-primary" id="btn-nueva-retencion">
        <i class="fa-solid fa-plus"></i> Nueva Retención
    </a>
    @endcan
</div>

{{-- Estadísticas rápidas --}}
@php
    $total   = $retenciones->total();
    $activas = \App\Models\Retencion::where('activo', true)->count();
    $oblig   = \App\Models\Retencion::where('obligatoria', true)->where('activo', true)->count();
@endphp
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;margin-bottom:24px;">
    <div class="card" style="padding:18px 20px;">
        <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px;">Total Retenciones</div>
        <div style="font-size:26px;font-weight:700;">{{ $total }}</div>
    </div>
    <div class="card" style="padding:18px 20px;">
        <div style="font-size:11px;color:var(--accent-3);text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px;">Activas</div>
        <div style="font-size:26px;font-weight:700;color:var(--accent-3);">{{ $activas }}</div>
    </div>
    <div class="card" style="padding:18px 20px;">
        <div style="font-size:11px;color:var(--accent-warn);text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px;">Obligatorias</div>
        <div style="font-size:26px;font-weight:700;color:var(--accent-warn);">{{ $oblig }}</div>
    </div>
</div>

<div class="card fade-up">
    <div class="card-header">
        <span class="card-title"><i class="fa-solid fa-list" style="margin-right:6px;color:var(--accent)"></i>Listado de Retenciones</span>
    </div>

    @if($retenciones->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fa-solid fa-percent"></i></div>
            <div class="empty-title">No hay retenciones registradas</div>
            <div class="empty-desc">Crea la primera retención usando el botón superior.</div>
        </div>
    @else
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Tipo / Valor</th>
                    <th>Base de Cálculo</th>
                    <th>Aplica a</th>
                    <th>Obligatoria</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($retenciones as $ret)
                <tr>
                    <td>
                        <span style="font-family:monospace;font-size:13px;background:rgba(79,142,247,0.1);color:var(--accent);padding:3px 8px;border-radius:5px;">
                            {{ $ret->codigo }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight:500;">{{ $ret->nombre }}</div>
                        @if($ret->descripcion)
                            <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">{{ Str::limit($ret->descripcion, 60) }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $ret->tipo === 'porcentaje' ? 'badge-blue' : 'badge-purple' }}">
                            {{ $ret->tipo_label }}
                        </span>
                    </td>
                    <td style="color:var(--text-secondary);font-size:12px;">
                        {{ $ret->base_calculo === 'monto_bruto' ? 'Monto Bruto' : 'Monto Neto' }}
                    </td>
                    <td>
                        <div style="display:flex;flex-wrap:wrap;gap:4px;">
                            @foreach($ret->aplica_a as $mod)
                                <span style="font-size:10px;padding:2px 7px;border-radius:4px;background:rgba(255,255,255,0.06);color:var(--text-secondary);">
                                    {{ \App\Models\Retencion::modulosDisponibles()[$mod] ?? $mod }}
                                </span>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        @if($ret->obligatoria)
                            <span class="badge badge-warn"><i class="fa-solid fa-lock" style="font-size:9px;margin-right:3px;"></i>Sí</span>
                        @else
                            <span style="color:var(--text-secondary);font-size:12px;">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $ret->estado_badge }}">
                            {{ $ret->activo ? 'Activa' : 'Inactiva' }}
                        </span>
                    </td>
                    <td>
                        <div class="actions-wrap" style="display:flex;gap:6px;align-items:center;">
                            @can('retenciones.editar')
                            <a href="{{ route('retenciones.edit', $ret) }}" class="btn btn-outline btn-sm" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form method="POST" action="{{ route('retenciones.toggle', $ret) }}" style="margin:0;">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $ret->activo ? 'btn-danger' : 'btn-outline' }}"
                                    title="{{ $ret->activo ? 'Desactivar' : 'Activar' }}"
                                    onclick="return confirm('¿{{ $ret->activo ? 'Desactivar' : 'Activar' }} esta retención?')">
                                    <i class="fa-solid fa-{{ $ret->activo ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('retenciones.destroy', $ret) }}" style="margin:0;"
                                onsubmit="return confirm('¿Eliminar la retención «{{ $ret->nombre }}»? Esta acción no se puede deshacer.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm" title="Eliminar"
                                    style="color:var(--accent-danger);background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.3);">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($retenciones->hasPages())
    <div class="pagination">
        @foreach($retenciones->links()->elements as $element)
            @if(is_string($element))
                <span class="page-link" style="cursor:default;opacity:.4;">{{ $element }}</span>
            @elseif(is_array($element))
                @foreach($element as $page => $url)
                    <a href="{{ $url }}" class="page-link {{ $retenciones->currentPage() == $page ? 'active' : '' }}">{{ $page }}</a>
                @endforeach
            @endif
        @endforeach
        <span class="page-info">{{ $retenciones->total() }} retenciones</span>
    </div>
    @endif
    @endif
</div>
@endsection
