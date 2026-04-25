@extends('layouts.app')
@section('title','Solicitud '.$solicitud->numero)
@section('breadcrumb')
    <span>Almacén</span><span class="breadcrumb-sep">›</span>
    <a href="{{ route('almacen.solicitudes.index') }}">Solicitudes de Artículos</a>
    <span class="breadcrumb-sep">›</span><span class="current">{{ $solicitud->numero }}</span>
@endsection
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $solicitud->numero }}</h1>
        <p class="page-subtitle">{{ $solicitud->motivo }}</p>
    </div>
    <div style="display:flex;gap:.75rem;align-items:center">
        <span class="badge {{ $solicitud->getEstadoBadge() }}">{{ $solicitud->getEstadoLabel() }}</span>
        <span class="badge {{ $solicitud->getPrioridadBadge() }}" style="font-size:.7rem">{{ ucfirst($solicitud->prioridad) }}</span>

        {{-- Aprobar (solo cuando enviada) --}}
        @can('almacen.solicitudes.aprobar')
        @if($solicitud->esEnviada())
        <form method="POST" action="{{ route('almacen.solicitudes.aprobar', $solicitud) }}"
              onsubmit="return confirm('¿Aprobar esta solicitud? El stock se descontará al momento de registrar la entrega.')">
            @csrf
            <button type="submit" class="btn btn-primary" style="font-size:.85rem" id="btn-aprobar">✅ Aprobar</button>
        </form>
        @endif
        @endcan

        <a href="{{ route('almacen.solicitudes.index') }}" class="btn btn-ghost">← Volver</a>
    </div>
</div>
@include('components.alert')

{{-- Flujo de estados visual --}}
<div style="display:flex;gap:0;margin-bottom:1.5rem;align-items:center">
    @foreach(['enviada'=>'📨 Enviada','aprobada'=>'✅ Aprobada','entregada'=>'📦 Entregada'] as $est => $label)
    @php
        $activo = $solicitud->estado === $est;
        $pasado = match($solicitud->estado) {
            'aprobada'  => in_array($est, ['enviada']),
            'entregada' => in_array($est, ['enviada','aprobada']),
            default     => false,
        };
    @endphp
    <div style="flex:1;padding:.6rem 1rem;text-align:center;font-size:.8rem;font-weight:600;
        background:{{ $activo ? 'var(--primary)' : ($pasado ? 'rgba(79,142,247,.15)' : 'var(--card-bg)') }};
        color:{{ $activo ? '#fff' : ($pasado ? 'var(--primary)' : 'var(--text-muted)') }};
        border:1px solid {{ $activo ? 'var(--primary)' : 'var(--border)' }};
        border-radius:{{ $loop->first ? '8px 0 0 8px' : ($loop->last ? '0 8px 8px 0' : '0') }}">
        {{ $label }}
    </div>
    @if(!$loop->last)<div style="width:0;height:0;border-top:19px solid transparent;border-bottom:19px solid transparent;border-left:10px solid var(--border);z-index:1"></div>@endif
    @endforeach
    @if($solicitud->esRechazada())
    <div style="flex:.5;padding:.6rem 1rem;text-align:center;font-size:.8rem;font-weight:600;background:rgba(239,68,68,.1);color:#ef4444;border:1px solid rgba(239,68,68,.3);border-radius:0 8px 8px 0">
        ❌ Rechazada
    </div>
    @endif
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem">

    {{-- Panel izquierdo --}}
    <div style="display:grid;gap:1.5rem">

        {{-- Tabla de artículos solicitados --}}
        <div class="card">
            <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--border)">
                <h3 style="font-size:.9rem">Artículos Solicitados</h3>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Artículo</th>
                            <th>Almacén</th>
                            <th style="text-align:center">Stock Actual</th>
                            <th style="text-align:right">Cant. Solicitada</th>
                            <th style="text-align:right">Cant. Entregada</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($solicitud->detalles as $det)
                        @php $art = $det->articulo; @endphp
                        <tr>
                            <td style="color:var(--text-muted);font-size:.8rem">{{ $det->orden }}</td>
                            <td>
                                @if($art)<div style="font-size:.73rem;color:var(--text-muted)"><code>{{ $art->codigo }}</code></div>@endif
                                <div style="font-size:.9rem">{{ $art?->nombre ?? '[Artículo eliminado]' }}</div>
                            </td>
                            <td style="font-size:.82rem;color:var(--text-muted)">{{ $art?->almacen?->nombre ?? '—' }}</td>
                            <td style="text-align:center">
                                @if($art)
                                    @if($art->getBajoStock())
                                        <span class="badge badge-danger" style="font-size:.7rem">{{ number_format($art->stock_actual,2) }} {{ $art->unidad_medida }}</span>
                                    @elseif($art->stock_actual <= 0)
                                        <span class="badge badge-danger" style="font-size:.7rem">Sin stock</span>
                                    @else
                                        <span class="badge badge-active" style="font-size:.7rem">{{ number_format($art->stock_actual,2) }} {{ $art->unidad_medida }}</span>
                                    @endif
                                @else
                                    <span style="color:var(--text-muted)">—</span>
                                @endif
                            </td>
                            <td style="text-align:right;font-family:monospace">
                                {{ number_format($det->cantidad_solicitada,2) }}
                                <span style="font-size:.75rem;color:var(--text-muted)">{{ $art?->unidad_medida }}</span>
                            </td>
                            <td style="text-align:right">
                                @if($det->cantidad_despachada !== null)
                                    <span class="badge badge-active">{{ number_format($det->cantidad_despachada,2) }}</span>
                                @else
                                    <span style="color:var(--text-muted)">—</span>
                                @endif
                            </td>
                            <td style="font-size:.82rem;color:var(--text-muted)">{{ $det->observacion ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- FORMULARIO DE REGISTRO DE ENTREGA (solo cuando está aprobada) --}}
        @can('almacen.solicitudes.aprobar')
        @if($solicitud->esAprobada())
        <div class="card" style="border:2px solid rgba(139,92,246,.4)">
            <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--border);background:rgba(139,92,246,.06)">
                <h3 style="font-size:.9rem;color:#8b5cf6">📦 Registrar Entrega de Artículos</h3>
                <p style="font-size:.78rem;color:var(--text-muted);margin-top:.25rem">Complete los datos de la entrega física. Al guardar, el inventario será actualizado automáticamente.</p>
            </div>
            <div style="padding:1.5rem">
                <form method="POST" action="{{ route('almacen.solicitudes.registrar-entrega', $solicitud) }}" id="form-entrega">
                    @csrf

                    {{-- Datos de la entrega --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
                        <div class="form-group">
                            <label class="form-label">Fecha de Entrega *</label>
                            <input type="date" name="fecha_entrega" class="form-control"
                                   value="{{ old('fecha_entrega', date('Y-m-d')) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Recibido Por (Nombre de la Persona) *</label>
                            <input type="text" name="recibido_por" class="form-control"
                                   value="{{ old('recibido_por') }}"
                                   placeholder="Nombre de quien recibe en la {{ $solicitud->unidadEjecutora?->nombre }}"
                                   required>
                        </div>
                    </div>

                    {{-- Cantidades a entregar --}}
                    <div style="margin-bottom:1rem">
                        <label class="form-label" style="margin-bottom:.5rem">Cantidades a Entregar *</label>
                        <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:.75rem">
                            Puede entregar una cantidad diferente a la solicitada si el stock es insuficiente.
                        </p>
                        <table style="width:100%;border-collapse:collapse">
                            <thead>
                                <tr style="font-size:.75rem;text-transform:uppercase;color:var(--text-muted)">
                                    <th style="padding:.4rem .5rem;text-align:left">Artículo</th>
                                    <th style="padding:.4rem .5rem;text-align:right;width:100px">Solicitado</th>
                                    <th style="padding:.4rem .5rem;text-align:center;width:80px">Stock</th>
                                    <th style="padding:.4rem .5rem;text-align:right;width:130px">Cant. a Entregar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($solicitud->detalles as $det)
                                @php $art = $det->articulo; @endphp
                                <tr style="border-top:1px solid var(--border)">
                                    <td style="padding:.5rem">
                                        <input type="hidden" name="lineas[{{ $loop->index }}][detalle_id]" value="{{ $det->id }}">
                                        <span style="font-size:.85rem;font-weight:600">{{ $art?->nombre ?? '[Eliminado]' }}</span>
                                        @if($art)<span style="font-size:.75rem;color:var(--text-muted)"> · {{ $art->unidad_medida }}</span>@endif
                                    </td>
                                    <td style="padding:.5rem;text-align:right;font-family:monospace;font-size:.85rem">
                                        {{ number_format($det->cantidad_solicitada,2) }}
                                    </td>
                                    <td style="padding:.5rem;text-align:center">
                                        @if($art)
                                        <span style="font-size:.78rem;color:{{ $art->stock_actual >= $det->cantidad_solicitada ? 'var(--success)' : '#f59e0b' }};font-weight:600">
                                            {{ number_format($art->stock_actual,2) }}
                                        </span>
                                        @else
                                        <span style="font-size:.78rem;color:var(--danger)">N/D</span>
                                        @endif
                                    </td>
                                    <td style="padding:.5rem">
                                        <input type="number"
                                               name="lineas[{{ $loop->index }}][cantidad_entregada]"
                                               class="form-control"
                                               style="font-size:.85rem;text-align:right"
                                               value="{{ old("lineas.{$loop->index}.cantidad_entregada", $det->cantidad_solicitada) }}"
                                               min="0"
                                               max="{{ $art?->stock_actual ?? $det->cantidad_solicitada }}"
                                               step="0.01">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group" style="margin-bottom:1rem">
                        <label class="form-label">Observaciones de Entrega</label>
                        <textarea name="observaciones_entrega" class="form-control" rows="2"
                                  placeholder="Notas adicionales sobre la entrega (opcional)...">{{ old('observaciones_entrega') }}</textarea>
                    </div>

                    <div style="display:flex;justify-content:flex-end">
                        <button type="submit" class="btn btn-primary" id="btn-registrar-entrega"
                                onclick="return confirm('¿Confirmar registro de entrega? El stock será descontado inmediatamente.')">
                            Registrar entrega
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
        @endcan

        {{-- Resumen de la entrega (cuando ya fue entregada) --}}
        @if($solicitud->esEntregada())
        <div class="card" style="border:1px solid rgba(34,197,94,.3)">
            <div style="padding:1rem 1.5rem;border-bottom:1px solid rgba(34,197,94,.2);background:rgba(34,197,94,.05)">
                <h3 style="font-size:.9rem;color:var(--success,#22c55e)">✅ Entrega Registrada</h3>
            </div>
            <div style="padding:1.2rem">
                <dl style="display:grid;grid-template-columns:150px 1fr;gap:.5rem;font-size:.85rem">
                    <dt style="color:var(--text-muted)">Fecha de entrega</dt>
                    <dd><strong>{{ $solicitud->fecha_entrega?->format('d/m/Y') }}</strong></dd>
                    <dt style="color:var(--text-muted)">Recibido por</dt>
                    <dd><strong>{{ $solicitud->recibido_por }}</strong></dd>
                    <dt style="color:var(--text-muted)">Entregado por</dt>
                    <dd>{{ $solicitud->aprobadoPor?->name }}</dd>
                    @if($solicitud->observaciones_entrega)
                    <dt style="color:var(--text-muted)">Observaciones</dt>
                    <dd>{{ $solicitud->observaciones_entrega }}</dd>
                    @endif
                </dl>
            </div>
        </div>
        @endif

    </div>

    {{-- Panel lateral --}}
    <div style="display:grid;gap:1rem;align-content:start">

        {{-- Información de la solicitud --}}
        <div class="card" style="padding:1.2rem">
            <h4 style="font-size:.8rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:.75rem">Información</h4>
            <dl style="display:grid;grid-template-columns:110px 1fr;gap:.4rem;font-size:.85rem">
                <dt style="color:var(--text-muted)">N°</dt><dd><code>{{ $solicitud->numero }}</code></dd>
                <dt style="color:var(--text-muted)">Oficina</dt><dd>{{ $solicitud->unidadEjecutora?->nombre }}</dd>
                <dt style="color:var(--text-muted)">Prioridad</dt>
                <dd><span class="badge {{ $solicitud->getPrioridadBadge() }}" style="font-size:.7rem">{{ ucfirst($solicitud->prioridad) }}</span></dd>
                <dt style="color:var(--text-muted)">Solicitado</dt><dd>{{ $solicitud->solicitadoPor?->name }}</dd>
                <dt style="color:var(--text-muted)">F. Requerida</dt><dd>{{ $solicitud->fecha_requerida?->format('d/m/Y') ?? '—' }}</dd>
                <dt style="color:var(--text-muted)">Registrado</dt><dd>{{ $solicitud->created_at->format('d/m/Y H:i') }}</dd>
                @if($solicitud->aprobadoPor)
                <dt style="color:var(--text-muted)">Aprobado por</dt><dd>{{ $solicitud->aprobadoPor->name }}</dd>
                <dt style="color:var(--text-muted)">F. Aprobación</dt><dd>{{ $solicitud->fecha_aprobacion?->format('d/m/Y H:i') }}</dd>
                @endif
                @if($solicitud->observaciones)
                <dt style="color:var(--text-muted)">Observaciones</dt><dd>{{ $solicitud->observaciones }}</dd>
                @endif
                @if($solicitud->motivo_rechazo)
                <dt style="color:var(--danger)">Motivo rechazo</dt>
                <dd style="color:var(--danger)">{{ $solicitud->motivo_rechazo }}</dd>
                @endif
            </dl>
        </div>

        {{-- Panel rechazar / cancelar (cualquier estado antes de entregada) --}}
        @can('almacen.solicitudes.aprobar')
        @if($solicitud->puedeRechazarse())
        <div class="card" style="padding:1.2rem;border-color:rgba(239,68,68,.3)">
            <h4 style="font-size:.8rem;color:var(--danger);text-transform:uppercase;margin-bottom:.75rem">
                {{ $solicitud->esEnviada() ? 'Rechazar Solicitud' : 'Cancelar Solicitud Aprobada' }}
            </h4>
            @if(!$solicitud->esEnviada())
            <p style="font-size:.75rem;color:var(--text-muted);margin-bottom:.75rem">La solicitud está aprobada pero aún no fue entregada.</p>
            @endif
            <form method="POST" action="{{ route('almacen.solicitudes.rechazar', $solicitud) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" style="font-size:.8rem">Motivo *</label>
                    <textarea name="motivo_rechazo" class="form-control" rows="2" required minlength="10" placeholder="Indique el motivo..."></textarea>
                </div>
                <button type="submit" class="btn" id="btn-rechazar"
                    style="width:100%;background:rgba(239,68,68,.12);color:var(--danger);border:1px solid rgba(239,68,68,.3);font-size:.8rem">
                    {{ $solicitud->esEnviada() ? 'Rechazar' : 'Cancelar solicitud' }}
                </button>
            </form>
        </div>
        @endif
        @endcan

    </div>
</div>
@endsection

