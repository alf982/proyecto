@extends('layouts.app')
@section('title','Solicitud '.$solicitud->numero)
@section('breadcrumb')
    <span>Compras</span><span class="breadcrumb-sep">›</span>
    <a href="{{ route('compras.solicitudes.index') }}">Solicitudes</a>
    <span class="breadcrumb-sep">›</span><span class="current">{{ $solicitud->numero }}</span>
@endsection
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $solicitud->numero }}</h1>
        <p class="page-subtitle">{{ $solicitud->motivo }}</p>
    </div>
    <div style="display:flex;gap:.75rem;align-items:center">
        <span class="badge {{ $solicitud->getEstadoBadge() }}">{{ ucfirst($solicitud->estado) }}</span>
        @can('compras.solicitudes.aprobar')
        @if($solicitud->estado === 'enviada')
        <form method="POST" action="{{ route('compras.solicitudes.aprobar', $solicitud) }}">
            @csrf
            <button type="submit" class="btn btn-primary" style="font-size:.85rem" id="btn-aprobar-sol">✅ Aprobar</button>
        </form>
        @endif
        @endcan
        <a href="{{ route('compras.solicitudes.index') }}" class="btn btn-ghost">← Volver</a>
    </div>
</div>
@include('components.alert')

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem">

    {{-- Artículos --}}
    <div class="card">
        <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--border)">
            <h3 style="font-size:.9rem">Artículos a Abastecer</h3>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Artículo / Descripción</th>
                        <th style="text-align:center">Stock Actual</th>
                        <th style="text-align:center">Stock Mínimo</th>
                        <th style="text-align:center">U/M</th>
                        <th style="text-align:right">Cant. Solicitada</th>
                        <th style="text-align:right">P. Estimado</th>
                        <th style="text-align:right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($solicitud->detalles as $d)
                    @php $art = $d->articulo; @endphp
                    <tr>
                        <td style="color:var(--text-muted);font-size:.8rem">{{ $d->orden }}</td>
                        <td>
                            @if($art)<div style="font-size:.73rem;color:var(--text-muted)"><code>{{ $art->codigo }}</code> · {{ $art->almacen?->nombre }}</div>@endif
                            <div style="font-size:.88rem">{{ $d->descripcion }}</div>
                            @if($d->especificaciones)<div style="font-size:.75rem;color:var(--text-muted);font-style:italic">{{ $d->especificaciones }}</div>@endif
                        </td>
                        <td style="text-align:center">
                            @if($art)
                                @if($art->stock_actual <= 0)
                                    <span class="badge badge-danger" style="font-size:.7rem">0 {{ $art->unidad_medida }}</span>
                                @elseif($art->getBajoStock())
                                    <span class="badge badge-warn" style="font-size:.7rem">{{ number_format($art->stock_actual,1) }} {{ $art->unidad_medida }}</span>
                                @else
                                    <span class="badge badge-active" style="font-size:.7rem">{{ number_format($art->stock_actual,1) }} {{ $art->unidad_medida }}</span>
                                @endif
                            @else <span style="color:var(--text-muted)">—</span>
                            @endif
                        </td>
                        <td style="text-align:center;font-size:.82rem;color:var(--text-muted)">{{ $art ? number_format($art->stock_minimo,1) : '—' }}</td>
                        <td style="text-align:center;font-size:.82rem;color:var(--text-muted)">{{ $d->unidad_medida }}</td>
                        <td style="text-align:right;font-family:monospace">{{ number_format($d->cantidad,2) }}</td>
                        <td style="text-align:right;font-family:monospace;color:var(--text-muted);font-size:.85rem">Bs. {{ number_format($d->precio_estimado,2) }}</td>
                        <td style="text-align:right;font-family:monospace">Bs. {{ number_format($d->getSubtotal(),2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid var(--border);font-weight:700">
                        <td colspan="7" style="padding:.75rem;text-align:right;color:var(--text-muted)">Total Estimado:</td>
                        <td style="padding:.75rem;text-align:right;font-family:monospace;color:var(--primary)">Bs. {{ number_format($solicitud->getTotalEstimado(),2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Panel lateral --}}
    <div style="display:grid;gap:1rem">

        <div class="card" style="padding:1.2rem">
            <h4 style="font-size:.8rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:.75rem">Información</h4>
            <dl style="display:grid;grid-template-columns:110px 1fr;gap:.4rem;font-size:.85rem">
                <dt style="color:var(--text-muted)">N°</dt><dd><code>{{ $solicitud->numero }}</code></dd>
                <dt style="color:var(--text-muted)">Generada por</dt><dd>{{ $solicitud->solicitadoPor?->name }}</dd>
                <dt style="color:var(--text-muted)">Prioridad</dt>
                <dd><span class="badge {{ $solicitud->getPrioridadBadge() }}" style="font-size:.7rem">{{ ucfirst($solicitud->prioridad) }}</span></dd>
                <dt style="color:var(--text-muted)">F. Requerida</dt><dd>{{ $solicitud->fecha_requerida?->format('d/m/Y') ?? '—' }}</dd>
                <dt style="color:var(--text-muted)">Registrado</dt><dd>{{ $solicitud->created_at->format('d/m/Y H:i') }}</dd>
                @if($solicitud->aprobadoPor)
                <dt style="color:var(--text-muted)">Aprobado</dt><dd>{{ $solicitud->aprobadoPor->name }}</dd>
                <dt style="color:var(--text-muted)">F. Aprobación</dt><dd>{{ $solicitud->fecha_aprobacion?->format('d/m/Y H:i') }}</dd>
                @endif
                @if($solicitud->observaciones)
                <dt style="color:var(--text-muted)">Observaciones</dt><dd>{{ $solicitud->observaciones }}</dd>
                @endif
                @if($solicitud->motivo_rechazo)
                <dt style="color:var(--danger)">Rechazo</dt>
                <dd style="color:var(--danger)">{{ $solicitud->motivo_rechazo }}</dd>
                @endif
            </dl>
        </div>

        @if($solicitud->estado === 'aprobada')
        <div class="card" style="padding:1.2rem;border-color:rgba(34,197,94,.3)">
            <h4 style="font-size:.8rem;color:var(--success,#22c55e);text-transform:uppercase;margin-bottom:.5rem">✅ Aprobada</h4>
            <p style="font-size:.82rem;color:var(--text-muted);line-height:1.6">
                Esta solicitud está aprobada. Proceda a crear una <strong>Orden de Compra</strong> referenciando este número.
            </p>
            <a href="{{ route('compras.ordenes.create') }}" class="btn btn-primary" style="width:100%;margin-top:.75rem;font-size:.82rem">
                📦 Crear Orden de Compra
            </a>
        </div>
        @endif

        @can('compras.solicitudes.aprobar')
        @if($solicitud->estado === 'enviada')
        <div class="card" style="padding:1.2rem;border-color:rgba(239,68,68,.3)">
            <h4 style="font-size:.8rem;color:var(--danger);text-transform:uppercase;margin-bottom:.75rem">Rechazar</h4>
            <form method="POST" action="{{ route('compras.solicitudes.rechazar', $solicitud) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" style="font-size:.8rem">Motivo *</label>
                    <textarea name="motivo_rechazo" class="form-control" rows="2" required minlength="10" placeholder="Indique el motivo..."></textarea>
                </div>
                <button type="submit" class="btn" style="width:100%;background:rgba(239,68,68,.12);color:var(--danger);border:1px solid rgba(239,68,68,.3);font-size:.8rem">
                    Rechazar Solicitud
                </button>
            </form>
        </div>
        @endif
        @endcan

    </div>
</div>
@endsection
