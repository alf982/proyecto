<?php

namespace App\Http\Controllers\Presupuesto;

use App\Http\Controllers\Controller;
use App\Models\CuentaBancaria;
use App\Models\EjercicioFiscal;
use App\Models\MovimientoPartida;
use App\Models\PartidaPresupuestaria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controlador de Movimientos de Partidas Presupuestarias
 * 
 * Gestiona el registro y anulación de modificaciones al presupuesto ordinario.
 * Soporta operaciones de:
 * - Asignación (Presupuesto inicial)
 * - Créditos Adicionales (Incremento de fondos)
 * - Traspasos (Modificaciones de entrada/salida entre partidas)
 * 
 * Implementa una arquitectura transaccional (DB::transaction) para asegurar 
 * que la contabilidad de partida doble (doble afectación) no genere inconsistencias.
 */
class MovimientoPartidaController extends Controller
{
    // ── Listado ───────────────────────────────────────────────────
    
    /**
     * Muestra el libro mayor de movimientos presupuestarios.
     * Filtra automáticamente por el Ejercicio Fiscal activo en sesión.
     */
    public function index(Request $request)
    {
        $ejercicioId = session('ejercicio_id');

        // Carga ambiciosa para optimizar el renderizado del historial
        $movimientos = MovimientoPartida::with(['partida', 'contrapartida', 'creadoPor'])
            ->when($ejercicioId, fn($q) => $q->where('ejercicio_fiscal_id', $ejercicioId))
            ->when($request->partida,     fn($q, $v) => $q->where('partida_presupuestaria_id', $v))
            ->when($request->tipo,        fn($q, $v) => $q->where('tipo', $v))
            ->when($request->estado,      fn($q, $v) => $q->where('estado', $v))
            ->when($request->fecha_desde, fn($q, $v) => $q->where('fecha_movimiento', '>=', $v))
            ->when($request->fecha_hasta, fn($q, $v) => $q->where('fecha_movimiento', '<=', $v))
            ->when($request->search,      fn($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('numero', 'like', "%$v%")
                  ->orWhere('concepto', 'like', "%$v%")
                  ->orWhere('referencia', 'like', "%$v%");
            }))
            ->orderByDesc('fecha_movimiento')
            ->orderByDesc('id')
            ->paginate(20)->withQueryString();

        $partidas = PartidaPresupuestaria::activas()->orderBy('codigo')->get();

        return view('presupuesto.movimientos-partidas.index', compact('movimientos', 'partidas'));
    }

    // ── Formulario nuevo ─────────────────────────────────────────
    
    /**
     * Muestra el formulario para registrar un nuevo movimiento.
     */
    public function create(Request $request)
    {
        $partidas   = PartidaPresupuestaria::activas()->orderBy('codigo')->get();
        $ejercicios = EjercicioFiscal::orderByDesc('anio')->get();
        $cuentas    = CuentaBancaria::activas()->orderBy('nombre')->get();
        $partidaSeleccionada = $request->partida
            ? PartidaPresupuestaria::find($request->partida)
            : null;

        return view('presupuesto.movimientos-partidas.create',
            compact('partidas', 'ejercicios', 'cuentas', 'partidaSeleccionada'));
    }

    // ── Guardar ───────────────────────────────────────────────────
    
    /**
     * Almacena y procesa matemáticamente un movimiento presupuestario.
     * 
     * Lógica Crítica:
     * 1. Bloquea las filas involucradas (lockForUpdate) para evitar race conditions.
     * 2. Recalcula `saldo_actual`, `monto_aprobado` y `monto_vigente` según el tipo.
     * 3. Si es un traspaso (`modificacion_entrada`), genera automáticamente el 
     *    movimiento espejo (`modificacion_salida`) deduciendo los fondos de la contrapartida.
     * 4. Dispara la sincronización del saldo físico de las Cuentas Bancarias vinculadas.
     */
    public function store(Request $request)
    {
        $request->validate([
            'partida_presupuestaria_id'  => 'required|exists:partidas_presupuestarias,id',
            'tipo'                       => 'required|in:asignacion,credito_adicional,modificacion_entrada,ejecucion,reintegro,nota_credito,nota_debito',
            'concepto'                   => 'required|string|max:300',
            'monto'                      => 'required|numeric|min:0.01',
            'fecha_movimiento'           => 'required|date',
            'referencia'                 => 'nullable|string|max:100',
            'cuenta_bancaria_id'         => 'nullable|exists:cuentas_bancarias,id',
            'ejercicio_fiscal_id'        => 'nullable|exists:ejercicios_fiscales,id',
            'observaciones'              => 'nullable|string',
            // Solo requerido para modificación (Traspasos entre cuentas)
            'partida_contrapartida_id'   => 'required_if:tipo,modificacion_entrada|nullable|exists:partidas_presupuestarias,id|different:partida_presupuestaria_id',
        ]);

        DB::transaction(function () use ($request) {
            $partida = PartidaPresupuestaria::lockForUpdate()->findOrFail($request->partida_presupuestaria_id);
            $esIngreso = in_array($request->tipo, MovimientoPartida::TIPOS_INGRESO);

            $saldoAnt  = (float) $partida->saldo_actual;
            $saldoPost = $esIngreso ? $saldoAnt + $request->monto : $saldoAnt - $request->monto;

            // 1. Registro del Movimiento Principal
            $mov = MovimientoPartida::create([
                'numero'                    => MovimientoPartida::generarNumero(now()->year),
                'partida_presupuestaria_id' => $partida->id,
                'partida_contrapartida_id'  => $request->partida_contrapartida_id ?? null,
                'cuenta_bancaria_id'        => $request->cuenta_bancaria_id ?? $partida->cuenta_bancaria_id,
                'ejercicio_fiscal_id'       => $request->ejercicio_fiscal_id ?? session('ejercicio_id'),
                'tipo'                      => $request->tipo,
                'concepto'                  => $request->concepto,
                'monto'                     => $request->monto,
                'fecha_movimiento'          => $request->fecha_movimiento,
                'referencia'                => $request->referencia,
                'saldo_anterior'            => $saldoAnt,
                'saldo_posterior'           => $saldoPost,
                'estado'                    => 'confirmado',
                'observaciones'             => $request->observaciones,
                'creado_por'                => auth()->id(),
            ]);

            $partida->update(['saldo_actual' => $saldoPost]);

            // ── monto_aprobado: solo sube con 'asignacion' (techo original) ──
            if ($request->tipo === 'asignacion') {
                $partida->increment('monto_aprobado', (float) $request->monto);
            }

            // ── monto_vigente: aprobado +- modificaciones presupuestarias formales ──
            // Tipos que aumentan vigente: asignacion, credito_adicional, modificacion_entrada
            // Tipos que reducen vigente:  modificacion_salida
            $tiposAumentanVigente = ['asignacion', 'credito_adicional', 'modificacion_entrada'];
            if (in_array($request->tipo, $tiposAumentanVigente)) {
                $partida->increment('monto_vigente', (float) $request->monto);
            } elseif ($request->tipo === 'modificacion_salida') {
                $partida->decrement('monto_vigente', (float) $request->monto);
            }
            // reintegro, nota_credito, ejecucion, nota_debito, compromiso, causacion, pago
            // → solo afectan saldo_actual, NO modifican monto_vigente

            // ── Sincronizar saldo de la cuenta bancaria vinculada ──
            if ($partida->cuenta_bancaria_id) {
                $partida->cuentaBancaria->recalcularSaldo();
            }

            // ── Doble afectación para modificaciones (Traspasos) ──
            // Si entra dinero a esta partida, debe salir de la contrapartida.
            if ($request->tipo === 'modificacion_entrada') {
                $contra  = PartidaPresupuestaria::lockForUpdate()->findOrFail($request->partida_contrapartida_id);
                $saldoAntC  = (float) $contra->saldo_actual;
                $saldoPostC = $saldoAntC - $request->monto;  // la contrapartida CEDE el monto

                // Generar movimiento espejo
                $movEspejo = MovimientoPartida::create([
                    'numero'                    => MovimientoPartida::generarNumero(now()->year),
                    'partida_presupuestaria_id' => $contra->id,
                    'partida_contrapartida_id'  => $partida->id,
                    'movimiento_relacionado_id' => $mov->id,
                    'cuenta_bancaria_id'        => $contra->cuenta_bancaria_id,
                    'ejercicio_fiscal_id'       => $request->ejercicio_fiscal_id ?? session('ejercicio_id'),
                    'tipo'                      => 'modificacion_salida',
                    'concepto'                  => $request->concepto . ' [Contrapartida]',
                    'monto'                     => $request->monto,
                    'fecha_movimiento'          => $request->fecha_movimiento,
                    'referencia'                => $request->referencia,
                    'saldo_anterior'            => $saldoAntC,
                    'saldo_posterior'           => $saldoPostC,
                    'estado'                    => 'confirmado',
                    'observaciones'             => $request->observaciones,
                    'creado_por'                => auth()->id(),
                ]);

                $contra->update(['saldo_actual' => $saldoPostC]);

                // ── Sincronizar cuenta bancaria de la contrapartida ──
                if ($contra->cuenta_bancaria_id) {
                    $contra->cuentaBancaria->recalcularSaldo();
                }
                
                // Enlazar bidireccionalmente para anulaciones conjuntas
                $mov->update(['movimiento_relacionado_id' => $movEspejo->id]);
            }
        });

        return redirect()->route('presupuesto.movimientos-partidas.index')
            ->with('success', 'Movimiento registrado correctamente.');
    }

    // ── Detalle ───────────────────────────────────────────────────
    
    /**
     * Muestra la vista de auditoría y detalle de un movimiento específico.
     */
    public function show(MovimientoPartida $movimiento)
    {
        $movimiento->load(['partida', 'contrapartida', 'movimientoRelacionado.partida', 'cuentaBancaria', 'ejercicioFiscal', 'creadoPor']);
        return view('presupuesto.movimientos-partidas.show', compact('movimiento'));
    }

    // ── Anular ────────────────────────────────────────────────────
    
    /**
     * Anula un movimiento de forma segura y controlada (Rollback Financiero).
     * 
     * Revierte los saldos restando/sumando a las partidas afectadas.
     * Si el movimiento es un traspaso, anula también automáticamente el
     * movimiento "espejo" para mantener la consistencia contable.
     */
    public function anular(Request $request, MovimientoPartida $movimiento)
    {
        if ($movimiento->estado === 'anulado') {
            return back()->with('error', 'Este movimiento ya está anulado.');
        }
        
        // Bloquear anulación directa de la salida en traspasos (forzar a hacerlo desde la entrada)
        if ($movimiento->tipo === 'modificacion_salida') {
            return back()->with('error', 'Anula el movimiento de entrada relacionado para revertir ambos.');
        }

        $request->validate(['motivo_anulacion' => 'required|string|min:5']);

        DB::transaction(function () use ($request, $movimiento) {
            // Revertir saldo de la partida principal
            $partida = PartidaPresupuestaria::lockForUpdate()->findOrFail($movimiento->partida_presupuestaria_id);
            $nuevoSaldo = $movimiento->esIngreso()
                ? (float) $partida->saldo_actual - (float) $movimiento->monto
                : (float) $partida->saldo_actual + (float) $movimiento->monto;
            $partida->update(['saldo_actual' => $nuevoSaldo]);

            // Al anular, revertir monto_aprobado y monto_vigente según tipo
            if ($movimiento->tipo === 'asignacion') {
                $nuevoAprobado = max(0, (float) $partida->monto_aprobado - (float) $movimiento->monto);
                $partida->update(['monto_aprobado' => $nuevoAprobado]);
            }

            $tiposAumentanVigente = ['asignacion', 'credito_adicional', 'modificacion_entrada'];
            if (in_array($movimiento->tipo, $tiposAumentanVigente)) {
                $nuevoVigente = max(0, (float) $partida->monto_vigente - (float) $movimiento->monto);
                $partida->update(['monto_vigente' => $nuevoVigente]);
            } elseif ($movimiento->tipo === 'modificacion_salida') {
                // Al anular la salida, el vigente vuelve a subir
                $partida->increment('monto_vigente', (float) $movimiento->monto);
            }

            // ── Sincronizar cuenta bancaria de la partida principal ──
            if ($partida->cuenta_bancaria_id) {
                $partida->cuentaBancaria->recalcularSaldo();
            }

            $movimiento->update([
                'estado'           => 'anulado',
                'motivo_anulacion' => $request->motivo_anulacion,
            ]);

            // Si tiene movimiento espejo (Traspaso), anularlo también automáticamente
            if ($movimiento->movimiento_relacionado_id) {
                $espejo = MovimientoPartida::find($movimiento->movimiento_relacionado_id);
                if ($espejo && $espejo->estado !== 'anulado') {
                    $contrapartida = PartidaPresupuestaria::lockForUpdate()->findOrFail($espejo->partida_presupuestaria_id);
                    $nuevoSaldoC = $espejo->esIngreso()
                        ? (float) $contrapartida->saldo_actual - (float) $espejo->monto
                        : (float) $contrapartida->saldo_actual + (float) $espejo->monto;
                    $contrapartida->update(['saldo_actual' => $nuevoSaldoC]);

                    // ── Sincronizar cuenta bancaria de la contrapartida anulada ──
                    if ($contrapartida->cuenta_bancaria_id) {
                        $contrapartida->cuentaBancaria->recalcularSaldo();
                    }
                    $espejo->update([
                        'estado'           => 'anulado',
                        'motivo_anulacion' => 'Anulado automáticamente por anulación del movimiento ' . $movimiento->numero,
                    ]);
                }
            }
        });

        return back()->with('success', 'Movimiento anulado y saldos revertidos.');
    }
}
