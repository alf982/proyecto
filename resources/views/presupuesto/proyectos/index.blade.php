@extends('layouts.app')
@section('title', 'Proyectos')
@section('breadcrumb')
    <span>Presupuesto</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Proyectos</span>
@endsection

@section('content')
{{-- 
  VISTA INDEX DE PROYECTOS (SIA)
  Listado de proyectos y acciones centralizadas formuladas para el año fiscal.
  Los Créditos Presupuestarios se vinculan operativamente desde aquí.
--}}

<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Proyectos</h1>
        <p class="page-subtitle">Proyectos del Plan Operativo Anual</p>
    </div>
    @can('proyectos.crear')
    <a href="{{ route('presupuesto.proyectos.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Nuevo Proyecto
    </a>
    @endcan
</div>

<div class="card fade-up" style="margin-bottom:20px;">
    <div class="card-body" style="padding:16px 22px;">
        <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:1;min-width:200px;">
                <label class="form-label">Buscar</label>
                <div style="position:relative;">
                    <input type="text" name="search" class="form-control" placeholder="Nombre o código..." value="{{ request('search') }}" style="padding-left:38px;">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--text-secondary);font-size:13px;"></i>
                </div>
            </div>
            <div style="min-width:180px;">
                <label class="form-label">Ejercicio</label>
                <select name="ejercicio_id" class="form-control">
                    <option value="">Todos</option>
                    @foreach($ejercicios as $ej)
                        <option value="{{ $ej->id }}" {{ request('ejercicio_id')==$ej->id?'selected':'' }}>{{ $ej->anio }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Filtrar</button>
                <a href="{{ route('presupuesto.proyectos.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="card fade-up" style="animation-delay:.05s">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Ejercicio</th>
                    <th>Unidad</th>
                    <th>Estado</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($proyectos as $proyecto)
                <tr>
                    <td><code style="background:rgba(124,92,252,0.1);color:var(--accent-2);padding:3px 8px;border-radius:5px;font-size:12px;font-weight:600;">{{ $proyecto->codigo }}</code></td>
                    <td style="font-size:13.5px;max-width:280px;">{{ $proyecto->nombre }}</td>
                    <td><span class="badge badge-blue">{{ $proyecto->ejercicioFiscal?->anio ?? '—' }}</span></td>
                    <td style="font-size:12px;color:var(--text-secondary);">{{ Str::limit($proyecto->unidadEjecutora?->nombre ?? '—', 30) }}</td>
                    <td>
                        @php $cls = match($proyecto->estado) { 'activo'=>'badge-active','terminado'=>'badge-blue','suspendido'=>'badge-danger',default=>'badge-warn' }; @endphp
                        <span class="badge {{ $cls }}">{{ ucfirst($proyecto->estado) }}</span>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;align-items:center;">
                            @can('proyectos.editar')
                            <a href="{{ route('presupuesto.proyectos.edit', $proyecto) }}" class="btn btn-outline btn-sm">
                                <i class="fa-solid fa-pen-to-square"></i> Editar
                            </a>
                            @endcan
                            
                            @can('proyectos.eliminar')
                            <form method="POST" action="{{ route('presupuesto.proyectos.destroy', $proyecto) }}" style="margin:0;"
                                  onsubmit="return confirm('¿Está seguro de eliminar lógicamente este Proyecto?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6">
                    <div class="empty-state">
                        <div class="empty-icon">🗂️</div>
                        <div class="empty-title">No hay proyectos</div>
                        <div class="empty-desc">Crea el primer proyecto del ejercicio fiscal.</div>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Componente estándar de paginación --}}
    <x-pagination :paginator="$proyectos" />
</div>
@endsection
