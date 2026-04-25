@extends('layouts.app')
@section('title','Compromisos')
@section('breadcrumb')
    <span>Presupuesto</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Compromisos</span>
@endsection
@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Compromisos Presupuestarios</h1>
        <p class="page-subtitle">Reservas de crédito previas a la causación</p>
    </div>
    <a href="{{ route('presupuesto.compromisos.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nuevo Compromiso</a>
</div>

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
            <tr>
                <td><code style="background:rgba(79,142,247,0.1);color:var(--accent);padding:3px 8px;border-radius:5px;font-size:12px;font-weight:600;">{{ $c->numero }}</code></td>
                <td style="font-size:12px;color:var(--text-secondary);">{{ $c->fecha_compromiso->format('d/m/Y') }}</td>
                <td><div style="font-weight:600;font-size:13px;">{{ $c->beneficiario }}</div></td>
                <td><code style="font-size:11px;color:var(--text-secondary);">{{ $c->partida?->codigo ?? '—' }}</code></td>
                <td style="text-align:right;font-weight:700;font-family:monospace;">{{ number_format($c->monto,2) }}</td>
                <td><span class="badge {{ $c->getEstadoBadgeClass() }}">{{ ucfirst($c->estado) }}</span></td>
                <td style="text-align:right;">
                    <div style="display:flex;gap:6px;justify-content:flex-end;align-items:center;">
                        <a href="{{ route('presupuesto.compromisos.show', $c) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-eye"></i></a>
                        @if($c->esBorrador())
                            <a href="{{ route('presupuesto.compromisos.edit', $c) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen-to-square"></i></a>
                            <form method="POST" action="{{ route('presupuesto.compromisos.aprobar', $c) }}" style="margin:0;">
                                @csrf
                                <button type="submit" class="btn btn-sm" style="background:rgba(34,211,166,0.15);border:1px solid rgba(34,211,166,.35);color:var(--accent-3);">
                                    <i class="fa-solid fa-check"></i> Aprobar
                                </button>
                            </form>
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
</div>

@endsection
