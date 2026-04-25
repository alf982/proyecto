@extends('layouts.app')
@section('title', 'Cargos')
@section('breadcrumb')
    <span>Nómina y Personal</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Cargos</span>
@endsection
@section('content')
<div class="page-header fade-up">
    <h1 class="page-title">Cargos</h1>
    <p class="page-subtitle">Estructura de cargos y salarios base de la institución</p>
</div>
<div class="card fade-up">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-briefcase" style="color:var(--accent);margin-right:8px;"></i>Listado de Cargos</div>
        <a href="{{ route('nomina.cargos.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nuevo Cargo</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Código</th><th>Nombre del Cargo</th><th>Nivel</th><th>Salario Base</th><th>Empleados</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            @forelse($cargos as $c)
            <tr>
                <td><code style="color:var(--accent);font-size:12px;">{{ $c->codigo }}</code></td>
                <td style="font-weight:600;">{{ $c->nombre }}</td>
                <td><span class="badge badge-blue">{{ ucfirst($c->nivel) }}</span></td>
                <td style="font-weight:700;color:var(--accent-3);">Bs. {{ number_format($c->salario_base,2) }}</td>
                <td><span class="badge badge-blue">{{ $c->empleados_count }}</span></td>
                <td><span class="badge {{ $c->activo ? 'badge-active' : 'badge-danger' }}">{{ $c->activo ? 'Activo' : 'Inactivo' }}</span></td>
                <td><a href="{{ route('nomina.cargos.edit', $c) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i></a></td>
            </tr>
            @empty
            <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">💼</div><div class="empty-title">Sin cargos registrados</div></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
