<?php

namespace App\Http\Controllers\Presupuesto;

use App\Http\Controllers\Controller;
use App\Models\EjercicioFiscal;
use App\Models\FuenteFinanciamiento;
use App\Models\MovimientoPartida;
use App\Models\PartidaPresupuestaria;
use App\Models\ProyectoSia;
use App\Models\Actividad;
use App\Models\CreditoPresupuestario;
use App\Models\UnidadEjecutora;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class CreditoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:creditos.ver', only: ['index', 'show']),
            new Middleware('can:creditos.crear', only: ['create', 'store']),
            new Middleware('can:creditos.editar', only: ['edit', 'update']),
            new Middleware('can:creditos.editar', only: ['destroy']),
        ];
    }
    public function index(Request $request)
    {
        $ejercicioActivo = EjercicioFiscal::where('estado', 'activo')->first();
        $ejercicioId     = $request->ejercicio_id ?? $ejercicioActivo?->id;

        $creditos = CreditoPresupuestario::with(['partida', 'unidadEjecutora', 'fuenteFinanciamiento', 'ejercicioFiscal'])
            ->when($ejercicioId, fn($q) => $q->where('ejercicio_fiscal_id', $ejercicioId))
            ->orderBy('created_at', 'desc')
            ->paginate(20)->withQueryString();

        $ejercicios = EjercicioFiscal::orderByDesc('anio')->get();
        $ejercicioSeleccionado = $ejercicioId ? EjercicioFiscal::find($ejercicioId) : null;

        return view('presupuesto.creditos.index', compact('creditos', 'ejercicios', 'ejercicioSeleccionado'));
    }

    public function create()
    {
        $ejercicios = EjercicioFiscal::whereIn('estado', ['borrador', 'activo'])->orderByDesc('anio')->get();
        $partidas   = PartidaPresupuestaria::activas()->orderBy('codigo')->get();
        $unidades   = UnidadEjecutora::activas()->orderBy('nombre')->get();
        $fuentes    = FuenteFinanciamiento::activas()->orderBy('nombre')->get();
        $proyectos  = ProyectoSia::with('ejercicioFiscal')->orderBy('codigo')->get();

        return view('presupuesto.creditos.create', compact('ejercicios', 'partidas', 'unidades', 'fuentes', 'proyectos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ejercicio_fiscal_id'       => 'required|exists:ejercicios_fiscales,id',
            'partida_presupuestaria_id' => 'required|exists:partidas_presupuestarias,id',
            'unidad_ejecutora_id'       => 'required|exists:unidades_ejecutoras,id',
            'fuente_financiamiento_id'  => 'nullable|exists:fuentes_financiamiento,id',
            'proyecto_sia_id'           => 'nullable|exists:proyectos_sia,id',
            'actividad_id'              => 'nullable|exists:actividades,id',
            'monto_aprobado'            => 'required|numeric|min:0',
            'observaciones'             => 'nullable|string',
        ]);

        $data['creado_por']        = auth()->id();
        $data['monto_modificado']  = 0;
        $data['monto_comprometido']= 0;
        $data['monto_causado']     = 0;
        $data['monto_pagado']      = 0;

        DB::transaction(function () use ($data) {
            $credito = CreditoPresupuestario::create($data);

            // ── Generar MovimientoPartida de asignación inicial ──
            $partida = PartidaPresupuestaria::lockForUpdate()->find($credito->partida_presupuestaria_id);
            if ($partida && $credito->monto_aprobado > 0) {
                $saldoAnt  = (float) $partida->saldo_actual;
                $saldoPost = $saldoAnt + (float) $credito->monto_aprobado;

                MovimientoPartida::create([
                    'numero'                    => MovimientoPartida::generarNumero(now()->year),
                    'partida_presupuestaria_id' => $partida->id,
                    'cuenta_bancaria_id'        => $partida->cuenta_bancaria_id,
                    'ejercicio_fiscal_id'       => $credito->ejercicio_fiscal_id,
                    'tipo'                      => 'asignacion',
                    'concepto'                  => 'Asignación inicial — Crédito presupuestario #' . $credito->id
                                                   . ' • ' . now()->format('Y'),
                    'monto'                     => $credito->monto_aprobado,
                    'fecha_movimiento'          => now()->toDateString(),
                    'referencia'                => 'CRED-' . str_pad($credito->id, 5, '0', STR_PAD_LEFT),
                    'saldo_anterior'            => $saldoAnt,
                    'saldo_posterior'           => $saldoPost,
                    'estado'                    => 'confirmado',
                    'observaciones'             => 'Generado automáticamente al registrar el crédito presupuestario.',
                    'creado_por'                => auth()->id(),
                ]);

                $partida->update([
                    'saldo_actual'    => $saldoPost,
                    // Acumula monto_aprobado (puede haber varios créditos sobre la misma partida)
                    'monto_aprobado'  => (float)$partida->monto_aprobado + (float)$credito->monto_aprobado,
                ]);

                // Sincronizar saldo de cuenta bancaria vinculada
                if ($partida->cuenta_bancaria_id) {
                    $partida->cuentaBancaria->recalcularSaldo();
                }
            }
        });

        return redirect()->route('presupuesto.creditos.index')
            ->with('success', 'Crédito presupuestario registrado. Se generó el movimiento de asignación en la partida.');
    }

    public function show(CreditoPresupuestario $credito)
    {
        $credito->load([
            'partida.movimientos' => fn($q) => $q->orderByDesc('fecha_movimiento')->limit(15),
            'ejercicioFiscal',
            'unidadEjecutora',
            'fuenteFinanciamiento',
            'creadoPor',
        ]);
        return view('presupuesto.creditos.show', compact('credito'));
    }

    public function edit(CreditoPresupuestario $credito)
    {
        $ejercicios = EjercicioFiscal::whereIn('estado', ['borrador', 'activo'])->orderByDesc('anio')->get();
        $partidas   = PartidaPresupuestaria::activas()->orderBy('codigo')->get();
        $unidades   = UnidadEjecutora::activas()->orderBy('nombre')->get();
        $fuentes    = FuenteFinanciamiento::activas()->orderBy('nombre')->get();
        $proyectos  = ProyectoSia::orderBy('codigo')->get();

        return view('presupuesto.creditos.edit', compact('credito', 'ejercicios', 'partidas', 'unidades', 'fuentes', 'proyectos'));
    }

    public function update(Request $request, CreditoPresupuestario $credito)
    {
        $data = $request->validate([
            'monto_aprobado'  => 'required|numeric|min:0',
            'observaciones'   => 'nullable|string',
        ]);

        $montoAnterior = (float) $credito->monto_aprobado;
        $montoNuevo    = (float) $request->monto_aprobado;
        $diferencia    = $montoNuevo - $montoAnterior;

        DB::transaction(function () use ($data, $credito, $diferencia) {
            $credito->update($data);

            // Si cambió el monto aprobado, generar movimiento de ajuste en la partida
            if (abs($diferencia) >= 0.01) {
                $partida = PartidaPresupuestaria::lockForUpdate()->find($credito->partida_presupuestaria_id);
                if ($partida) {
                    $saldoAnt  = (float) $partida->saldo_actual;
                    $esAumento = $diferencia > 0;
                    $saldoPost = $saldoAnt + $diferencia;

                    MovimientoPartida::create([
                        'numero'                    => MovimientoPartida::generarNumero(now()->year),
                        'partida_presupuestaria_id' => $partida->id,
                        'cuenta_bancaria_id'        => $partida->cuenta_bancaria_id,
                        'ejercicio_fiscal_id'       => $credito->ejercicio_fiscal_id,
                        'tipo'                      => $esAumento ? 'credito_adicional' : 'nota_debito',
                        'concepto'                  => ($esAumento ? 'Incremento' : 'Reducción') . ' de crédito presupuestario #' . $credito->id,
                        'monto'                     => abs($diferencia),
                        'fecha_movimiento'          => now()->toDateString(),
                        'referencia'                => 'CRED-' . str_pad($credito->id, 5, '0', STR_PAD_LEFT) . '-MOD',
                        'saldo_anterior'            => $saldoAnt,
                        'saldo_posterior'           => $saldoPost,
                        'estado'                    => 'confirmado',
                        'observaciones'             => 'Ajuste automático por edición del crédito presupuestario.',
                        'creado_por'                => auth()->id(),
                    ]);

                    $partida->update([
                        'saldo_actual'   => $saldoPost,
                        // monto_aprobado solo sube si el crédito aumentó
                        'monto_aprobado' => (float)$partida->monto_aprobado + ($diferencia > 0 ? $diferencia : 0),
                    ]);

                    if ($partida->cuenta_bancaria_id) {
                        $partida->cuentaBancaria->recalcularSaldo();
                    }
                }
            }
        });

        return redirect()->route('presupuesto.creditos.index')
            ->with('success', 'Crédito presupuestario actualizado.' . (abs($diferencia) >= 0.01 ? ' Se registró el ajuste en la partida.' : ''));
    }

    public function destroy(CreditoPresupuestario $credito)
    {
        if ($credito->monto_comprometido > 0) {
            return back()->with('error', 'No se puede eliminar un crédito con compromisos registrados.');
        }
        $credito->delete();
        return redirect()->route('presupuesto.creditos.index')
            ->with('success', 'Crédito presupuestario eliminado.');
    }
}
