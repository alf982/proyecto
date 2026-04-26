@extends('layouts.app')

@section('title', 'Historial de Auditoría')

@section('breadcrumb')
    <span>Administración</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Auditoría del Sistema</span>
@endsection

@section('content')
<div class="page-header fade-up">
    <h1 class="page-title"><i class="fa-solid fa-shield-halved" style="color:var(--accent);margin-right:10px;"></i>Historial de Auditoría</h1>
    <p class="page-subtitle">Registros de todos los cambios realizados en el sistema.</p>
</div>

{{-- Filtros --}}
<div class="card fade-up" style="margin-bottom:20px;">
    <div class="card-body" style="padding:16px 22px;">
        <form method="GET" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:12px;align-items:end;">
            <div class="form-group" style="margin:0;">
                <label class="form-label">Modelo</label>
                <select name="modelo" class="form-control">
                    <option value="">Todos los modelos</option>
                    @foreach($modelos as $m)
                        <option value="{{ $m }}" {{ request('modelo') === $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Evento</label>
                <select name="evento" class="form-control">
                    <option value="">Todos</option>
                    @foreach($eventos as $e)
                        <option value="{{ $e }}" {{ request('evento') === $e ? 'selected' : '' }}>{{ ucfirst($e) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Usuario</label>
                <select name="usuario" class="form-control">
                    <option value="">Todos</option>
                    @foreach($usuarios as $id => $nombre)
                        <option value="{{ $id }}" {{ request('usuario') == $id ? 'selected' : '' }}>{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary" style="flex:1;"><i class="fa-solid fa-filter"></i> Filtrar</button>
                <a href="{{ route('admin.auditoria.index') }}" class="btn btn-outline"><i class="fa-solid fa-xmark"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Tabla de resultados --}}
<div class="card fade-up" style="animation-delay:.1s">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-list" style="color:var(--accent);margin-right:8px;"></i>Eventos Registrados</div>
        <span style="font-size:12px;color:var(--text-secondary);">{{ $query->total() }} evento(s) encontrado(s)</span>
    </div>
    @if($query->isEmpty())
    <div class="empty-state">
        <div class="empty-icon">🔍</div>
        <div class="empty-title">Sin registros de auditoría</div>
        <div class="empty-desc">No hay eventoss que coincidan con los filtros aplicados.</div>
    </div>
    @else
    <div class="table-wrap">
        <table>
            <thead><tr>
                <th>Fecha / Hora</th>
                <th>Usuario</th>
                <th>Evento</th>
                <th>Modelo</th>
                <th>ID Registro</th>
                <th>Campos Modificados</th>
                <th></th>
            </tr></thead>
            <tbody>
            @foreach($query as $audit)
            <tr>
                <td style="font-size:12px;white-space:nowrap;">
                    {{ $audit->created_at->format('d/m/Y') }}<br>
                    <span style="color:var(--text-secondary);">{{ $audit->created_at->format('H:i:s') }}</span>
                </td>
                <td style="font-size:13px;">
                    @if($audit->user)
                        <div style="font-weight:500;">{{ $audit->user->name }}</div>
                        <div style="font-size:11px;color:var(--text-secondary);">{{ $audit->user->email }}</div>
                    @else
                        <span style="color:var(--text-secondary);">Sistema</span>
                    @endif
                </td>
                <td>
                    @php
                        $badgeClass = match($audit->event) {
                            'created'  => 'badge-active',
                            'updated'  => 'badge-blue',
                            'deleted'  => 'badge-danger',
                            'restored' => 'badge-purple',
                            default    => 'badge-warn',
                        };
                        $eventLabel = match($audit->event) {
                            'created'  => 'Creado',
                            'updated'  => 'Actualizado',
                            'deleted'  => 'Eliminado',
                            'restored' => 'Restaurado',
                            default    => ucfirst($audit->event),
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $eventLabel }}</span>
                </td>
                <td style="font-size:12px;font-family:monospace;">{{ class_basename($audit->auditable_type) }}</td>
                <td style="font-size:12px;font-family:monospace;">#{{ $audit->auditable_id }}</td>
                <td style="font-size:12px;color:var(--text-secondary);">
                    @if($audit->new_values)
                        {{ count($audit->new_values) }} campo(s)
                        <span style="font-size:11px;">({{ implode(', ', array_keys(array_slice($audit->new_values, 0, 3))) }}{{ count($audit->new_values) > 3 ? '...' : '' }})</span>
                    @else
                        —
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.auditoria.show', $audit) }}" class="btn btn-sm btn-outline">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination">
        @foreach($query->links()->elements as $element)
            @if(is_string($element))
                <span class="page-link" style="opacity:.4;">{{ $element }}</span>
            @elseif(is_array($element))
                @foreach($element as $page => $url)
                    <a href="{{ $url }}" class="page-link {{ $page == $query->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                @endforeach
            @endif
        @endforeach
        <span class="page-info">{{ $query->firstItem() }}–{{ $query->lastItem() }} de {{ $query->total() }}</span>
    </div>
    @endif
</div>
@endsection
