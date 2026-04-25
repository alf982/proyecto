<?php
namespace App\Http\Controllers\Compras;
use App\Http\Controllers\Controller;
use App\Models\InventarioMovimiento;
use App\Models\MovimientoPartida;
use App\Models\OrdenCompra;
use App\Models\PartidaPresupuestaria;
use App\Models\RecepcionBienes;
use App\Models\RecepcionDetalle;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class RecepcionController extends Controller implements HasMiddleware {
    public static function middleware(): array
    {
        return [
            new Middleware('can:compras.recepciones.ver', only: ['index', 'show']),
            new Middleware('can:compras.recepciones.crear', only: ['create', 'store']),
        ];
    }
    public function index(Request $request) {
        $ejercicioId = session('ejercicio_id');

        $q = RecepcionBienes::with(['orden','creadoPor'])
            ->when($ejercicioId, fn($q) => $q->where('ejercicio_fiscal_id', $ejercicioId))
            ->when($request->search, fn($q,$v) => $q->where(fn($q) => $q->where('numero','like',"%$v%")->orWhere('numero_factura','like',"%$v%")))
            ->when($request->estado, fn($q,$v) => $q->where('estado',$v))
            ->orderByDesc('fecha_recepcion')->paginate(20)->withQueryString();
        return view('compras.recepciones.index', compact('q'));
    }
    public function create(Request $request) {
        $ordenes = OrdenCompra::whereIn('estado',['emitida','confirmada','en_transito'])->with('detalles.articulo')->orderByDesc('id')->get();
        $orden   = $request->orden ? OrdenCompra::with('detalles.articulo')->find($request->orden) : $ordenes->first();

        // Preparar datos para el JS sin closures anidados en Blade
        $ordenesData = $ordenes->keyBy('id')->map(function ($oc) {
            return [
                'id'      => $oc->id,
                'detalles'=> $oc->detalles->map(function ($d) {
                    return [
                        'id'             => $d->id,
                        'articulo_id'    => $d->articulo_id,
                        'descripcion'    => $d->descripcion,
                        'codigo'         => $d->articulo?->codigo,
                        'cantidad'       => $d->cantidad,
                        'precio_unitario'=> $d->precio_unitario,
                        'pendiente'      => $d->getPendienteRecibir(),
                    ];
                })->values(),
            ];
        });

        return view('compras.recepciones.create', compact('ordenes','orden','ordenesData'));
    }
    public function store(Request $request) {
        $request->validate([
            'orden_compra_id'  => 'required|exists:ordenes_compra,id',
            'fecha_recepcion'  => 'required|date',
            'recibido_por'     => 'required|string',
            'lineas'           => 'required|array|min:1',
            'lineas.*.orden_detalle_id'   => 'required|exists:ordenes_compra_detalle,id',
            'lineas.*.cantidad_recibida'  => 'required|numeric|min:0',
        ]);
        DB::transaction(function () use ($request) {
            $orden    = OrdenCompra::with('detalles.articulo')->findOrFail($request->orden_compra_id);
            $totalRec = collect($request->lineas)->sum(fn($l) => ($l['cantidad_recibida'] ?? 0) * ($l['precio_unitario'] ?? 0));
            $rec = RecepcionBienes::create([
                'numero'             => RecepcionBienes::generarNumero(now()->year),
                'ejercicio_fiscal_id'=> session('ejercicio_id')
                    ?? \App\Models\EjercicioFiscal::where('estado','activo')->value('id'),
                'orden_compra_id'    => $orden->id,
                'fecha_recepcion' => $request->fecha_recepcion,
                'recibido_por'    => $request->recibido_por,
                'entregado_por'   => $request->entregado_por,
                'numero_guia'     => $request->numero_guia,
                'numero_factura'  => $request->numero_factura,
                'total_recibido'  => $totalRec,
                'estado'          => $request->estado ?? 'conforme',
                'observaciones'   => $request->observaciones,
                'creado_por'      => auth()->id(),
            ]);
            foreach ($request->lineas as $linea) {
                $cantRec = (float)($linea['cantidad_recibida'] ?? 0);
                if ($cantRec <= 0) continue;
                $det     = $orden->detalles->firstWhere('id', $linea['orden_detalle_id']);
                RecepcionDetalle::create([
                    'recepcion_id'       => $rec->id,
                    'orden_detalle_id'   => $det->id,
                    'articulo_id'        => $det->articulo_id,
                    'cantidad_recibida'  => $cantRec,
                    'precio_unitario'    => $linea['precio_unitario'] ?? $det->precio_unitario,
                    'condicion'          => $linea['condicion'] ?? 'bueno',
                    'observacion'        => $linea['observacion'] ?? null,
                ]);
                // Actualizar stock del artículo
                if ($det->articulo_id) {
                    $art   = $det->articulo;
                    $antes = $art->stock_actual;
                    $nuevo = $antes + $cantRec;
                    $art->update(['stock_actual' => $nuevo]);
                    InventarioMovimiento::create([
                        'articulo_id'    => $art->id,
                        'almacen_id'     => $art->almacen_id,
                        'tipo'           => 'entrada',
                        'origen_tipo'    => 'recepcion',
                        'origen_id'      => $rec->id,
                        'cantidad'       => $cantRec,
                        'precio_unitario'=> $linea['precio_unitario'] ?? $det->precio_unitario,
                        'stock_anterior' => $antes,
                        'stock_nuevo'    => $nuevo,
                        'concepto'       => 'Recepción '.$rec->numero.' - OC '.$orden->numero,
                        'fecha'          => $request->fecha_recepcion,
                        'creado_por'     => auth()->id(),
                    ]);
                }
                // Actualizar cantidad recibida en detalle de orden
                $det->increment('cantidad_recibida', $cantRec);
            }
            // ── EFECTO PRESUPUESTARIO ──────────────────────────────────────
            // Si la OC tiene partida asignada, descontar el monto recibido del saldo
            if ($orden->partida_presupuestaria_id && $totalRec > 0) {
                $partida   = PartidaPresupuestaria::find($orden->partida_presupuestaria_id);
                $saldoAnt  = (float) $partida->saldo_actual;
                $saldoPost = max(0, $saldoAnt - $totalRec);

                $partida->decrement('saldo_actual', $totalRec);

                MovimientoPartida::create([
                    'numero'                    => MovimientoPartida::generarNumero(now()->year),
                    'partida_presupuestaria_id' => $partida->id,
                    'ejercicio_fiscal_id'       => $orden->ejercicio_fiscal_id,
                    'tipo'                      => 'pago',
                    'concepto'                  => 'Recepción ' . $rec->numero . ' — OC ' . $orden->numero . ' — ' . $orden->concepto,
                    'monto'                     => $totalRec,
                    'fecha_movimiento'          => $request->fecha_recepcion,
                    'referencia'                => $orden->numero,
                    'saldo_anterior'            => $saldoAnt,
                    'saldo_posterior'           => $saldoPost,
                    'estado'                    => 'confirmado',
                    'creado_por'                => auth()->id(),
                ]);
            }

            // Verificar si la orden quedó completada
            $orden->refresh();
            $completada = $orden->detalles->every(fn($d) => $d->cantidad_recibida >= $d->cantidad);
            if ($completada) $orden->update(['estado' => 'completada']);
        });

        return redirect()->route('compras.recepciones.index')->with('success', 'Recepción registrada, stock actualizado y saldo de partida descontado.');
    }
    public function show(RecepcionBienes $recepcion) {
        $recepcion->load(['orden','detalles.articulo','detalles.ordenDetalle','creadoPor']);
        return view('compras.recepciones.show', compact('recepcion'));
    }
}
