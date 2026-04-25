@extends('layouts.app')
@section('title', 'Movimiento ' . $movimiento->numero)
@section('breadcrumb')
    <span>Presupuesto</span>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <a href="{{ route('presupuesto.movimientos-partidas.index') }}" style="color:var(--text-secondary);text-decoration:none;">Movimientos</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">{{ $movimiento->numero }}</span>
@endsection

@section('content')
<div class="page-header fade-up" style="display:flex;align-items:center;gap:12px;">
    <a href="{{ route('presupuesto.movimientos-partidas.index') }}" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
    <div style="flex:1;">
        <h1 class="page-title">{{ $movimiento->numero }}</h1>
        <p class="page-subtitle">{{ \App\Models\MovimientoPartida::etiquetaTipo($movimiento->tipo) }}</p>
    </div>
    <span class="badge {{ $movimiento->getBadgeEstadoClass() }}" style="font-size:12px;padding:6px 14px;">
        {{ ucfirst($movimiento->estado) }}
    </span>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:16px;" class="fade-up">

    {{-- Panel principal --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Datos del movimiento --}}
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fa-solid fa-file-invoice" style="color:var(--accent);margin-right:8px;"></i>Datos del Movimiento</div></div>
            <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;padding:20px;">
                <div>
                    <div class="form-label">N° Movimiento</div>
                    <code style="font-size:15px;font-weight:700;background:rgba(79,142,247,0.1);color:var(--accent);padding:6px 10px;border-radius:8px;display:inline-block;">
                        {{ $movimiento->numero }}
                    </code>
                </div>
                <div>
                    <div class="form-label">Fecha</div>
                    <div style="font-size:14px;font-weight:600;">{{ $movimiento->fecha_movimiento->format('d/m/Y') }}</div>
                </div>
                <div>
                    <div class="form-label">Tipo</div>
                    <span class="badge {{ $movimiento->getBadgeTipoClass() }}">
                        {{ \App\Models\MovimientoPartida::etiquetaTipo($movimiento->tipo) }}
                    </span>
                </div>
                <div>
                    <div class="form-label">Referencia</div>
                    <div style="font-size:13px;">{{ $movimiento->referencia ?? '—' }}</div>
                </div>
                <div style="grid-column:1/-1;">
                    <div class="form-label">Concepto</div>
                    <div style="font-size:13.5px;">{{ $movimiento->concepto }}</div>
                </div>
                @if($movimiento->observaciones)
                <div style="grid-column:1/-1;">
                    <div class="form-label">Observaciones</div>
                    <div style="font-size:13px;color:var(--text-secondary);">{{ $movimiento->observaciones }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Partidas afectadas --}}
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="fa-solid fa-list-ol" style="color:var(--accent);margin-right:8px;"></i>Partidas Afectadas</div></div>
            <div class="card-body" style="padding:20px;display:flex;flex-direction:column;gap:14px;">

                {{-- Partida principal --}}
                <div style="padding:14px;background:rgba(79,142,247,0.06);border:1px solid rgba(79,142,247,0.2);border-radius:10px;">
                    <div style="font-size:10px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Partida Principal</div>
                    <div style="font-size:14px;font-weight:700;">
                        <code style="color:var(--accent);">{{ $movimiento->partida->codigo }}</code>
                        — {{ $movimiento->partida->descripcion }}
                    </div>
                    <div style="display:flex;gap:20px;margin-top:12px;">
                        <div>
                            <div style="font-size:10px;color:var(--text-secondary);">Saldo Anterior</div>
                            <div style="font-size:14px;font-weight:600;">Bs. {{ number_format($movimiento->saldo_anterior, 2) }}</div>
                        </div>
                        <div style="font-size:20px;color:var(--text-secondary);align-self:center;">→</div>
                        <div>
                            <div style="font-size:10px;color:var(--text-secondary);">
                                {{ $movimiento->esIngreso() ? '+ Monto' : '- Monto' }}
                            </div>
                            <div style="font-size:14px;font-weight:700;color:{{ $movimiento->esIngreso() ? 'var(--accent-3)' : 'var(--accent-danger)' }};">
                                {{ $movimiento->esIngreso() ? '+' : '-' }} Bs. {{ number_format($movimiento->monto, 2) }}
                            </div>
                        </div>
                        <div style="font-size:20px;color:var(--text-secondary);align-self:center;">→</div>
                        <div>
                            <div style="font-size:10px;color:var(--text-secondary);">Saldo Posterior</div>
                            <div style="font-size:15px;font-weight:700;">Bs. {{ number_format($movimiento->saldo_posterior, 2) }}</div>
                        </div>
                    </div>
                </div>

                {{-- Contrapartida (solo para modificaciones) --}}
                @if($movimiento->movimientoRelacionado)
                <div style="padding:14px;background:rgba(255,180,0,0.06);border:1px solid rgba(255,180,0,0.25);border-radius:10px;">
                    <div style="font-size:10px;color:var(--accent-warn);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">
                        <i class="fa-solid fa-arrows-left-right"></i> Contrapartida (doble afectación)
                    </div>
                    @php $espejo = $movimiento->movimientoRelacionado; @endphp
                    <div style="font-size:14px;font-weight:700;">
                        <code style="color:var(--accent-warn);">{{ $espejo->partida->codigo }}</code>
                        — {{ $espejo->partida->descripcion }}
                    </div>
                    <div style="display:flex;gap:20px;margin-top:12px;">
                        <div>
                            <div style="font-size:10px;color:var(--text-secondary);">Saldo Anterior</div>
                            <div style="font-size:14px;font-weight:600;">Bs. {{ number_format($espejo->saldo_anterior, 2) }}</div>
                        </div>
                        <div style="font-size:20px;color:var(--text-secondary);align-self:center;">→</div>
                        <div>
                            <div style="font-size:10px;color:var(--text-secondary);">- Monto cedido</div>
                            <div style="font-size:14px;font-weight:700;color:var(--accent-danger);">
                                - Bs. {{ number_format($espejo->monto, 2) }}
                            </div>
                        </div>
                        <div style="font-size:20px;color:var(--text-secondary);align-self:center;">→</div>
                        <div>
                            <div style="font-size:10px;color:var(--text-secondary);">Saldo Posterior</div>
                            <div style="font-size:15px;font-weight:700;">Bs. {{ number_format($espejo->saldo_posterior, 2) }}</div>
                        </div>
                    </div>
                    <div style="margin-top:8px;">
                        <a href="{{ route('presupuesto.movimientos-partidas.show', $espejo) }}" style="font-size:11px;color:var(--accent);">
                            Ver movimiento espejo: {{ $espejo->numero }}
                        </a>
                    </div>
                </div>
                @endif

                @if($movimiento->estado === 'anulado' && $movimiento->motivo_anulacion)
                <div style="padding:12px 16px;background:rgba(220,53,69,0.07);border:1px solid rgba(220,53,69,0.2);border-radius:8px;">
                    <div style="font-size:11px;font-weight:700;color:var(--accent-danger);margin-bottom:4px;">MOTIVO DE ANULACIÓN</div>
                    <div style="font-size:13px;">{{ $movimiento->motivo_anulacion }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Panel lateral --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card" style="height:fit-content;">
            <div class="card-header"><div class="card-title">Información</div></div>
            <div class="card-body" style="padding:20px;display:flex;flex-direction:column;gap:12px;">
                @if($movimiento->cuentaBancaria)
                <div>
                    <div class="form-label"><i class="fa-solid fa-building-columns" style="color:var(--accent);margin-right:5px;"></i>Cuenta Bancaria</div>
                    <div style="font-size:13px;font-weight:600;">{{ $movimiento->cuentaBancaria->nombre }}</div>
                    <div style="font-size:11px;color:var(--text-secondary);">{{ $movimiento->cuentaBancaria->banco }}</div>
                </div>
                @endif
                @if($movimiento->ejercicioFiscal)
                <div>
                    <div class="form-label">Ejercicio Fiscal</div>
                    <div style="font-size:13px;font-weight:600;">{{ $movimiento->ejercicioFiscal->anio }}</div>
                </div>
                @endif
                <div>
                    <div class="form-label">Registrado por</div>
                    <div style="font-size:13px;">{{ $movimiento->creadoPor?->name ?? '—' }}</div>
                    <div style="font-size:11px;color:var(--text-secondary);">{{ $movimiento->created_at->format('d/m/Y H:i') }}</div>
                </div>

                @if($movimiento->estado !== 'anulado' && $movimiento->tipo !== 'modificacion_salida')
                <div class="nav-divider"></div>
                <form method="POST" action="{{ route('presupuesto.movimientos-partidas.anular', $movimiento) }}"
                    onsubmit="return confirm('¿Confirmas anular este movimiento? Los saldos serán revertidos.')">
                    @csrf
                    <div class="form-group" style="margin-bottom:10px;">
                        <label class="form-label">Motivo de anulación *</label>
                        <textarea name="motivo_anulacion" class="form-control" rows="2"
                            placeholder="Describe el motivo..." required minlength="5"></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger" style="width:100%;">
                        <i class="fa-solid fa-ban"></i> Anular Movimiento
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
