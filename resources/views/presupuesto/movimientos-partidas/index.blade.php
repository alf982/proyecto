@extends('layouts.app')
@section('title', 'Movimientos de Partidas')
@section('breadcrumb')
    <span>Presupuesto</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Movimientos de Partidas</span>
@endsection

@section('content')
{{-- 
  VISTA INDEX DE MOVIMIENTOS DE PARTIDAS (Libro Mayor)
  Muestra el historial de ingresos y egresos (Asignaciones, Traspasos, etc.).
  Integra múltiples filtros financieros.
--}}

<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Movimientos de Partidas Presupuestarias</h1>
        <p class="page-subtitle">Registro de ingresos, egresos y modificaciones de fondos por partida</p>
    </div>
    @can('movimientos.crear')
    <a href="{{ route('presupuesto.movimientos-partidas.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Nuevo Movimiento
    </a>
    @endcan
</div>

{{-- Filtros --}}
<div class="card fade-up" style="margin-bottom:20px;">
    <div class="card-body" style="padding:16px 22px;">
        <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:1;min-width:180px;">
                <label class="form-label">Buscar</label>
                <div style="position:relative;">
                    <input type="text" name="search" class="form-control" placeholder="N°, concepto, referencia..."
                        value="{{ request('search') }}" style="padding-left:38px;">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--text-secondary);font-size:13px;"></i>
                </div>
            </div>
            <div style="min-width:200px;">
                <label class="form-label">Partida</label>
                <select name="partida" class="form-control">
                    <option value="">Todas</option>
                    @foreach($partidas as $p)
                        <option value="{{ $p->id }}" {{ request('partida') == $p->id ? 'selected' : '' }}>
                            {{ $p->codigo }} — {{ Str::limit($p->descripcion, 40) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:180px;">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-control">
                    <option value="">Todos</option>
                    @foreach([
                        'asignacion'           => 'Asignación Inicial',
                        'credito_adicional'    => 'Crédito Adicional',
                        'modificacion_entrada' => 'Modificación (Entrada)',
                        'modificacion_salida'  => 'Modificación (Salida)',
                        'ejecucion'            => 'Ejecución',
                        'reintegro'            => 'Reintegro',
                        'nota_credito'         => 'Nota de Crédito',
                        'nota_debito'          => 'Nota de Débito',
                    ] as $val => $label)
                        <option value="{{ $val }}" {{ request('tipo') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:130px;">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-control">
                    <option value="">Todos</option>
                    <option value="pendiente"  {{ request('estado')=='pendiente'  ? 'selected':'' }}>Pendiente</option>
                    <option value="confirmado" {{ request('estado')=='confirmado' ? 'selected':'' }}>Confirmado</option>
                    <option value="anulado"    {{ request('estado')=='anulado'    ? 'selected':'' }}>Anulado</option>
                </select>
            </div>
            <div style="min-width:130px;">
                <label class="form-label">Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
            </div>
            <div style="min-width:130px;">
                <label class="form-label">Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Filtrar</button>
                <a href="{{ route('presupuesto.movimientos-partidas.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="card fade-up" style="animation-delay:.05s;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Fecha</th>
                    <th>Partida</th>
                    <th>Tipo</th>
                    <th>Concepto</th>
                    <th style="text-align:right;">Monto</th>
                    <th style="text-align:right;">Saldo Posterior</th>
                    <th>Estado</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movimientos as $mov)
                <tr style="{{ $mov->estado === 'anulado' ? 'opacity:.55;' : '' }}">
                    <td>
                        <code style="font-size:11px;background:rgba(79,142,247,0.1);color:var(--accent);padding:2px 7px;border-radius:5px;">
                            {{ $mov->numero }}
                        </code>
                    </td>
                    <td style="font-size:12.5px;">{{ $mov->fecha_movimiento->format('d/m/Y') }}</td>
                    <td>
                        <div style="font-size:12px;font-weight:600;">{{ $mov->partida?->codigo ?? '—' }}</div>
                        <div style="font-size:10px;color:var(--text-secondary);">{{ Str::limit($mov->partida?->descripcion ?? '', 35) }}</div>
                        @if($mov->contrapartida)
                            <div style="font-size:10px;color:var(--accent-warn);margin-top:2px;">
                                <i class="fa-solid fa-arrows-left-right" style="font-size:9px;"></i>
                                {{ $mov->contrapartida?->codigo ?? '—' }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $mov->getBadgeTipoClass() }}" style="font-size:10px;white-space:nowrap;">
                            {{ \App\Models\MovimientoPartida::etiquetaTipo($mov->tipo) }}
                        </span>
                    </td>
                    <td style="font-size:12.5px;max-width:220px;">{{ Str::limit($mov->concepto, 50) }}</td>
                    <td style="text-align:right;font-size:13px;font-weight:600;">
                        <span style="color:{{ $mov->esIngreso() ? 'var(--accent-3)' : 'var(--accent-danger)' }};">
                            {{ $mov->esIngreso() ? '+' : '-' }} Bs. {{ number_format($mov->monto, 2) }}
                        </span>
                    </td>
                    <td style="text-align:right;font-size:13px;">
                        Bs. {{ number_format($mov->saldo_posterior, 2) }}
                    </td>
                    <td>
                        <span class="badge {{ $mov->getBadgeEstadoClass() }}" style="font-size:10px;">
                            {{ ucfirst($mov->estado) }}
                        </span>
                    </td>
                    <td style="text-align:right;">
                        @can('movimientos.ver')
                        <a href="{{ route('presupuesto.movimientos-partidas.show', $mov) }}" class="btn btn-outline btn-sm">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="9">
                    <div class="empty-state">
                        <div class="empty-icon">📊</div>
                        <div class="empty-title">No hay movimientos registrados</div>
                        <div class="empty-desc">Registra el primer movimiento de partida presupuestaria.</div>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Componente estándar de paginación --}}
    <x-pagination :paginator="$movimientos" />
</div>
@endsection
