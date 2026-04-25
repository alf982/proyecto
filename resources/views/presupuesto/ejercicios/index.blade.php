@extends('layouts.app')
@section('title', 'Ejercicios Fiscales')
@section('breadcrumb')
    <span>Presupuesto</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Ejercicios Fiscales</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;justify-content:space-between;">
    <div>
        <h1 class="page-title">Ejercicios Fiscales</h1>
        <p class="page-subtitle">Control del ejercicio presupuestario anual</p>
    </div>
    <a href="{{ route('presupuesto.ejercicios.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Nuevo Ejercicio
    </a>
</div>

<div class="card fade-up" style="animation-delay:.05s">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Año</th>
                    <th>Período</th>
                    <th>Estado</th>
                    <th>Creado por</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ejercicios as $ejercicio)
                <tr>
                    <td>
                        <span style="font-size:22px;font-weight:800;letter-spacing:-1px;">{{ $ejercicio->anio }}</span>
                    </td>
                    <td style="font-size:13px;color:var(--text-secondary);">
                        {{ $ejercicio->fecha_inicio->isoFormat('D MMM YYYY') }} —
                        {{ $ejercicio->fecha_fin->isoFormat('D MMM YYYY') }}
                    </td>
                    <td>
                        @php
                            $cls = match($ejercicio->estado) {
                                'activo'  => 'badge-active',
                                'cerrado' => 'badge-danger',
                                default   => 'badge-warn',
                            };
                            $lbl = match($ejercicio->estado) {
                                'activo'  => 'Activo',
                                'cerrado' => 'Cerrado',
                                default   => 'Borrador',
                            };
                        @endphp
                        <span class="badge {{ $cls }}">{{ $lbl }}</span>
                    </td>
                    <td style="font-size:13px;">{{ $ejercicio->creadoPor?->name ?? '—' }}</td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:8px;justify-content:flex-end;align-items:center;">

                            {{-- Editar --}}
                            <a href="{{ route('presupuesto.ejercicios.edit', $ejercicio) }}" class="btn btn-outline btn-sm">
                                <i class="fa-solid fa-pen-to-square"></i> Editar
                            </a>

                            {{-- Activar: form independiente, sin JS --}}
                            @if($ejercicio->estado === 'borrador')
                                <form method="POST"
                                      action="{{ route('presupuesto.ejercicios.activar', $ejercicio->id) }}"
                                      style="margin:0;">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-sm"
                                            style="background:rgba(34,211,166,0.15);border:1px solid rgba(34,211,166,0.35);color:var(--accent-3);">
                                        <i class="fa-solid fa-play"></i> Activar
                                    </button>
                                </form>
                            @endif

                            {{-- Cerrar: form independiente, sin JS --}}
                            @if($ejercicio->estado === 'activo')
                                <form method="POST"
                                      action="{{ route('presupuesto.ejercicios.cerrar', $ejercicio->id) }}"
                                      style="margin:0;">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-danger btn-sm">
                                        <i class="fa-solid fa-lock"></i> Cerrar
                                    </button>
                                </form>
                            @endif

                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5">
                    <div class="empty-state">
                        <div class="empty-icon">📅</div>
                        <div class="empty-title">No hay ejercicios fiscales</div>
                        <div class="empty-desc">Crea el primer ejercicio fiscal para comenzar la formulación del presupuesto.</div>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
