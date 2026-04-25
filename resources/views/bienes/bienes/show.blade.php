@extends('layouts.app')
@section('title', 'Bien ' . $bien->numero_inventario)
@section('breadcrumb')
    <a href="{{ route('bienes.bienes.index') }}">Inventario</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">{{ $bien->numero_inventario }}</span>
@endsection
@section('content')
<div class="page-header fade-up">
    <h1 class="page-title">{{ $bien->descripcion }}</h1>
    <div style="display:flex;gap:8px;margin-top:8px;">
        <span class="badge {{ $bien->estadoBadge() }}" style="font-size:13px;padding:5px 14px;">{{ ucwords(str_replace('_',' ',$bien->estado)) }}</span>
        <code style="color:var(--accent);background:rgba(79,142,247,0.1);padding:5px 12px;border-radius:7px;">{{ $bien->numero_inventario }}</code>
        @if($bien->estado === 'activo')
        <a href="{{ route('bienes.bienes.edit', $bien) }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i> Editar</a>
        @endif
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
<div>
<div class="card fade-up">
    <div class="card-header"><div class="card-title">Ficha del Bien</div></div>
    <div class="card-body">
        <div class="form-row form-row-3" style="margin-bottom:16px;">
            <div><div style="font-size:11px;color:var(--text-secondary);">CATEGORÍA</div><div style="font-weight:600;">{{ $bien->categoria->nombre ?? '—' }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">MARCA / MODELO</div><div>{{ $bien->marca ?? '—' }} {{ $bien->modelo }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">SERIAL</div><div style="font-family:monospace;">{{ $bien->serial ?? '—' }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">AÑO ADQUISICIÓN</div><div>{{ $bien->anio_adquisicion ?? '—' }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">VALOR ADQUISICIÓN</div><div style="color:var(--accent-3);">Bs. {{ number_format($bien->valor_adquisicion,2) }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">VALOR ACTUAL</div><div style="font-weight:700;color:var(--accent-3);">Bs. {{ number_format($bien->valor_actual,2) }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">UNIDAD EJECUTORA</div><div style="font-size:13px;">{{ $bien->unidadEjecutora->nombre ?? '—' }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">UBICACIÓN</div><div style="font-size:13px;">{{ $bien->ubicacion ?? '—' }}</div></div>
            <div><div style="font-size:11px;color:var(--text-secondary);">RESPONSABLE</div><div style="font-size:13px;">{{ $bien->responsable ?? '—' }}</div></div>
        </div>
        @if($bien->observaciones)<div><div style="font-size:11px;color:var(--text-secondary);">OBSERVACIONES</div><div style="font-size:13px;margin-top:4px;">{{ $bien->observaciones }}</div></div>@endif
    </div>
</div>

<div class="card fade-up" style="margin-top:20px;animation-delay:.05s">
    <div class="card-header"><div class="card-title">Historial de Movimientos</div></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Fecha</th><th>Tipo</th><th>Origen</th><th>Destino</th><th>Motivo</th><th>Usuario</th></tr></thead>
            <tbody>
            @forelse($bien->movimientos->sortByDesc('fecha') as $mov)
            <tr>
                <td style="font-size:12px;">{{ $mov->fecha->format('d/m/Y') }}</td>
                <td><span class="badge badge-blue">{{ ucfirst($mov->tipo) }}</span></td>
                <td style="font-size:12px;">{{ $mov->unidadOrigen->nombre ?? '—' }}</td>
                <td style="font-size:12px;">{{ $mov->unidadDestino->nombre ?? '—' }}</td>
                <td style="font-size:12px;max-width:200px;">{{ $mov->motivo }}</td>
                <td style="font-size:12px;">{{ $mov->usuario->name ?? '—' }}</td>
            </tr>
            @empty<tr><td colspan="6" style="text-align:center;color:var(--text-secondary);">Sin movimientos</td></tr>@endforelse
            </tbody>
        </table>
    </div>
</div>
</div>

<div>
@if($bien->estado === 'activo')
<div class="card fade-up" style="animation-delay:.1s">
    <div class="card-header"><div class="card-title">Acciones</div></div>
    <div class="card-body" style="display:flex;flex-direction:column;gap:10px;">
        <button class="btn btn-outline" onclick="document.getElementById('modal-traslado').style.display='flex'"><i class="fa-solid fa-arrows-left-right"></i> Trasladar</button>
        <button class="btn btn-danger" onclick="document.getElementById('modal-baja').style.display='flex'"><i class="fa-solid fa-ban"></i> Dar de Baja</button>
    </div>
</div>
@endif
<div style="margin-top:12px;"><a href="{{ route('bienes.bienes.index') }}" class="btn btn-outline" style="width:100%;justify-content:center;">← Volver al inventario</a></div>
</div>
</div>

<div id="modal-traslado" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:999;align-items:center;justify-content:center;">
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:28px;width:420px;">
        <h3 style="margin-bottom:16px;font-size:16px;">Trasladar Bien</h3>
        <form method="POST" action="{{ route('bienes.bienes.trasladar', $bien) }}">@csrf
            <div class="form-group">
                <label class="form-label">Unidad de Destino *</label>
                <select name="unidad_destino_id" class="form-control" required>
                    <option value="">Seleccione…</option>
                    @foreach(\App\Models\UnidadEjecutora::where('activo',true)->orderBy('nombre')->get() as $u)
                    @if($u->id !== $bien->unidad_ejecutora_id)<option value="{{ $u->id }}">{{ $u->nombre }}</option>@endif
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label class="form-label">Motivo *</label><textarea name="motivo" class="form-control" rows="2" required minlength="5"></textarea></div>
            <div style="display:flex;gap:10px;"><button type="submit" class="btn btn-primary">Confirmar Traslado</button><button type="button" class="btn btn-outline" onclick="document.getElementById('modal-traslado').style.display='none'">Cancelar</button></div>
        </form>
    </div>
</div>

<div id="modal-baja" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:999;align-items:center;justify-content:center;">
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:28px;width:420px;">
        <h3 style="margin-bottom:16px;font-size:16px;color:var(--accent-danger);">Dar de Baja</h3>
        <form method="POST" action="{{ route('bienes.bienes.baja', $bien) }}">@csrf
            <div class="form-group"><label class="form-label">Motivo de baja *</label><textarea name="motivo_baja" class="form-control" rows="3" required minlength="10" placeholder="Describa el motivo…"></textarea></div>
            <div style="display:flex;gap:10px;"><button type="submit" class="btn btn-danger">Confirmar Baja</button><button type="button" class="btn btn-outline" onclick="document.getElementById('modal-baja').style.display='none'">Cancelar</button></div>
        </form>
    </div>
</div>
@endsection
