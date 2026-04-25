@extends('layouts.app')
@section('title', 'Detalle del Proyecto')
@section('breadcrumb')
    <a href="{{ route('presupuesto.proyectos.index') }}" style="color:var(--text-secondary);text-decoration:none;">Proyectos</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">{{ $proyecto->codigo }}</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;gap:12px;">
    <a href="{{ route('presupuesto.proyectos.index') }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <h1 class="page-title">{{ $proyecto->nombre }}</h1>
        <p class="page-subtitle">{{ $proyecto->codigo }} · {{ $proyecto->unidadEjecutora?->nombre }}</p>
    </div>
    @php
        $badgeClass = match($proyecto->estado) {
            'activo'       => 'badge-active',
            'formulacion'  => 'badge-blue',
            'finalizado'   => 'badge-warn',
            default        => 'badge-danger',
        };
    @endphp
    <span class="badge {{ $badgeClass }}" style="margin-left:auto;">{{ ucfirst($proyecto->estado) }}</span>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;" class="fade-up">
    <div style="display:flex;flex-direction:column;gap:16px;">
        {{-- Causaciones vinculadas --}}
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fa-solid fa-file-invoice-dollar" style="color:var(--accent-warn);margin-right:8px;"></i>Causaciones del Proyecto</div></div>
            @php $causaciones = $proyecto->causaciones ?? collect(); @endphp
            @if($causaciones->isEmpty())
                <div class="empty-state" style="padding:30px;"><div class="empty-icon">📄</div><div class="empty-title">Sin causaciones registradas</div></div>
            @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>N°</th><th>Beneficiario</th><th>Fecha</th><th>Monto</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                    @foreach($causaciones as $c)
                    <tr>
                        <td style="font-size:12px;font-family:monospace;">{{ $c->numero }}</td>
                        <td style="font-size:12px;">{{ Str::limit($c->beneficiario, 30) }}</td>
                        <td style="font-size:12px;">{{ $c->fecha_causacion?->format('d/m/Y') }}</td>
                        <td style="font-size:12px;font-weight:600;">Bs. {{ number_format($c->monto_causado, 2) }}</td>
                        <td><span class="badge {{ $c->getEstadoBadgeClass() }}">{{ $c->getEstadoLabel() }}</span></td>
                        <td><a href="{{ route('presupuesto.causaciones.show', $c) }}" class="btn btn-sm btn-outline"><i class="fa-solid fa-eye"></i></a></td>
                    </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align:right;font-size:12px;color:var(--text-secondary);">Total causado:</td>
                            <td style="font-weight:700;font-size:13px;">Bs. {{ number_format($causaciones->sum('monto_causado'), 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif
        </div>
    </div>

    <div class="card fade-up" style="height:fit-content;">
        <div class="card-header"><div class="card-title">Información del Proyecto</div></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:14px;padding:20px;">
            <div><div class="form-label">Código</div><code style="font-size:13px;background:rgba(79,142,247,0.1);color:var(--accent);padding:4px 8px;border-radius:6px;">{{ $proyecto->codigo }}</code></div>
            <div><div class="form-label">Descripción</div><div style="font-size:12px;color:var(--text-secondary);">{{ $proyecto->descripcion ?? '—' }}</div></div>
            <div><div class="form-label">Ejercicio Fiscal</div><div style="font-size:13px;">{{ $proyecto->ejercicioFiscal?->anio }}</div></div>
            <div><div class="form-label">Unidad Ejecutora</div><div style="font-size:13px;">{{ $proyecto->unidadEjecutora?->nombre }}</div></div>
            <div><div class="form-label">Período</div>
                <div style="font-size:12px;">{{ $proyecto->fecha_inicio?->format('d/m/Y') }} — {{ $proyecto->fecha_fin?->format('d/m/Y') }}</div>
            </div>
            <div class="nav-divider"></div>
            @can('proyectos.editar')
            <a href="{{ route('presupuesto.proyectos.edit', $proyecto) }}" class="btn btn-primary" style="width:100%;text-align:center;"><i class="fa-solid fa-pen-to-square"></i> Editar</a>
            @endcan
        </div>
    </div>
</div>
@endsection
