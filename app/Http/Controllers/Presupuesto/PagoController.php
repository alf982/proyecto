<?php
namespace App\Http\Controllers\Presupuesto;

use App\Http\Controllers\Controller;
use App\Models\Causacion;
use App\Models\EjercicioFiscal;
use App\Models\OrdenPago;
use App\Models\Pago;
use App\Models\UnidadEjecutora;
use App\Traits\GuardaRetenciones;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller implements HasMiddleware
{
    use GuardaRetenciones;
    public static function middleware(): array
    {
        return [
            new Middleware('can:pagos.ver', only: ['index', 'show']),
            new Middleware('can:pagos.crear', only: ['create', 'store']),
            new Middleware('can:pagos.procesar', only: ['procesar']),
            new Middleware('can:pagos.anular', only: ['anular']),
        ];
    }

    // ── LISTADO ──────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $q = Pago::with(['causacion', 'ejercicioFiscal', 'unidadEjecutora'])
            ->when($request->ejercicio, fn($q, $v) => $q->where('ejercicio_fiscal_id', $v))
            ->when($request->estado, fn($q, $v) => $q->where('estado', $v))
            ->when($request->tipo, fn($q, $v) => $q->where('tipo_pago', $v))
            ->when($request->search, fn($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('numero', 'like', "%$v%")
                    ->orWhere('beneficiario', 'like', "%$v%")
                    ->orWhere('numero_referencia', 'like', "%$v%");
            }))
            ->orderByDesc('numero')
            ->paginate(20)->withQueryString();

        $ejercicios = EjercicioFiscal::orderByDesc('anio')->get();
        $pendientes = Pago::where('estado', 'pendiente')->count();

        // Causaciones aprobadas que aún no tienen pago registrado
        $causacionesPendientes = Causacion::where('estado', 'aprobada')
            ->orderByDesc('id')
            ->get();

        return view('presupuesto.pagos.index', compact('q', 'ejercicios', 'pendientes', 'causacionesPendientes'));
    }

    // ── CREAR ─────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        // Causaciones aprobadas que aún no tienen pago procesado
        $causaciones = Causacion::where('estado', 'aprobada')
            ->with(['unidadEjecutora', 'partida', 'compromiso.beneficiarioModel'])
            ->orderByDesc('id')
            ->get();

        // Pre-selección desde causacion show
        $causacion = $request->causacion_id
            ? Causacion::with(['ejercicioFiscal', 'unidadEjecutora', 'partida', 'compromiso.beneficiarioModel'])->find($request->causacion_id)
            : null;

        $ejercicio = EjercicioFiscal::where('estado', 'activo')->first();

        return view('presupuesto.pagos.create', compact('causaciones', 'causacion', 'ejercicio'));
    }

    // ── GUARDAR ───────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'causacion_id' => 'required|exists:causaciones,id',
            'tipo_pago' => 'required|in:cheque,transferencia,efectivo,otro',
            'numero_referencia' => 'nullable|string|max:60',
            'banco' => 'nullable|string|max:100',
            'cuenta_bancaria' => 'nullable|string|max:30',
            'monto_pagado' => 'required|numeric|min:0.01',
            'monto_sin_iva' => 'nullable|numeric|min:0',
            'fecha_pago' => 'required|date',
            'concepto' => 'required|string|max:500',
            'observaciones' => 'nullable|string',
            'retenciones' => 'nullable|array',
            'retenciones.*' => 'integer|exists:retenciones,id',
        ]);

        $idsRetenciones = $request->input('retenciones', []);

        $causacion = Causacion::with('partida')->findOrFail($request->causacion_id);

        if ($causacion->estado !== 'aprobada') {
            return back()->withErrors(['causacion_id' => 'Solo se pueden pagar causaciones en estado Aprobada.'])->withInput();
        }

        // Base neta para ISLR: viene del form (monto_sin_iva del compromiso/causación)
        // Si no se envía, usamos el total con IVA (comportamiento legado)
        $montoTotal = (float) $request->monto_pagado;
        $montoSinIva = $request->filled('monto_sin_iva') ? (float) $request->monto_sin_iva : null;

        $totalRetenciones = $this->calcularTotalRetenciones($idsRetenciones, $montoTotal, $montoSinIva);
        $montoNeto = round($montoTotal - $totalRetenciones, 2);

        DB::transaction(function () use ($request, $causacion, $idsRetenciones, $totalRetenciones, $montoTotal, $montoSinIva, $montoNeto) {
            // 1. Crear el pago
            $pago = Pago::create([
                'numero' => Pago::generarNumero(now()->year),
                'causacion_id' => $causacion->id,
                'ejercicio_fiscal_id' => $causacion->ejercicio_fiscal_id,
                'unidad_ejecutora_id' => $causacion->unidad_ejecutora_id,
                'beneficiario' => $causacion->beneficiario,
                'rif_beneficiario' => $causacion->rif_beneficiario,
                'tipo_pago' => $request->tipo_pago,
                'numero_referencia' => $request->numero_referencia,
                'banco' => $request->banco,
                'cuenta_bancaria' => $request->cuenta_bancaria,
                'monto_pagado' => $montoNeto,
                'fecha_pago' => $request->fecha_pago,
                'concepto' => $request->concepto,
                'estado' => 'procesado',
                'observaciones' => $request->observaciones,
                'created_by' => auth()->id(),
            ]);

            // 2. Guardar retenciones con las dos bases correctas
            $this->guardarRetenciones($pago, $idsRetenciones, $montoTotal, $montoSinIva);

            // NOTA: El saldo de la partida YA fue descontado cuando se registró el compromiso.
            // El pago no vuelve a descontarlo — solo registra que el dinero fue erogado.

            // 3. Marcar la causación como pagada
            $causacion->update([
                'estado' => 'pagada',
                'fecha_pago' => $request->fecha_pago,
                'monto_retencion' => $totalRetenciones,
            ]);
        });

        return redirect()->route('presupuesto.pagos.index')
            ->with('success', 'Pago registrado y partida presupuestaria actualizada correctamente.');
    }

    // ── DETALLE ───────────────────────────────────────────────────────
    public function show(Pago $pago)
    {
        $pago->load(['causacion.partida', 'causacion.unidadEjecutora', 'ejercicioFiscal', 'unidadEjecutora', 'creadoPor', 'retenciones.retencion']);
        return view('presupuesto.pagos.show', compact('pago'));
    }

    // ── PROCESAR (para pagos en pendiente) ────────────────────────────
    public function procesar(Request $request, Pago $pago)
    {
        if (!$pago->esPendiente()) {
            return back()->with('error', 'Solo se procesan pagos en estado Pendiente.');
        }

        $request->validate([
            'numero_referencia' => 'nullable|string|max:60',
            'banco' => 'nullable|string|max:100',
            'cuenta_bancaria' => 'nullable|string|max:30',
            'fecha_pago' => 'required|date',
        ]);

        DB::transaction(function () use ($request, $pago) {
            $pago->update([
                'estado' => 'procesado',
                'numero_referencia' => $request->numero_referencia,
                'banco' => $request->banco,
                'cuenta_bancaria' => $request->cuenta_bancaria,
                'fecha_pago' => $request->fecha_pago,
            ]);

            $causacion = Causacion::find($pago->causacion_id);
            // NOTA: El saldo ya fue descontado en el compromiso, no volver a descontarlo.
            if ($causacion) {
                $causacion->update(['estado' => 'pagada', 'fecha_pago' => $request->fecha_pago]);
            }
            if ($pago->orden_pago_id) {
                OrdenPago::where('id', $pago->orden_pago_id)->update([
                    'estado' => 'pagada',
                    'numero_referencia' => $request->numero_referencia,
                    'banco' => $request->banco,
                    'cuenta_bancaria_num' => $request->cuenta_bancaria,
                    'fecha_pago' => $request->fecha_pago,
                ]);
            }
        });

        return redirect()->route('presupuesto.pagos.show', $pago)
            ->with('success', 'Pago procesado. El saldo de la partida fue actualizado.');
    }

    // ── ANULAR ────────────────────────────────────────────────────────
    public function anular(Request $request, Pago $pago)
    {
        if ($pago->esAnulado()) {
            return back()->with('error', 'El pago ya está anulado.');
        }
        $request->validate(['motivo_anulacion' => 'required|string|min:10']);

        DB::transaction(function () use ($request, $pago) {
            $pago->update(['estado' => 'anulado', 'motivo_anulacion' => $request->motivo_anulacion]);

            // Revertir causación a aprobada para que pueda volver a pagarse
            $causacion = Causacion::find($pago->causacion_id);
            if ($causacion) {
                $causacion->update(['estado' => 'aprobada', 'fecha_pago' => null]);
            }
            // NOTA: El saldo de la partida fué reservado por el compromiso.
            // No lo devolvemos aquí — el saldo se libera solo al anular el compromiso.
        });

        return redirect()->route('presupuesto.pagos.index')
            ->with('success', 'Pago anulado y causación revertida a Aprobada.');
    }
}
