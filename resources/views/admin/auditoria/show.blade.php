@extends('layouts.app')

@section('title', 'Detalle de Auditoría')

@section('breadcrumb')
    <a href="{{ route('admin.auditoria.index') }}" style="color:var(--text-secondary);text-decoration:none;">Auditoría</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">Evento #{{ $audit->id }}</span>
@endsection

@section('content')
<div class="page-header fade-up">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="{{ route('admin.auditoria.index') }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <div>
            <h1 class="page-title">Detalle del Evento #{{ $audit->id }}</h1>
            <p class="page-subtitle">{{ $audit->created_at->format('d/m/Y \a \l\a\s H:i:s') }}</p>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:320px 1fr;gap:16px;" class="fade-up">

    {{-- Info del evento --}}
    <div>
        <div class="card">
            <div class="card-header"><div class="card-title">Información del Evento</div></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:14px;">
                <div>
                    <div class="form-label">Evento</div>
                    @php
                        $badgeClass = match($audit->event) {
                            'created'  => 'badge-active',
                            'updated'  => 'badge-blue',
                            'deleted'  => 'badge-danger',
                            'restored' => 'badge-purple',
                            default    => 'badge-warn',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ ucfirst($audit->event) }}</span>
                </div>
                <div>
                    <div class="form-label">Modelo</div>
                    <span style="font-family:monospace;font-size:13px;">{{ class_basename($audit->auditable_type) }}</span>
                </div>
                <div>
                    <div class="form-label">ID del Registro</div>
                    <span style="font-family:monospace;">#{{ $audit->auditable_id }}</span>
                </div>
                <div>
                    <div class="form-label">Usuario</div>
                    @if($audit->user)
                        <div style="font-weight:600;">{{ $audit->user->name }}</div>
                        <div style="font-size:12px;color:var(--text-secondary);">{{ $audit->user->email }}</div>
                    @else
                        <span style="color:var(--text-secondary);">Sistema / Automático</span>
                    @endif
                </div>
                <div>
                    <div class="form-label">IP del Cliente</div>
                    <span style="font-family:monospace;font-size:12px;">{{ $audit->ip_address ?? '—' }}</span>
                </div>
                <div>
                    <div class="form-label">Navigador</div>
                    <span style="font-size:11px;color:var(--text-secondary);">{{ Str::limit($audit->user_agent ?? '—', 60) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Cambios --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        @if($audit->old_values)
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-rotate-left" style="color:var(--accent-danger);margin-right:8px;"></i>Valores Anteriores</div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Campo</th><th>Valor</th></tr></thead>
                    <tbody>
                    @foreach($audit->old_values as $campo => $valor)
                    <tr>
                        <td style="font-family:monospace;font-size:12px;color:var(--text-secondary);">{{ $campo }}</td>
                        <td style="font-size:13px;color:#f87171;">
                            @if(is_array($valor))
                                <span style="font-family:monospace;font-size:11px;">{{ json_encode($valor, JSON_UNESCAPED_UNICODE) }}</span>
                            @else
                                {{ $valor ?? '(vacío)' }}
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if($audit->new_values)
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-rotate-right" style="color:var(--accent-3);margin-right:8px;"></i>Valores Nuevos</div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Campo</th><th>Valor</th></tr></thead>
                    <tbody>
                    @foreach($audit->new_values as $campo => $valor)
                    <tr>
                        <td style="font-family:monospace;font-size:12px;color:var(--text-secondary);">{{ $campo }}</td>
                        <td style="font-size:13px;color:var(--accent-3);">
                            @if(is_array($valor))
                                <span style="font-family:monospace;font-size:11px;">{{ json_encode($valor, JSON_UNESCAPED_UNICODE) }}</span>
                            @else
                                {{ $valor ?? '(vacío)' }}
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if(!$audit->old_values && !$audit->new_values)
        <div class="card">
            <div class="empty-state" style="padding:40px;">
                <div class="empty-icon">📋</div>
                <div class="empty-title">Sin detalle de campos</div>
                <div class="empty-desc">Este evento no registró cambios detallados de campos.</div>
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
