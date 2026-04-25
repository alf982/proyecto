@extends('layouts.app')
@section('title', 'Órdenes de Pago')
@section('breadcrumb')
    <span>Ordenamiento de Pago</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Órdenes de Pago</span>
@endsection
@section('content')
<div class="page-header fade-up">
    <h1 class="page-title">Órdenes de Pago</h1>
    <p class="page-subtitle">Flujo de autorización de pagos institucionales</p>
</div>

<div class="card fade-up">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-file-circle-check" style="color:var(--accent);margin-right:8px;"></i>Listado</div>
        <div style="display:flex;gap:8px;align-items:center;">
            <form method="GET" style="display:flex;gap:8px;">
                <input name="search" value="{{ request('search') }}" placeholder="Buscar número o concepto…" class="form-control" style="width:220px;padding:7px 12px;font-size:12px;">
                <select name="estado" class="form-control" style="width:140px;padding:7px 12px;font-size:12px;" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    @foreach(['borrador','revisada','aprobada','enviada','pagada','anulada'] as $e)
                        <option value="{{ $e }}" {{ request('estado')==$e?'selected':'' }}>{{ ucfirst($e) }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('tesoreria.ordenes.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nueva Orden</a>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr>
                <th>Número</th><th>Concepto</th><th>Unidad</th><th>Beneficiario</th><th>Monto</th><th>Tipo Pago</th><th>Estado</th><th>Fecha</th><th></th>
            </tr></thead>
            <tbody>
            @forelse($ordenes as $o)
            <tr>
                <td><code style="color:var(--accent);font-size:12px;">{{ $o->numero }}</code></td>
                <td style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $o->concepto }}</td>
                <td style="font-size:12px;">{{ $o->unidadEjecutora->nombre ?? '—' }}</td>
                <td style="font-size:12px;">{{ $o->beneficiario->razon_social ?? '—' }}</td>
                <td style="font-weight:700;color:var(--accent-3);">Bs. {{ number_format($o->monto_total,2) }}</td>
                <td><span class="badge badge-blue">{{ ucfirst($o->tipo_pago) }}</span></td>
                <td><span class="badge {{ $o->estadoBadge() }}">{{ ucfirst($o->estado) }}</span></td>
                <td style="font-size:12px;color:var(--text-secondary);">{{ $o->created_at->format('d/m/Y') }}</td>
                <td><a href="{{ route('tesoreria.ordenes.show', $o) }}" class="btn btn-outline btn-sm">Ver</a></td>
            </tr>
            @empty
            <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">📄</div><div class="empty-title">Sin órdenes de pago</div></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($ordenes->hasPages())<div class="pagination">{{ $ordenes->links() }}</div>@endif
</div>
@endsection
