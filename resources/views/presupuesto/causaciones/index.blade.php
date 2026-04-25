@extends('layouts.app')
@section('title', 'Causaciones')
@section('breadcrumb')
    <span>Presupuesto</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Causaciones</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Causaciones / Órdenes de Pago</h1>
        <p class="page-subtitle">Registro de compromisos y órdenes de pago emitidas</p>
    </div>
    <a href="{{ route('presupuesto.causaciones.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Nueva Causación
    </a>
</div>

<!-- Filtros -->
<div class="card fade-up" style="margin-bottom:20px;">
    <div class="card-body" style="padding:16px 22px;">
        <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:1;min-width:200px;">
                <label class="form-label">Buscar</label>
                <div style="position:relative;">
                    <input type="text" name="search" class="form-control" placeholder="N° causación, beneficiario, concepto..."
                        value="{{ request('search') }}" style="padding-left:38px;">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--text-secondary);font-size:13px;"></i>
                </div>
            </div>
            <div style="min-width:150px;">
                <label class="form-label">Ejercicio</label>
                <select name="ejercicio" class="form-control">
                    <option value="">Todos</option>
                    @foreach($ejercicios as $ej)
                        <option value="{{ $ej->id }}" {{ request('ejercicio') == $ej->id ? 'selected' : '' }}>{{ $ej->anio }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:180px;">
                <label class="form-label">Unidad</label>
                <select name="unidad" class="form-control">
                    <option value="">Todas</option>
                    @foreach($unidades as $u)
                        <option value="{{ $u->id }}" {{ request('unidad') == $u->id ? 'selected' : '' }}>{{ $u->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:140px;">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-control">
                    <option value="">Todos</option>
                    <option value="borrador"  {{ request('estado') === 'borrador'  ? 'selected' : '' }}>Borrador</option>
                    <option value="aprobada"  {{ request('estado') === 'aprobada'  ? 'selected' : '' }}>Aprobada</option>
                    <option value="pagada"    {{ request('estado') === 'pagada'    ? 'selected' : '' }}>Pagada</option>
                    <option value="anulada"   {{ request('estado') === 'anulada'   ? 'selected' : '' }}>Anulada</option>
                </select>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Filtrar</button>
                <a href="{{ route('presupuesto.causaciones.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla -->
<div class="card fade-up" style="animation-delay:.05s;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>N° Causación</th>
                    <th>Fecha</th>
                    <th>Beneficiario</th>
                    <th>Partida</th>
                    <th>Concepto</th>
                    <th style="text-align:right;">Monto</th>
                    <th>Estado</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($q as $causacion)
                <tr>
                    <td>
                        <code style="background:rgba(79,142,247,0.1);color:var(--accent);padding:3px 8px;border-radius:5px;font-size:12px;font-weight:600;">
                            {{ $causacion->numero }}
                        </code>
                    </td>
                    <td style="font-size:12px;color:var(--text-secondary);">
                        {{ $causacion->fecha_causacion->format('d/m/Y') }}
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $causacion->beneficiario }}</div>
                        @if($causacion->rif_beneficiario)
                            <div style="font-size:11px;color:var(--text-secondary);">{{ $causacion->rif_beneficiario }}</div>
                        @endif
                    </td>
                    <td>
                        <code style="font-size:11px;color:var(--text-secondary);">{{ $causacion->partida?->codigo ?? '—' }}</code>
                    </td>
                    <td style="font-size:12px;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $causacion->concepto }}
                    </td>
                    <td style="text-align:right;font-weight:700;font-family:monospace;">
                        {{ number_format($causacion->monto_causado, 2) }}
                        @if($causacion->monto_retencion > 0)
                            <div style="font-size:10px;color:var(--accent-danger);font-weight:400;">
                                ret: {{ number_format($causacion->monto_retencion, 2) }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $causacion->getEstadoBadgeClass() }}">
                            {{ $causacion->getEstadoLabel() }}
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;">
                            <a href="{{ route('presupuesto.causaciones.show', $causacion) }}" class="btn btn-outline btn-sm" title="Ver detalle">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            @if($causacion->esBorrador())
                            <a href="{{ route('presupuesto.causaciones.edit', $causacion) }}" class="btn btn-outline btn-sm" title="Editar">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8">
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <div class="empty-title">No hay causaciones registradas</div>
                        <div class="empty-desc">Registra la primera causación para comenzar el seguimiento de pagos.</div>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($q->hasPages())
    <div class="pagination">
        @foreach($q->links()->elements[0] as $page => $url)
            <a href="{{ $url }}" class="page-link {{ $q->currentPage() == $page ? 'active' : '' }}">{{ $page }}</a>
        @endforeach
        <span class="page-info">{{ $q->firstItem() }}–{{ $q->lastItem() }} de {{ $q->total() }}</span>
    </div>
    @endif
</div>
@endsection
