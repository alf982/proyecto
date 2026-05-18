@extends('layouts.app')
@section('title','Compromisos')
@section('breadcrumb')
    <span>Presupuesto</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Compromisos</span>
@endsection
@section('content')

{{-- 
  VISTA INDEX DE COMPROMISOS
  Muestra el listado de reservas presupuestarias.
  Incluye una "Bandeja de Entrada" en la parte superior para que los usuarios con permisos
  de aprobación puedan procesar rápidamente los compromisos en estado "borrador".
--}}

<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Compromisos Presupuestarios</h1>
        <p class="page-subtitle">Reservas de crédito previas a la causación</p>
    </div>
    @can('compromisos.crear')
    <a href="{{ route('presupuesto.compromisos.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nuevo Compromiso</a>
    @endcan
</div>
@if(session('success'))
<div class="alert alert-success fade-up"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger fade-up"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
@endif

{{-- ── COMPROMISOS PENDIENTES DE APROBACIÓN (Bandeja de entrada) --}}
@if($compromisosPendientes->count())
<div class="card fade-up" style="margin-bottom:20px;border:1px solid rgba(247,187,67,0.35);">
    <div class="card-header" style="background:rgba(247,187,67,0.07);">
        <div class="card-title" style="color:var(--accent-warn);">
            <i class="fa-solid fa-clock" style="margin-right:8px;"></i>
            Compromisos pendientes de aprobación ({{ $compromisosPendientes->count() }})
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        <table class="table" style="margin:0;">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Fecha</th>
                    <th>Beneficiario</th>
                    <th>Partida</th>
                    <th style="text-align:right;">Monto</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($compromisosPendientes as $cp)
                <tr>
                    <td><code style="color:var(--accent);font-weight:700;">{{ $cp->numero }}</code></td>
                    <td style="font-size:12px;color:var(--text-secondary);">{{ $cp->fecha_compromiso?->format('d/m/Y') ?? '—' }}</td>
                    <td style="font-size:13px;font-weight:600;">{{ $cp->beneficiario }}</td>
                    <td><code style="font-size:11px;color:var(--text-secondary);">{{ $cp->partida?->codigo ?? '—' }}</code></td>
                    <td style="text-align:right;font-weight:700;font-family:monospace;font-size:14px;color:var(--accent-3);">
                        Bs. {{ number_format((float)$cp->monto, 2) }}
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;">
                            <a href="{{ route('presupuesto.compromisos.show', $cp) }}" class="btn btn-sm btn-outline" title="Ver">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            @can('compromisos.aprobar')
                            <form method="POST" action="{{ route('presupuesto.compromisos.aprobar', $cp) }}" style="margin:0;"
                                  onsubmit="return confirm('¿Aprobar compromiso? Esto creará automáticamente una Causación en borrador.');">
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


<div class="card fade-up" style="margin-bottom:18px;">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:1;min-width:200px;">
                <label class="form-label">Buscar</label>
                <input type="text" name="search" class="form-control" placeholder="N°, beneficiario..." value="{{ request('search') }}">
            </div>
            <div style="min-width:140px;">
                <label class="form-label">Ejercicio</label>
                <select name="ejercicio" class="form-control">
                    <option value="">Todos</option>
                    @foreach($ejercicios as $e)<option value="{{ $e->id }}" {{ request('ejercicio')==$e->id?'selected':'' }}>{{ $e->anio }}</option>@endforeach
                </select>
            </div>
            <div style="min-width:130px;">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-control">
                    <option value="">Todos</option>
                    <option value="borrador" {{ request('estado')==='borrador'?'selected':'' }}>Borrador</option>
                    <option value="aprobado" {{ request('estado')==='aprobado'?'selected':'' }}>Aprobado</option>
                    <option value="causado"  {{ request('estado')==='causado'?'selected':'' }}>Causado</option>
                    <option value="anulado"  {{ request('estado')==='anulado'?'selected':'' }}>Anulado</option>
                </select>
            </div>
            <div style="display:flex;gap:6px;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Filtrar</button>
                <a href="{{ route('presupuesto.compromisos.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="card fade-up" style="animation-delay:.05s;">
    <div class="table-wrap">
        <table>
            <thead><tr>
                <th>N° Compromiso</th><th>Fecha</th><th>Beneficiario</th>
                <th>Partida</th><th style="text-align:right;">Monto</th>
                <th>Estado</th><th style="text-align:right;">Acciones</th>
            </tr></thead>
            <tbody>
            @forelse($q as $c)
            <tr style="{{ $c->estado === 'anulado' ? 'opacity:.6;' : '' }}">
                <td><code style="background:rgba(79,142,247,0.1);color:var(--accent);padding:3px 8px;border-radius:5px;font-size:12px;font-weight:600;">{{ $c->numero }}</code></td>
                <td style="font-size:12px;color:var(--text-secondary);">{{ $c->fecha_compromiso->format('d/m/Y') }}</td>
                <td><div style="font-weight:600;font-size:13px;">{{ $c->beneficiario }}</div></td>
                <td><code style="font-size:11px;color:var(--text-secondary);">{{ $c->partida?->codigo ?? '—' }}</code></td>
                <td style="text-align:right;font-weight:700;font-family:monospace;">{{ number_format($c->monto,2) }}</td>
                <td><span class="badge {{ $c->getEstadoBadgeClass() }}">{{ ucfirst($c->estado) }}</span></td>
                <td style="text-align:right;">
                    <div style="display:flex;gap:6px;justify-content:flex-end;align-items:center;">
                        <a href="{{ route('presupuesto.compromisos.show', $c) }}" class="btn btn-outline btn-sm" title="Ver detalle"><i class="fa-solid fa-eye"></i></a>
                        @if(!$c->esBorrador() && !$c->esAnulado())
                        <a href="{{ route('pdf.compromisos.pdf', $c) }}" target="_blank" class="btn btn-outline btn-sm" style="color:var(--accent-danger);border-color:rgba(229,57,53,0.3)" title="Exportar PDF">
                            <i class="fa-solid fa-file-pdf"></i>
                        </a>
                        @endif
                        @if($c->esBorrador())
                            @can('compromisos.crear')
                            <a href="{{ route('presupuesto.compromisos.edit', $c) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen-to-square"></i></a>
                            @endcan
                            @can('compromisos.aprobar')
                            <form method="POST" action="{{ route('presupuesto.compromisos.aprobar', $c) }}" style="margin:0;"
                                  onsubmit="return confirm('¿Aprobar compromiso?');">
                                @csrf
                                <button type="submit" class="btn btn-sm" style="background:rgba(34,211,166,0.15);border:1px solid rgba(34,211,166,.35);color:var(--accent-3);">
                                    <i class="fa-solid fa-check"></i> Aprobar
                                </button>
                            </form>
                            @endcan
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">📝</div><div class="empty-title">Sin compromisos</div></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Componente estándar de paginación --}}
    <x-pagination :paginator="$q" />
</div>

@endsection
