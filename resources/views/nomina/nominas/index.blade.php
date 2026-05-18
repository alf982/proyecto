@extends('layouts.app')
@section('title', 'Nóminas')
@section('breadcrumb')
    <span>Nómina y Personal</span>
    <span class="breadcrumb-sep">›</span>
    <span class="current">Nóminas</span>
@endsection
@section('content')

{{-- 
  VISTA INDEX DE NÓMINAS
  Bandeja de entrada del ciclo salarial (Generación, Aprobación y Pago de nóminas).
--}}

<div class="page-header fade-up">
    <h1 class="page-title">Nóminas</h1>
    <p class="page-subtitle">Cálculo y aprobación de nóminas de personal</p>
</div>
<div class="card fade-up">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-money-check-dollar" style="color:var(--accent);margin-right:8px;"></i>Historial</div>
        <div style="display:flex;gap:8px;align-items:center;">
            <form method="GET" style="display:flex;gap:8px;">
                <select name="estado" class="form-control" style="width:140px;padding:7px 12px;font-size:12px;" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    @foreach(['borrador','calculada','aprobada','pagada','anulada'] as $e)<option value="{{ $e }}" {{ request('estado')==$e?'selected':'' }}>{{ ucfirst($e) }}</option>@endforeach
                </select>
                <select name="tipo_nomina" class="form-control" style="width:140px;padding:7px 12px;font-size:12px;" onchange="this.form.submit()">
                    <option value="">Todos los tipos</option>
                    @foreach(['ordinaria','vacacional','utilidades','bono','liquidacion'] as $t)<option value="{{ $t }}" {{ request('tipo_nomina')==$t?'selected':'' }}>{{ ucfirst($t) }}</option>@endforeach
                </select>
            </form>
            
            @can('nomina.crear')
            <a href="{{ route('nomina.nominas.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nueva Nómina</a>
            @endcan
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Número</th><th>Tipo</th><th>Período</th><th>Asignaciones</th><th>Deducciones</th><th>Neto a Pagar</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            @forelse($nominas as $n)
            <tr>
                <td><code style="color:var(--accent);font-size:12px;">{{ $n->numero }}</code></td>
                <td><span class="badge badge-blue">{{ ucfirst($n->tipo_nomina) }}</span></td>
                <td style="font-size:12px;">{{ $n->periodo_inicio->format('d/m/Y') }} — {{ $n->periodo_fin->format('d/m/Y') }}</td>
                <td style="color:var(--accent-3);">Bs. {{ number_format($n->total_asignaciones,2) }}</td>
                <td style="color:var(--accent-danger);">Bs. {{ number_format($n->total_deducciones,2) }}</td>
                <td style="font-weight:800;color:var(--accent-3);">Bs. {{ number_format($n->total_neto,2) }}</td>
                <td><span class="badge {{ $n->estadoBadge() }}">{{ ucfirst($n->estado) }}</span></td>
                <td>
                    @can('nomina.ver')
                    <a href="{{ route('nomina.nominas.show', $n) }}" class="btn btn-outline btn-sm">Ver</a>
                    @endcan
                </td>
            </tr>
            @empty
            <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">💰</div><div class="empty-title">Sin nóminas registradas</div></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    
    <x-pagination :paginator="$nominas" />
</div>
@endsection
