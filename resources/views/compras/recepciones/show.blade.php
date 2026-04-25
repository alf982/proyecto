@extends('layouts.app')
@section('title','Recepción '.$recepcion->numero)
@section('content')
<div class="page-header">
    <div><h1 class="page-title">{{ $recepcion->numero }}</h1><p class="page-subtitle">OC: <code>{{ $recepcion->orden?->numero }}</code></p></div>
    <div style="display:flex;gap:.75rem;align-items:center">
        <span class="badge {{ $recepcion->getEstadoBadge() }}">{{ ucfirst($recepcion->estado) }}</span>
        <a href="{{ route('pdf.recepcion-bienes', $recepcion) }}" target="_blank" class="btn btn-sm"
           style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);color:#ef4444;font-size:.85rem">
            <i class="fa-solid fa-file-pdf"></i> Exportar PDF
        </a>
        <a href="{{ route('compras.recepciones.index') }}" class="btn btn-ghost">← Volver</a>
    </div>
</div>
@include('components.alert')
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem">
    <div class="card">
        <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--border)"><h3 style="font-size:.9rem">Artículos Recibidos</h3></div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Artículo</th><th>Descripción</th><th style="text-align:right">Recibido</th><th style="text-align:right">P.Unit.</th><th style="text-align:right">Subtotal</th><th>Condición</th></tr></thead>
                <tbody>
                    @foreach($recepcion->detalles as $d)
                    <tr>
                        <td>@if($d->articulo)<code style="font-size:.75rem">{{ $d->articulo->codigo }}</code>@else —@endif</td>
                        <td style="font-size:.85rem">{{ $d->ordenDetalle?->descripcion }}</td>
                        <td style="text-align:right;font-family:monospace">{{ number_format($d->cantidad_recibida,2) }}</td>
                        <td style="text-align:right;font-family:monospace;font-size:.85rem">Bs. {{ number_format($d->precio_unitario,2) }}</td>
                        <td style="text-align:right;font-family:monospace">Bs. {{ number_format($d->getSubtotal(),2) }}</td>
                        <td><span class="badge {{ $d->condicion=='bueno'?'badge-active':($d->condicion=='incompleto'?'badge-warn':'badge-danger') }}" style="font-size:.7rem">{{ ucfirst($d->condicion) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid var(--border);font-weight:700">
                        <td colspan="4" style="padding:.75rem;text-align:right;color:var(--text-muted)">TOTAL RECIBIDO:</td>
                        <td style="padding:.75rem;text-align:right;font-family:monospace;color:var(--success)">Bs. {{ number_format($recepcion->total_recibido,2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="card" style="padding:1.2rem">
        <h4 style="font-size:.8rem;color:var(--text-muted);text-transform:uppercase;margin-bottom:.75rem">Información</h4>
        <dl style="display:grid;grid-template-columns:120px 1fr;gap:.4rem;font-size:.85rem">
            <dt style="color:var(--text-muted)">N° Recepción</dt><dd><code>{{ $recepcion->numero }}</code></dd>
            <dt style="color:var(--text-muted)">Orden de Compra</dt><dd><a href="{{ route('compras.ordenes.show',$recepcion->orden) }}" style="color:var(--primary)">{{ $recepcion->orden?->numero }}</a></dd>
            <dt style="color:var(--text-muted)">Fecha</dt><dd>{{ $recepcion->fecha_recepcion->format('d/m/Y') }}</dd>
            <dt style="color:var(--text-muted)">Recibido Por</dt><dd>{{ $recepcion->recibido_por }}</dd>
            <dt style="color:var(--text-muted)">Entregado Por</dt><dd>{{ $recepcion->entregado_por ?? '—' }}</dd>
            <dt style="color:var(--text-muted)">N° Guía</dt><dd>{{ $recepcion->numero_guia ?? '—' }}</dd>
            <dt style="color:var(--text-muted)">N° Factura</dt><dd>{{ $recepcion->numero_factura ?? '—' }}</dd>
            @if($recepcion->observaciones)<dt style="color:var(--text-muted)">Observ.</dt><dd>{{ $recepcion->observaciones }}</dd>@endif
        </dl>
    </div>
</div>
@endsection
