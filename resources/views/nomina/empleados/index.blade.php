@extends('layouts.app')
@section('title', 'Empleados')
@section('breadcrumb')
    <span>Nómina y Personal</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Empleados</span>
@endsection
@section('content')
<div class="page-header fade-up">
    <h1 class="page-title">Empleados</h1>
    <p class="page-subtitle">Personal registrado en el sistema de nómina</p>
</div>
<div class="card fade-up">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-id-card" style="color:var(--accent);margin-right:8px;"></i>Listado</div>
        <div style="display:flex;gap:8px;align-items:center;">
            <form method="GET" style="display:flex;gap:8px;">
                <input name="search" value="{{ request('search') }}" placeholder="Cédula, nombre…" class="form-control" style="width:200px;padding:7px 12px;font-size:12px;">
                <select name="estado" class="form-control" style="width:130px;padding:7px 12px;font-size:12px;" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    @foreach(['activo','inactivo','jubilado','retirado'] as $e)<option value="{{ $e }}" {{ request('estado')==$e?'selected':'' }}>{{ ucfirst($e) }}</option>@endforeach
                </select>
                <select name="tipo" class="form-control" style="width:130px;padding:7px 12px;font-size:12px;" onchange="this.form.submit()">
                    <option value="">Todos los tipos</option>
                    @foreach(['fijo','contratado','obrero'] as $t)<option value="{{ $t }}" {{ request('tipo')==$t?'selected':'' }}>{{ ucfirst($t) }}</option>@endforeach
                </select>
            </form>
            <a href="{{ route('nomina.empleados.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nuevo Empleado</a>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Cédula</th><th>Nombre Completo</th><th>Cargo</th><th>Unidad</th><th>Tipo</th><th>Estado</th><th>Ingreso</th><th></th></tr></thead>
            <tbody>
            @forelse($empleados as $emp)
            <tr>
                <td><code style="color:var(--accent);font-size:12px;">{{ $emp->cedula }}</code></td>
                <td style="font-weight:600;">{{ $emp->nombre_completo }}</td>
                <td style="font-size:12px;">{{ $emp->cargo->nombre ?? '—' }}</td>
                <td style="font-size:12px;">{{ $emp->unidadEjecutora->nombre ?? '—' }}</td>
                <td><span class="badge badge-blue">{{ ucfirst($emp->tipo) }}</span></td>
                <td><span class="badge {{ $emp->estadoBadge() }}">{{ ucfirst($emp->estado) }}</span></td>
                <td style="font-size:12px;color:var(--text-secondary);">{{ $emp->fecha_ingreso->format('d/m/Y') }}</td>
                <td style="display:flex;gap:6px;">
                    <a href="{{ route('nomina.empleados.show', $emp) }}" class="btn btn-outline btn-sm">Ver</a>
                    <a href="{{ route('nomina.empleados.edit', $emp) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i></a>
                </td>
            </tr>
            @empty
            <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">👤</div><div class="empty-title">Sin empleados registrados</div></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($empleados->hasPages())<div class="pagination">{{ $empleados->links() }}</div>@endif
</div>
@endsection
