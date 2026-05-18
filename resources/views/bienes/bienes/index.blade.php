@extends('layouts.app')
@section('title', 'Inventario de Bienes')
@section('breadcrumb')
    <span>Bienes Nacionales</span>
    <span class="breadcrumb-sep">›</span>
    <span class="current">Inventario</span>
@endsection
@section('content')

{{-- 
  VISTA INDEX DE INVENTARIO DE BIENES
  Catálogo principal de todos los activos fijos registrados en el sistema.
  Permite filtrar por categoría, estado y exportar a PDF para auditorías físicas.
--}}

<div class="page-header fade-up">
    <h1 class="page-title">Inventario de Bienes</h1>
    <p class="page-subtitle">Activos fijos registrados en el sistema</p>
</div>
<div class="card fade-up">
    <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-computer" style="color:var(--accent);margin-right:8px;"></i>Bienes registrados</div>
        <div style="display:flex;gap:8px;align-items:center;">
            <form method="GET" style="display:flex;gap:8px;">
                <input name="search" value="{{ request('search') }}" placeholder="N° inv., descripción, serial…" class="form-control" style="width:220px;padding:7px 12px;font-size:12px;">
                <select name="estado" class="form-control" style="width:140px;padding:7px 12px;font-size:12px;" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    @foreach(['activo','en_reparacion','dado_de_baja','extraviado'] as $e)
                        <option value="{{ $e }}" {{ request('estado')==$e?'selected':'' }}>{{ ucwords(str_replace('_',' ',$e)) }}</option>
                    @endforeach
                </select>
                <select name="categoria" class="form-control" style="width:160px;padding:7px 12px;font-size:12px;" onchange="this.form.submit()">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $cat)<option value="{{ $cat->id }}" {{ request('categoria')==$cat->id?'selected':'' }}>{{ $cat->nombre }}</option>@endforeach
                </select>
            </form>
            
            @can('bienes.crear')
            <a href="{{ route('bienes.bienes.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Incorporar Bien</a>
            @endcan
            
            <a href="{{ route('pdf.inventario-bienes', request()->only(['categoria','estado'])) }}" target="_blank"
               class="btn btn-sm" style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);color:#ef4444;">
                <i class="fa-solid fa-file-pdf"></i> Exportar PDF
            </a>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nº Inventario</th><th>Descripción</th><th>Categoría</th><th>Unidad</th><th>Valor Actual</th><th>Estado</th><th>Fecha Incorp.</th><th></th></tr></thead>
            <tbody>
            @forelse($bienes as $b)
            <tr>
                <td><code style="color:var(--accent);font-size:12px;">{{ $b->numero_inventario }}</code></td>
                <td>
                    <div>{{ $b->descripcion }}</div>
                    @if($b->marca)<small style="color:var(--text-secondary);">{{ $b->marca }} {{ $b->modelo }}</small>@endif
                </td>
                <td style="font-size:12px;">{{ $b->categoria->nombre ?? '—' }}</td>
                <td style="font-size:12px;">{{ $b->unidadEjecutora->nombre ?? '—' }}</td>
                <td style="font-weight:700;color:var(--accent-3);">Bs. {{ number_format($b->valor_actual,2) }}</td>
                <td><span class="badge {{ $b->estadoBadge() }}">{{ ucwords(str_replace('_',' ',$b->estado)) }}</span></td>
                <td style="font-size:12px;color:var(--text-secondary);">{{ $b->fecha_incorporacion->format('d/m/Y') }}</td>
                <td>
                    @can('bienes.ver')
                    <a href="{{ route('bienes.bienes.show', $b) }}" class="btn btn-outline btn-sm">Ver</a>
                    @endcan
                </td>
            </tr>
            @empty
            <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">💻</div><div class="empty-title">Sin bienes registrados</div><p class="empty-desc">Incorpore el primer bien al inventario.</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Componente estándar de paginación --}}
    <x-pagination :paginator="$bienes" />
</div>
@endsection
