<?php
namespace App\Http\Controllers\Almacen;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use App\Models\InventarioMovimiento;
use App\Models\SolicitudDespacho;
use App\Models\SolicitudDespachoDetalle;
use App\Models\UnidadEjecutora;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class SolicitudDespachoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:almacen.solicitudes.ver',     only: ['index', 'show']),
            new Middleware('can:almacen.solicitudes.crear',   only: ['create', 'store']),
            new Middleware('can:almacen.solicitudes.aprobar', only: ['aprobar', 'rechazar', 'registrarEntrega']),
        ];
    }

    // ── LISTADO ────────────────────────────────────────────────
    public function index(Request $request)
    {
        $ejercicioId = session('ejercicio_id');

        $q = SolicitudDespacho::with(['unidadEjecutora', 'solicitadoPor'])
            ->when($ejercicioId, fn($q) => $q->where('ejercicio_fiscal_id', $ejercicioId))
            ->when($request->estado,    fn($q, $v) => $q->where('estado', $v))
            ->when($request->unidad,    fn($q, $v) => $q->where('unidad_ejecutora_id', $v))
            ->when($request->prioridad, fn($q, $v) => $q->where('prioridad', $v))
            ->when($request->search,    fn($q, $v) => $q->where(fn($q) =>
                $q->where('numero', 'like', "%$v%")
                  ->orWhere('motivo', 'like', "%$v%")
            ))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $unidades = UnidadEjecutora::where('activo', true)->orderBy('nombre')->get();

        // KPIs
        $baseQuery = SolicitudDespacho::when($ejercicioId, fn($q) => $q->where('ejercicio_fiscal_id', $ejercicioId));
        $kpis = [
            'pendientes'  => (clone $baseQuery)->where('estado', 'enviada')->count(),
            'aprobadas'   => (clone $baseQuery)->where('estado', 'aprobada')->count(),
            'entregadas'  => (clone $baseQuery)->where('estado', 'entregada')->count(),
            'rechazadas'  => (clone $baseQuery)->where('estado', 'rechazada')->count(),
        ];

        return view('almacen.solicitudes.index', compact('q', 'unidades', 'kpis'));
    }

    // ── CREAR (formulario para la oficina) ──────────────────────
    public function create()
    {
        $articulos = Articulo::activos()
            ->where('stock_actual', '>', 0)
            ->with('almacen')
            ->orderBy('nombre')
            ->get();

        $unidades = UnidadEjecutora::where('activo', true)->orderBy('nombre')->get();

        return view('almacen.solicitudes.create', compact('articulos', 'unidades'));
    }

    // ── GUARDAR SOLICITUD ────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'unidad_ejecutora_id'          => 'required|exists:unidades_ejecutoras,id',
            'motivo'                       => 'required|string|max:500',
            'prioridad'                    => 'required|in:baja,media,alta,urgente',
            'fecha_requerida'              => 'nullable|date|after_or_equal:today',
            'observaciones'                => 'nullable|string',
            'lineas'                       => 'required|array|min:1',
            'lineas.*.articulo_id'         => 'required|exists:articulos,id',
            'lineas.*.cantidad_solicitada' => 'required|numeric|min:0.01',
        ], [
            'lineas.required' => 'Debe agregar al menos un artículo a la solicitud.',
        ]);

        DB::transaction(function () use ($request) {
            $ejercicioId = session('ejercicio_id')
                ?? \App\Models\EjercicioFiscal::where('estado', 'activo')->value('id');

            $sol = SolicitudDespacho::create([
                'numero'              => SolicitudDespacho::generarNumero(now()->year),
                'ejercicio_fiscal_id' => $ejercicioId,
                'unidad_ejecutora_id' => $request->unidad_ejecutora_id,
                'motivo'              => $request->motivo,
                'prioridad'           => $request->prioridad,
                'fecha_requerida'     => $request->fecha_requerida ?: null,
                'observaciones'       => $request->observaciones,
                'estado'              => 'enviada',
                'solicitado_por'      => auth()->id(),
            ]);

            foreach ($request->lineas as $orden => $linea) {
                SolicitudDespachoDetalle::create([
                    'solicitud_despacho_id' => $sol->id,
                    'articulo_id'           => $linea['articulo_id'],
                    'cantidad_solicitada'   => $linea['cantidad_solicitada'],
                    'observacion'           => $linea['observacion'] ?? null,
                    'orden'                 => $orden + 1,
                ]);
            }
        });

        return redirect()->route('almacen.solicitudes.index')
            ->with('success', 'Solicitud registrada. El almacén la procesará a la brevedad.');
    }

    // ── DETALLE ──────────────────────────────────────────────────
    public function show(SolicitudDespacho $solicitud)
    {
        $solicitud->load(['unidadEjecutora', 'solicitadoPor', 'aprobadoPor', 'detalles.articulo.almacen']);
        return view('almacen.solicitudes.show', compact('solicitud'));
    }

    // ── APROBAR (solo aprueba, NO entrega ni descuenta stock) ───
    public function aprobar(SolicitudDespacho $solicitud)
    {
        if (!$solicitud->esEnviada()) {
            return back()->with('error', 'Solo se pueden aprobar solicitudes en estado Enviada.');
        }

        $solicitud->update([
            'estado'           => 'aprobada',
            'aprobado_por'     => auth()->id(),
            'fecha_aprobacion' => now(),
        ]);

        return redirect()->route('almacen.solicitudes.show', $solicitud)
            ->with('success', 'Solicitud aprobada. Ahora puede registrar la entrega al momento de despachar los artículos.');
    }

    // ── REGISTRAR ENTREGA REAL (descuenta stock) ─────────────────
    public function registrarEntrega(Request $request, SolicitudDespacho $solicitud)
    {
        if (!$solicitud->esAprobada()) {
            return back()->with('error', 'Solo se puede registrar la entrega de solicitudes aprobadas.');
        }

        $request->validate([
            'recibido_por'         => 'required|string|max:200',
            'fecha_entrega'        => 'required|date',
            'observaciones_entrega'=> 'nullable|string|max:1000',
            'lineas'               => 'required|array|min:1',
            'lineas.*.detalle_id'  => 'required|exists:solicitudes_despacho_detalle,id',
            'lineas.*.cantidad_entregada' => 'required|numeric|min:0',
        ], [
            'recibido_por.required' => 'Debe indicar quién recibió los artículos en la oficina.',
            'fecha_entrega.required'=> 'Debe registrar la fecha de entrega.',
        ]);

        $solicitud->load('detalles.articulo');

        // Verificar stock para las cantidades a entregar
        $sinStock = [];
        foreach ($request->lineas as $linea) {
            $cantEnt = (float) ($linea['cantidad_entregada'] ?? 0);
            if ($cantEnt <= 0) continue;

            $detalle = $solicitud->detalles->firstWhere('id', $linea['detalle_id']);
            if (!$detalle) continue;

            $art = $detalle->articulo;
            if (!$art) {
                $sinStock[] = "• [Artículo eliminado ID:{$detalle->articulo_id}] — No existe en catálogo.";
                continue;
            }
            if ((float) $art->stock_actual < $cantEnt) {
                $sinStock[] = "• {$art->nombre} — Disponible: " . number_format($art->stock_actual, 2)
                            . " / A entregar: " . number_format($cantEnt, 2) . " {$art->unidad_medida}";
            }
        }

        if (!empty($sinStock)) {
            return back()->with('error',
                'Stock insuficiente para los siguientes artículos:' . "\n" . implode("\n", $sinStock)
            )->withInput();
        }

        DB::transaction(function () use ($request, $solicitud) {
            $fechaEntrega = $request->fecha_entrega;

            foreach ($request->lineas as $linea) {
                $cantEnt = (float) ($linea['cantidad_entregada'] ?? 0);
                if ($cantEnt <= 0) continue;

                $detalle = $solicitud->detalles->firstWhere('id', $linea['detalle_id']);
                if (!$detalle) continue;

                $art = $detalle->articulo;
                if (!$art) continue;

                // Descontar stock
                $antes = (float) $art->stock_actual;
                $nuevo = max(0, $antes - $cantEnt);
                $art->update(['stock_actual' => $nuevo]);

                // Registrar movimiento de inventario (kardex)
                InventarioMovimiento::create([
                    'articulo_id'     => $art->id,
                    'almacen_id'      => $art->almacen_id,
                    'tipo'            => 'salida',
                    'origen_tipo'     => 'despacho',
                    'origen_id'       => $solicitud->id,
                    'cantidad'        => $cantEnt,
                    'precio_unitario' => $art->precio_referencia ?? 0,
                    'stock_anterior'  => $antes,
                    'stock_nuevo'     => $nuevo,
                    'concepto'        => "Entrega {$solicitud->numero} → {$solicitud->unidadEjecutora->nombre}",
                    'fecha'           => $fechaEntrega,
                    'creado_por'      => auth()->id(),
                ]);

                // Actualizar cantidad despachada en el detalle
                $detalle->update(['cantidad_despachada' => $cantEnt]);
            }

            // Actualizar la solicitud con datos de entrega
            $solicitud->update([
                'estado'                => 'entregada',
                'recibido_por'          => $request->recibido_por,
                'fecha_entrega'         => $request->fecha_entrega,
                'fecha_despacho'        => now(),
                'observaciones_entrega' => $request->observaciones_entrega,
            ]);
        });

        return redirect()->route('almacen.solicitudes.show', $solicitud)
            ->with('success', 'Entrega registrada correctamente. El inventario ha sido actualizado.');
    }

    // ── RECHAZAR / CANCELAR (enviada o aprobada) ─────────────────
    public function rechazar(Request $request, SolicitudDespacho $solicitud)
    {
        $request->validate([
            'motivo_rechazo' => 'required|string|min:10|max:500',
        ]);

        if (!$solicitud->puedeRechazarse()) {
            return back()->with('error', 'Esta solicitud ya fue entregada y no puede rechazarse.');
        }

        $solicitud->update([
            'estado'           => 'rechazada',
            'motivo_rechazo'   => $request->motivo_rechazo,
            'aprobado_por'     => auth()->id(),
            'fecha_aprobacion' => now(),
        ]);

        return redirect()->route('almacen.solicitudes.index')
            ->with('success', 'Solicitud rechazada.');
    }
}
