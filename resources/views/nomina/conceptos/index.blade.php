@extends('layouts.app')
@section('title', 'Conceptos de Nómina')
@section('breadcrumb')
    <span>Nómina y Personal</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Conceptos de Nómina</span>
@endsection
@section('content')
<div class="page-header fade-up">
    <h1 class="page-title">Conceptos de Nómina</h1>
    <p class="page-subtitle">Configure asignaciones y deducciones (IVSS, FAOV, BANAVIH, bonos, etc.) con sus valores editables</p>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
    <div class="card fade-up" style="padding:18px;">
        <div style="font-size:12px;color:var(--text-secondary);margin-bottom:4px;">ASIGNACIONES ACTIVAS</div>
        <div style="font-size:28px;font-weight:800;color:var(--accent-3);">{{ $conceptos->where('tipo','asignacion')->where('activo',true)->count() }}</div>
    </div>
    <div class="card fade-up" style="padding:18px;animation-delay:.05s">
        <div style="font-size:12px;color:var(--text-secondary);margin-bottom:4px;">DEDUCCIONES ACTIVAS</div>
        <div style="font-size:28px;font-weight:800;color:var(--accent-danger);">{{ $conceptos->where('tipo','deduccion')->where('activo',true)->count() }}</div>
    </div>
</div>
<div class="card fade-up" style="animation-delay:.1s">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-sliders" style="color:var(--accent);margin-right:8px;"></i>Conceptos configurables</div>
        <a href="{{ route('nomina.conceptos.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nuevo Concepto</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Código</th><th>Nombre</th><th>Tipo</th><th>Cálculo</th><th>Valor</th><th>Aplica A</th><th>Obligatorio</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            @forelse($conceptos as $c)
            <tr>
                <td><code style="color:var(--accent);font-size:12px;">{{ $c->codigo }}</code></td>
                <td>{{ $c->nombre }}</td>
                <td><span class="badge {{ $c->tipo === 'asignacion' ? 'badge-active' : 'badge-danger' }}">{{ $c->tipo === 'asignacion' ? '+ Asignación' : '- Deducción' }}</span></td>
                <td>{{ $c->calculo === 'porcentaje' ? 'Porcentaje' : 'Monto fijo' }}</td>
                <td><strong>{{ $c->calculo === 'porcentaje' ? $c->valor.'%' : 'Bs. '.number_format($c->valor,2) }}</strong></td>
                <td style="font-size:12px;">{{ ucfirst($c->aplica_a) }}</td>
                <td>@if($c->es_obligatorio)<span class="badge badge-warn">Sí</span>@else<span style="color:var(--text-secondary);font-size:12px;">No</span>@endif</td>
                <td><span class="badge {{ $c->activo ? 'badge-active' : 'badge-danger' }}">{{ $c->activo ? 'Activo' : 'Inactivo' }}</span></td>
                <td><a href="{{ route('nomina.conceptos.edit', $c) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i></a></td>
            </tr>
            @empty
            <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">⚙️</div><div class="empty-title">Sin conceptos</div><p class="empty-desc">Cree conceptos como IVSS (4%), FAOV (1%), BANAVIH (1%), etc.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
