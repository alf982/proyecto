@extends('layouts.app')
@section('title', 'Nómina ' . $nomina->numero)
@section('breadcrumb')
    <a href="{{ route('nomina.nominas.index') }}">Nóminas</a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span class="current">{{ $nomina->numero }}</span>
@endsection
@section('content')
<div class="page-header fade-up">
    <h1 class="page-title">Nómina: <span style="color:var(--accent);">{{ $nomina->numero }}</span></h1>
    <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
        <span class="badge {{ $nomina->estadoBadge() }}" style="font-size:13px;padding:5px 14px;">{{ ucfirst($nomina->estado) }}</span>
        <a href="{{ route('pdf.nomina', $nomina) }}" target="_blank" class="btn btn-sm"
           style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);color:#ef4444;">
            <i class="fa-solid fa-file-pdf"></i> Exportar PDF
        </a>
        @if($nomina->estado === 'calculada')
        <form method="POST" action="{{ route('nomina.nominas.aprobar', $nomina) }}">@csrf<button class="btn btn-primary btn-sm">✓ Aprobar Nómina</button></form>
        @endif
        @if(!in_array($nomina->estado, ['pagada','anulada']))
        <form method="POST" action="{{ route('nomina.nominas.anular', $nomina) }}">@csrf<button class="btn btn-danger btn-sm" onclick="return confirm('¿Anular?')">Anular</button></form>
        @endif
    </div>
</div>

@include('components.alert')

{{-- Panel de pago con selector de partida --}}
@if($nomina->estado === 'aprobada')
<div class="card fade-up" style="margin-bottom:20px;border:1px solid rgba(34,211,166,0.3);background:rgba(34,211,166,0.04);">
    <div style="padding:1rem 1.5rem;border-bottom:1px solid rgba(34,211,166,0.2);">
        <h3 style="font-size:.9rem;color:var(--accent-3);display:flex;align-items:center;gap:8px;">
            <i class="fa-solid fa-money-bill-wave"></i> Procesar Pago de Nómina
        </h3>
    </div>
    <div style="padding:1.2rem">
        <form method="POST" action="{{ route('nomina.nominas.pagar', $nomina) }}" id="form-pagar-nomina">
            @csrf
            <div style="display:grid;grid-template-columns:1fr auto;gap:1rem;align-items:end">
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" style="font-size:.82rem;">
                        Partida Presupuestaria <span style="color:var(--accent-danger)">*</span>
                        <span style="font-size:11px;color:var(--text-muted);margin-left:6px;font-weight:400;">
                            Se descontará <strong>Bs. {{ number_format($nomina->total_neto, 2) }}</strong> del saldo disponible
                        </span>
                    </label>
                    <select name="partida_presupuestaria_id" id="sel-partida-pago" class="form-control" onchange="actualizarSaldoPago(this)" required>
                        <option value="">— Seleccione una partida —</option>
                        @foreach($partidas as $p)
                        <option value="{{ $p->id }}"
                            data-saldo="{{ $p->saldo_actual }}"
                            data-codigo="{{ $p->codigo }}"
                            {{ (old('partida_presupuestaria_id', $nomina->partida_presupuestaria_id) == $p->id) ? 'selected' : '' }}>
                            {{ $p->codigo }} — {{ Str::limit($p->descripcion, 55) }}
                            (Bs. {{ number_format($p->saldo_actual, 2) }} disponible)
                        </option>
                        @endforeach
                    </select>
                    @error('partida_presupuestaria_id')
                    <div class="form-error">{{ $message }}</div>
                    @enderror

                    {{-- Info de saldo dinámico --}}
                    <div id="panel-saldo-pago" style="display:none;margin-top:8px;padding:8px 12px;border-radius:6px;font-size:.8rem;display:none;">
                        <div style="display:flex;gap:20px;flex-wrap:wrap;">
                            <span>Saldo disponible: <strong id="info-saldo-pago" style="color:var(--accent-3);font-family:monospace;"></strong></span>
                            <span>Saldo tras pago: <strong id="info-saldo-post" style="font-family:monospace;"></strong></span>
                        </div>
                        <div id="alerta-insuficiente" style="display:none;color:var(--accent-danger);margin-top:4px;font-weight:600;">
                            ⚠ Saldo insuficiente en la partida seleccionada
                        </div>
                    </div>
                </div>
                <div>
                    <button type="submit" id="btn-pagar" class="btn btn-primary"
                        style="background:linear-gradient(135deg,var(--accent-3),#1aaa88);white-space:nowrap;"
                        onclick="return confirm('¿Confirmar pago de la nómina {{ $nomina->numero }}? Se descontarán Bs. {{ number_format($nomina->total_neto, 2) }} de la partida seleccionada.')">
                        💳 Confirmar Pago
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Info partida si ya fue pagada --}}
@if($nomina->estado === 'pagada' && $nomina->partida)
<div style="margin-bottom:20px;padding:10px 16px;background:rgba(34,211,166,0.06);border:1px solid rgba(34,211,166,0.25);border-radius:8px;font-size:.85rem;display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
    <i class="fa-solid fa-circle-check" style="color:var(--accent-3);font-size:1.1rem;"></i>
    <div>
        <span style="color:var(--text-muted);">Pagada con cargo a:</span>
        <code style="color:var(--accent);margin-left:6px;font-weight:700;">{{ $nomina->partida->codigo }}</code>
        <span style="color:var(--text-secondary);margin-left:6px;">{{ $nomina->partida->descripcion }}</span>
    </div>
    <div style="margin-left:auto;font-weight:700;color:var(--accent-danger);font-family:monospace;">
        − Bs. {{ number_format($nomina->total_neto, 2) }}
    </div>
</div>
@endif

{{-- KPI cards --}}
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:20px;">
    <div class="card fade-up" style="padding:18px;">
        <div style="font-size:11px;color:var(--text-secondary);">TOTAL ASIGNACIONES</div>
        <div style="font-size:22px;font-weight:800;color:var(--accent-3);">Bs. {{ number_format($nomina->total_asignaciones,2) }}</div>
    </div>
    <div class="card fade-up" style="padding:18px;animation-delay:.05s">
        <div style="font-size:11px;color:var(--text-secondary);">TOTAL DEDUCCIONES</div>
        <div style="font-size:22px;font-weight:800;color:var(--accent-danger);">Bs. {{ number_format($nomina->total_deducciones,2) }}</div>
    </div>
    <div class="card fade-up" style="padding:18px;animation-delay:.1s;border-color:rgba(34,211,166,0.3);">
        <div style="font-size:11px;color:var(--text-secondary);">NETO A PAGAR</div>
        <div style="font-size:22px;font-weight:800;color:var(--accent-3);">Bs. {{ number_format($nomina->total_neto,2) }}</div>
    </div>
</div>

<div class="card fade-up" style="animation-delay:.15s">
    <div class="card-header">
        <div class="card-title">Detalle por Empleado ({{ $nomina->detalles->count() }} registros)</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Empleado</th><th>Cargo</th><th>Sueldo Base</th><th>Asignaciones</th><th>Deducciones</th><th>Neto</th><th></th></tr></thead>
            <tbody>
            @foreach($nomina->detalles as $det)
            <tr>
                <td>
                    <div style="font-weight:600;">{{ $det->empleado->nombre_completo }}</div>
                    <div style="font-size:11px;color:var(--text-secondary);">{{ $det->empleado->cedula }}</div>
                </td>
                <td style="font-size:12px;">{{ $det->empleado->cargo->nombre ?? '—' }}</td>
                <td>Bs. {{ number_format($det->salario_base,2) }}</td>
                <td style="color:var(--accent-3);">Bs. {{ number_format($det->total_asignaciones,2) }}</td>
                <td style="color:var(--accent-danger);">Bs. {{ number_format($det->total_deducciones,2) }}</td>
                <td style="font-weight:700;color:var(--accent-3);">Bs. {{ number_format($det->neto,2) }}</td>
                <td></td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
const montoNomina = {{ $nomina->total_neto }};

function actualizarSaldoPago(sel) {
    const opt    = sel.options[sel.selectedIndex];
    const panel  = document.getElementById('panel-saldo-pago');
    const btnPagar = document.getElementById('btn-pagar');

    if (!sel.value) {
        panel.style.display = 'none';
        return;
    }

    const saldo    = parseFloat(opt.dataset.saldo || 0);
    const saldoPost = saldo - montoNomina;

    document.getElementById('info-saldo-pago').textContent = 'Bs. ' + saldo.toLocaleString('es-VE', {minimumFractionDigits:2});
    document.getElementById('info-saldo-post').textContent = 'Bs. ' + Math.max(0, saldoPost).toLocaleString('es-VE', {minimumFractionDigits:2});

    const insuficiente = saldo < montoNomina;
    document.getElementById('alerta-insuficiente').style.display = insuficiente ? 'block' : 'none';
    document.getElementById('info-saldo-post').style.color = insuficiente ? 'var(--accent-danger)' : 'var(--accent-3)';
    panel.style.display = 'block';

    if (btnPagar) btnPagar.disabled = insuficiente;
}

// Init si old() tenía valor
window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('sel-partida-pago');
    if (sel?.value) actualizarSaldoPago(sel);
});
</script>
@endsection
