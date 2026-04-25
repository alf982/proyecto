<?php
namespace App\Http\Controllers\Tesoreria;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tesoreria\StoreOrdenPagoRequest;
use App\Models\Beneficiario;
use App\Models\Causacion;
use App\Models\EjercicioFiscal;
use App\Models\OrdenPago;
use App\Models\OrdenPagoDetalle;
use App\Models\Pago;
use App\Models\UnidadEjecutora;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class OrdenPagoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:tesoreria.ordenes.ver',     only: ['index', 'show']),
            new Middleware('can:tesoreria.ordenes.crear',   only: ['create', 'store']),
            new Middleware('can:tesoreria.ordenes.aprobar', only: ['revisar', 'aprobar', 'enviar', 'anular']),
            new Middleware('can:tesoreria.ordenes.pagar',   only: ['pagar']),
        ];
    }

    // ── LISTADO ──────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $ejercicioId = session('ejercicio_id');

        $ordenes = OrdenPago::with(['unidadEjecutora', 'beneficiario', 'causacion', 'creadoPor'])
            ->when($ejercicioId, fn($q) => $q->where('ejercicio_fiscal_id', $ejercicioId))
            ->when($request->estado,  fn($q, $v) => $q->where('estado', $v))
            ->when($request->search,  fn($q, $v) => $q->where(fn($q) =>
                $q->where('numero', 'like', "%$v%")
                  ->orWhere('concepto', 'like', "%$v%")
            ))
            ->orderByDesc('id')
            ->paginate(20)->withQueryString();

        return view('tesoreria.ordenes.index', compact('ordenes'));
    }

    // ── CREAR ─────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        // Solo causaciones aprobadas sin pago todavía
        $causaciones = Causacion::where('estado', 'aprobada')
            ->with(['partida', 'unidadEjecutora'])
            ->orderByDesc('id')
            ->get();

        $unidades      = UnidadEjecutora::where('activo', true)->orderBy('nombre')->get();
        $beneficiarios = Beneficiario::activos()->orderBy('razon_social')->get();
        $ejercicio     = EjercicioFiscal::where('estado', 'activo')->first();

        // Pre-selección si viene desde causación
        $causacionSeleccionada = $request->causacion_id
            ? Causacion::with(['partida', 'unidadEjecutora'])->find($request->causacion_id)
            : null;

        return view('tesoreria.ordenes.create', compact(
            'causaciones', 'unidades', 'beneficiarios', 'ejercicio', 'causacionSeleccionada'
        ));
    }

    // ── GUARDAR ───────────────────────────────────────────────────────
    public function store(StoreOrdenPagoRequest $request)
    {
        $ejercicio = EjercicioFiscal::where('estado', 'activo')->firstOrFail();

        DB::transaction(function () use ($request, $ejercicio) {
            $causacion = Causacion::with('partida')->findOrFail($request->causacion_id);

            $total = collect($request->lineas)->sum('monto');

            $orden = OrdenPago::create([
                'numero'              => OrdenPago::generarNumero(now()->year),
                'ejercicio_fiscal_id' => $ejercicio->id,
                'unidad_ejecutora_id' => $causacion->unidad_ejecutora_id,
                'beneficiario_id'     => $request->beneficiario_id ?: null,
                'causacion_id'        => $causacion->id,
                'concepto'            => $request->concepto,
                'monto_total'         => $total,
                'tipo_pago'           => $request->tipo_pago,
                'estado'              => 'borrador',
                'observaciones'       => $request->observaciones,
                'creado_por'          => auth()->id(),
            ]);

            foreach ($request->lineas as $i => $linea) {
                if (!($linea['descripcion'] ?? null)) continue;
                OrdenPagoDetalle::create([
                    'orden_pago_id' => $orden->id,
                    'descripcion'   => $linea['descripcion'],
                    'monto'         => $linea['monto'],
                    'orden'         => $i + 1,
                ]);
            }
        });

        return redirect()->route('tesoreria.ordenes.index')
            ->with('success', 'Orden de pago creada correctamente.');
    }

    // ── DETALLE ───────────────────────────────────────────────────────
    public function show(OrdenPago $orden)
    {
        $orden->load([
            'unidadEjecutora', 'beneficiario',
            'causacion.partida', 'causacion.unidadEjecutora',
            'detalles', 'pago',
            'creadoPor', 'revisadoPor', 'aprobadoPor',
        ]);
        return view('tesoreria.ordenes.show', compact('orden'));
    }

    // ── FLUJO DE APROBACIÓN ──────────────────────────────────────────
    public function revisar(OrdenPago $orden)
    {
        abort_if($orden->estado !== 'borrador', 403, 'Solo se pueden revisar órdenes en borrador.');
        $orden->update([
            'estado'          => 'revisada',
            'revisado_por'    => auth()->id(),
            'fecha_revision'  => now(),
        ]);
        return back()->with('success', 'Orden marcada como revisada.');
    }

    public function aprobar(OrdenPago $orden)
    {
        abort_if(!in_array($orden->estado, ['borrador', 'revisada']), 403, 'Estado no permite aprobación.');
        $orden->update([
            'estado'           => 'aprobada',
            'aprobado_por'     => auth()->id(),
            'fecha_aprobacion' => now(),
        ]);
        return back()->with('success', 'Orden de pago aprobada.');
    }

    // ── ENVIAR: genera Pago "pendiente" en el módulo Presupuesto ─────
    public function enviar(OrdenPago $orden)
    {
        abort_if($orden->estado !== 'aprobada', 403, 'Solo se pueden enviar órdenes aprobadas.');
        abort_if(!$orden->causacion_id, 422, 'La orden debe tener una causación vinculada.');

        DB::transaction(function () use ($orden) {
            $causacion = Causacion::findOrFail($orden->causacion_id);
            $orden->update(['estado' => 'enviada', 'fecha_envio' => now()]);

            // Crear el Pago en estado "pendiente" para procesar en Presupuesto > Pagos
            Pago::create([
                'numero'              => Pago::generarNumero(now()->year),
                'causacion_id'        => $causacion->id,
                'orden_pago_id'       => $orden->id,
                'ejercicio_fiscal_id' => $causacion->ejercicio_fiscal_id,
                'unidad_ejecutora_id' => $causacion->unidad_ejecutora_id,
                'beneficiario'        => $causacion->beneficiario,
                'rif_beneficiario'    => $causacion->rif_beneficiario,
                'tipo_pago'           => $orden->tipo_pago,
                'monto_pagado'        => $orden->monto_total,
                'fecha_pago'          => now()->toDateString(),
                'concepto'            => $orden->concepto,
                'estado'              => 'pendiente',
                'observaciones'       => "Orden de Pago: {$orden->numero}",
                'created_by'          => auth()->id(),
                'generado_automatico' => true,
            ]);
        });

        return back()->with('success', 'Orden enviada. Se generó el Pago pendiente en Presupuesto > Pagos.');
    }

    // ── ANULAR ────────────────────────────────────────────────────────
    public function anular(Request $request, OrdenPago $orden)
    {
        $request->validate(['motivo_anulacion' => 'required|string|min:10']);
        abort_if($orden->estado === 'pagada', 403, 'No se puede anular una orden ya pagada.');

        DB::transaction(function () use ($request, $orden) {
            $orden->update(['estado' => 'anulada', 'motivo_anulacion' => $request->motivo_anulacion]);

            // Anular también el pago pendiente si existe
            if ($orden->pago && $orden->pago->esPendiente()) {
                $orden->pago->update([
                    'estado'           => 'anulado',
                    'motivo_anulacion' => "Orden {$orden->numero} anulada: {$request->motivo_anulacion}",
                ]);
            }
        });

        return back()->with('success', 'Orden de pago anulada.');
    }
}
