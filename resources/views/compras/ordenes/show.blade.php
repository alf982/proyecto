@extends('layouts.app')
@section('title','OC '.$orden->numero)
@section('content')
<div class="page-header">
    <div><h1 class="page-title">{{ $orden->numero }}</h1><p class="page-subtitle">{{ $orden->concepto }}</p></div>
    <div style="display:flex;gap:.75rem;align-items:center">
        <span class="badge {{ $orden->getEstadoBadge() }}">{{ $orden->getEstadoLabel() }}</span>
        @if($orden->estaActiva())
        <a href="{{ route('compras.recepciones.create',['orden'=>$orden->id]) }}" class="btn btn-primary" style="font-size:.85rem">📦 Registrar Recepción</a>
        @endif
        <a href="{{ route('pdf.orden-compra', $orden) }}" target="_blank" class="btn btn-sm"
           style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);color:#ef4444;font-size:.85rem">
            <i class="fa-solid fa-file-pdf"></i> Exportar PDF
        </a>
        <a href="{{ route('compras.ordenes.index') }}" class="btn btn-ghost">← Volver</a>
    </div>
</div>
@include('components.alert')
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem">
    <div>
        <div class="card" style="margin-bottom:1rem">
            <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--border)"><h3 style="font-size:.9rem">Renglones</h3></div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Artículo/Descripción</th><th>U/M</th><th style="text-align:right">Cant.</th><th style="text-align:right">P.Unit.</th><th style="text-align:right">Subtotal</th><th style="text-align:right">Recibido</th></tr></thead>
                    <tbody>
                        @foreach($orden->detalles as $d)
                        <tr>
                            <td>
                                @if($d->articulo)<code style="font-size:.75rem">{{ $d->articulo->codigo }}</code><br>@endif
                                <span style="font-size:.85rem">{{ $d->descripcion }}</span>
                            </td>
                            <td style="font-size:.8rem;color:var(--text-muted)">{{ $d->unidad_medida }}</td>
                            <td style="text-align:right;font-family:monospace">{{ number_format($d->cantidad,2) }}</td>
                            <td style="text-align:right;font-family:monospace;font-size:.85rem">Bs. {{ number_format($d->precio_unitario,2) }}</td>
                            <td style="text-align:right;font-family:monospace">Bs. {{ number_format($d->subtotal,2) }}</td>
                            <td style="text-align:right;font-family:monospace;color:{{ $d->cantidad_recibida>=$d->cantidad?'var(--success)':'var(--primary)' }}">{{ number_format($d->cantidad_recibida,2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td colspan="4" style="text-align:right;padding:.5rem;color:var(--text-muted);">Subtotal:</td><td style="text-align:right;padding:.5rem;font-family:monospace">Bs. {{ number_format($orden->subtotal,2) }}</td><td></td></tr>
                        <tr style="font-weight:700;border-top:2px solid var(--border)"><td colspan="4" style="text-align:right;padding:.75rem;color:var(--text-muted)">TOTAL:</td><td style="text-align:right;padding:.75rem;font-family:monospace;color:var(--primary)">Bs. {{ number_format($orden->total,2) }}</td><td></td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @if($orden->recepciones->count())
        <div class="card">
            <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--border)"><h3 style="font-size:.9rem">Recepciones</h3></div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>N°</th><th>Fecha</th><th>Recibido Por</th><th style="text-align:right">Total</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                        @foreach($orden->recepciones as $rec)
                        <tr>
                            <td><a href="{{ route('compras.recepciones.show',$rec) }}" style="color:var(--primary)">{{ $rec->numero }}</a></td>
                            <td style="font-size:.85rem">{{ $rec->fecha_recepcion->format('d/m/Y') }}</td>
                            <td style="font-size:.85rem">{{ $rec->recibido_por }}</td>
                            <td style="text-align:right;font-family:monospace">Bs. {{ number_format($rec->total_recibido,2) }}</td>
                            <td><span class="badge {{ $rec->getEstadoBadge() }}" style="font-size:.7rem">{{ ucfirst($rec->estado) }}</span></td>
                            <td><a href="{{ route('compras.recepciones.show',$rec) }}" class="btn-icon">👁</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
    <div style="display:grid;gap:1rem">
        <div class="card" style="padding:1.2rem">
            <h4 style="font-size:.8rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:.75rem">Información</h4>
            <dl style="display:grid;grid-template-columns:110px 1fr;gap:.4rem;font-size:.85rem">
                <dt style="color:var(--text-muted)">N°</dt><dd><code>{{ $orden->numero }}</code></dd>
                <dt style="color:var(--text-muted)">Fecha</dt><dd>{{ $orden->fecha_emision->format('d/m/Y') }}</dd>
                <dt style="color:var(--text-muted)">Proveedor</dt><dd>{{ $orden->proveedor_nombre ?? $orden->beneficiario?->nombre ?? '—' }}</dd>
                <dt style="color:var(--text-muted)">RIF</dt><dd>{{ $orden->proveedor_rif ?? '—' }}</dd>
                <dt style="color:var(--text-muted)">Modalidad</dt><dd>{{ ucfirst(str_replace('_',' ',$orden->modalidad)) }}</dd>
                <dt style="color:var(--text-muted)">Creado por</dt><dd>{{ $orden->creadoPor?->name }}</dd>
                @if($orden->solicitud)<dt style="color:var(--text-muted)">Solicitud</dt><dd><a href="{{ route('compras.solicitudes.show',$orden->solicitud) }}" style="color:var(--primary)">{{ $orden->solicitud->numero }}</a></dd>@endif
            </dl>
        </div>

        {{-- Partida Presupuestaria --}}
        @if($orden->partida)
        <div class="card" style="padding:1.2rem;border:1px solid rgba(79,142,247,0.25);">
            <h4 style="font-size:.8rem;color:var(--accent);text-transform:uppercase;margin-bottom:.75rem">
                <i class="fa-solid fa-chart-pie" style="margin-right:5px;"></i>Partida Presupuestaria
            </h4>
            <div>
                <code style="font-size:.85rem;font-weight:700;color:var(--accent);">{{ $orden->partida->codigo }}</code>
                <div style="font-size:.8rem;color:var(--text-muted);margin-top:3px;">{{ $orden->partida->descripcion }}</div>
            </div>
            <div style="margin-top:12px;padding-top:10px;border-top:1px solid var(--border);">
                <div style="font-size:.75rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:3px;">Saldo actual</div>
                @php $saldo = (float) $orden->partida->saldo_actual; @endphp
                <div style="font-size:1.1rem;font-weight:800;font-family:monospace;color:{{ $saldo < 0 ? 'var(--accent-danger)' : 'var(--accent-3)' }};">
                    Bs. {{ number_format($saldo, 2) }}
                </div>
            </div>
            @if(!in_array($orden->estado, ['completada','anulada']))
            <div style="margin-top:8px;font-size:.75rem;color:var(--text-muted);">
                <i class="fa-solid fa-lock" style="margin-right:3px;"></i>
                Bs. {{ number_format((float)$orden->total, 2) }} reservados por esta OC
            </div>
            @endif
        </div>
        @else
        <div style="padding:10px 14px;background:rgba(247,187,67,0.07);border:1px solid rgba(247,187,67,0.25);border-radius:8px;font-size:.8rem;color:var(--text-muted);">
            <i class="fa-solid fa-triangle-exclamation" style="color:var(--accent-warn);margin-right:5px;"></i>
            Sin afectación presupuestaria asignada
        </div>
        @endif
        @if($orden->estaActiva())
        <div class="card" style="padding:1.2rem">
            <h4 style="font-size:.8rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:.75rem">Cambiar Estado</h4>
            @foreach(['confirmada'=>'🤝 Confirmar','en_transito'=>'🚚 Marcar En Tránsito','anulada'=>'❌ Anular'] as $est => $lbl)
            @if($est !== $orden->estado)
            <form method="POST" action="{{ route('compras.ordenes.cambiar-estado',$orden) }}" style="margin-bottom:.5rem"
                onsubmit="return '{{ $est }}'==='anulada'?confirm('¿Anular esta orden?'):true">
                @csrf
                <input type="hidden" name="estado" value="{{ $est }}">
                @if($est==='anulada')<input type="text" name="motivo_anulacion" class="form-control" style="font-size:.8rem;margin-bottom:.35rem" placeholder="Motivo de anulación *" required>@endif
                <button type="submit" class="btn btn-ghost" style="width:100%;font-size:.8rem">{{ $lbl }}</button>
            </form>
            @endif
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
