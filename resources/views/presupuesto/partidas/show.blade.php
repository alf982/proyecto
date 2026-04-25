@extends('layouts.app')
@section('title', 'Detalle de Partida Presupuestaria')
@section('breadcrumb')
    <a href="{{ route('presupuesto.partidas.index') }}" style="color:var(--text-secondary);text-decoration:none;">Partidas</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">{{ $partida->codigo }}</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;gap:12px;">
    <a href="{{ route('presupuesto.partidas.index') }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <h1 class="page-title">{{ $partida->codigo }}</h1>
        <p class="page-subtitle">{{ $partida->descripcion }}</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;" class="fade-up">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="fa-solid fa-coins" style="color:var(--accent);margin-right:8px;"></i>Créditos en esta Partida</div></div>
        @php $creditos = $partida->creditos ?? collect(); @endphp
        @if($creditos->isEmpty())
            <div class="empty-state" style="padding:30px;"><div class="empty-icon">📋</div><div class="empty-title">Sin créditos asignados</div><div class="empty-desc">Esta partida no tiene créditos en ningún ejercicio.</div></div>
        @else
        <div class="table-wrap">
            <table>
                <thead><tr><th>Ejercicio</th><th>Unidad</th><th>Aprobado</th><th>Comprometido</th><th>Disponible</th></tr></thead>
                <tbody>
                @foreach($creditos as $cr)
                <tr>
                    <td style="font-size:13px;font-weight:600;">{{ $cr->ejercicioFiscal?->anio }}</td>
                    <td style="font-size:12px;">{{ Str::limit($cr->unidadEjecutora?->nombre, 30) }}</td>
                    <td style="text-align:right;font-size:13px;">Bs. {{ number_format($cr->monto_aprobado, 2) }}</td>
                    <td style="text-align:right;font-size:13px;color:var(--accent-warn);">Bs. {{ number_format($cr->monto_comprometido, 2) }}</td>
                    <td style="text-align:right;font-size:13px;">
                        @php $d = $cr->monto_vigente - $cr->monto_comprometido; @endphp
                        <span style="color:{{ $d >= 0 ? 'var(--accent-3)' : 'var(--accent-danger)' }};">Bs. {{ number_format($d, 2) }}</span>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="card fade-up" style="height:fit-content;">
        <div class="card-header"><div class="card-title">Información</div></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:14px;padding:20px;">
            <div><div class="form-label">Código</div>
                <code style="font-size:15px;font-weight:700;background:rgba(79,142,247,0.1);color:var(--accent);padding:6px 10px;border-radius:8px;display:inline-block;">{{ $partida->codigo }}</code>
            </div>
            <div><div class="form-label">Descripción</div><div style="font-size:13px;">{{ $partida->descripcion }}</div></div>
            <div><div class="form-label">Tipo</div>
                <span class="badge {{ $partida->tipo === 'gasto' ? 'badge-danger' : 'badge-active' }}">{{ ucfirst($partida->tipo) }}</span>
            </div>
            <div><div class="form-label">Genérica / Específica</div>
                <div style="font-size:12px;color:var(--text-secondary);">{{ $partida->generica }}.{{ $partida->especifica }}.{{ $partida->subespecifica }}</div>
            </div>
            <div><div class="form-label">Estado</div>
                <span class="badge {{ $partida->activo ? 'badge-active' : 'badge-danger' }}">{{ $partida->activo ? 'Activa' : 'Inactiva' }}</span>
            </div>
            <div>
                <div class="form-label"><i class="fa-solid fa-building-columns" style="margin-right:5px;color:var(--accent);"></i>Cuenta Bancaria</div>
                @if($partida->cuentaBancaria)
                    <div style="font-size:13px;font-weight:600;">{{ $partida->cuentaBancaria->nombre }}</div>
                    <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">
                        {{ $partida->cuentaBancaria->banco }}<br>
                        <code style="font-size:10px;">{{ $partida->cuentaBancaria->numero_cuenta }}</code>
                    </div>
                @else
                    <span style="color:var(--text-secondary);font-size:12px;font-style:italic;">Sin cuenta asignada</span>
                @endif
            </div>
            <div class="nav-divider"></div>
            @can('partidas.editar')
            <a href="{{ route('presupuesto.partidas.edit', $partida) }}" class="btn btn-primary" style="width:100%;text-align:center;"><i class="fa-solid fa-pen-to-square"></i> Editar</a>
            @endcan
        </div>
    </div>
</div>
@endsection
