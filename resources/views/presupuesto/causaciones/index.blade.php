@extends('layouts.app')
@section('title', 'Causaciones')
@section('breadcrumb')
    <span>Presupuesto</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Causaciones</span>
@endsection

@section('content')
{{-- 
  VISTA INDEX DE CAUSACIONES
  Muestra el listado del gasto devengado.
  Incluye una "Bandeja de Entrada" en la parte superior para que los usuarios con permisos
  de aprobación puedan procesar rápidamente las causaciones que acaban de crearse en estado "borrador".
--}}

<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Causaciones / Órdenes de Pago</h1>
        <p class="page-subtitle">Registro de compromisos causados y órdenes de pago emitidas</p>
    </div>
    @can('causaciones.crear')
    <a href="{{ route('presupuesto.causaciones.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Nueva Causación
    </a>
    @endcan
</div>

@if(session('success'))
<div class="alert alert-success fade-up"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger fade-up"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
@endif

{{-- ── CAUSACIONES PENDIENTES DE APROBACIÓN (Bandeja de entrada) --}}
@if($causacionesPendientes->count())
<div class="card fade-up" style="margin-bottom:20px;border:1px solid rgba(247,187,67,0.35);">
    <div class="card-header" style="background:rgba(247,187,67,0.07);">
        <div class="card-title" style="color:var(--accent-warn);">
            <i class="fa-solid fa-clock" style="margin-right:8px;"></i>
            Causaciones pendientes de aprobación ({{ $causacionesPendientes->count() }})
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        <table class="table" style="margin:0;">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Fecha</th>
                    <th>Compromiso</th>
                    <th>Beneficiario</th>
                    <th style="max-width:180px;">Concepto</th>
                    <th style="text-align:right;">Monto</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($causacionesPendientes as $cp)
                <tr>
                    <td><code style="color:var(--accent);font-weight:700;">{{ $cp->numero }}</code></td>
                    <td style="font-size:12px;color:var(--text-secondary);">{{ $cp->fecha_causacion?->format('d/m/Y') ?? '—' }}</td>
                    <td><code style="font-size:11px;color:var(--accent);">{{ $cp->compromiso?->numero ?? '—' }}</code></td>
                    <td style="font-size:13px;font-weight:600;">{{ $cp->beneficiario }}</td>
                    <td style="font-size:12px;color:var(--text-secondary);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $cp->concepto }}
                    </td>
                    <td style="text-align:right;font-weight:700;font-family:monospace;font-size:14px;color:var(--accent-3);">
                        Bs. {{ number_format((float)$cp->monto_causado, 2) }}
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;">
                            @can('causaciones.ver')
                            <a href="{{ route('presupuesto.causaciones.show', $cp) }}" class="btn btn-sm btn-outline" title="Ver">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            @endcan
                            
                            @can('causaciones.aprobar')
                            <form method="POST" action="{{ route('presupuesto.causaciones.aprobar', $cp) }}" style="margin:0;"
                                  onsubmit="return confirm('¿Aprobar causación? Esto indicará que el devengado es definitivo.');">
                                @csrf
                                <button type="submit" class="btn btn-sm"
                                    style="background:rgba(247,187,67,0.15);border:1px solid rgba(247,187,67,.4);color:var(--accent-warn);white-space:nowrap;">
                                    <i class="fa-solid fa-check"></i> Aprobar
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
</div>
@endif

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
                <tr style="{{ $causacion->estado === 'anulada' ? 'opacity:.6;' : '' }}">
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
                            @can('causaciones.ver')
                            <a href="{{ route('presupuesto.causaciones.show', $causacion) }}" class="btn btn-outline btn-sm" title="Ver detalle">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            @endcan
                            
                            @if($causacion->esBorrador())
                            @can('causaciones.crear')
                            <a href="{{ route('presupuesto.causaciones.edit', $causacion) }}" class="btn btn-outline btn-sm" title="Editar">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            @endcan
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
    
    {{-- Componente estándar de paginación --}}
    <x-pagination :paginator="$q" />
</div>
@endsection
