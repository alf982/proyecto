<?php
namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use App\Models\Beneficiario;
use App\Models\EjercicioFiscal;
use App\Models\MovimientoPartida;
use App\Models\OrdenCompra;
use App\Models\OrdenDetalle;
use App\Models\PartidaPresupuestaria;
use App\Models\SolicitudCompra;
use App\Traits\GuardaRetenciones;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class OrdenCompraController extends Controller implements HasMiddleware
{
    use GuardaRetenciones;
    public static function middleware(): array
    {
        return [
            new Middleware('can:compras.ordenes.ver',     only: ['index', 'show']),
            new Middleware('can:compras.ordenes.crear',   only: ['create', 'store']),
            new Middleware('can:compras.ordenes.aprobar', only: ['cambiarEstado']),
        ];
    }

    // ── LISTADO ──────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $ejercicioId = session('ejercicio_id');

        $q = OrdenCompra::with(['beneficiario', 'creadoPor', 'partida'])
            ->when($ejercicioId, fn($q) => $q->where('ejercicio_fiscal_id', $ejercicioId))
            ->when($request->estado, fn($q, $v) => $q->where('estado', $v))
            ->when($request->search, fn($q, $v) => $q->where(fn($q) =>
                $q->where('numero', 'like', "%$v%")
                  ->orWhere('concepto', 'like', "%$v%")
                  ->orWhere('proveedor_nombre', 'like', "%$v%")
            ))
            ->orderByDesc('fecha_emision')
            ->paginate(20)
            ->withQueryString();

        return view('compras.ordenes.index', compact('q'));
    }

    // ── CREAR ─────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        $solicitudes   = SolicitudCompra::with('detalles.articulo')->where('estado', 'aprobada')->orderByDesc('id')->get();
        $beneficiarios = Beneficiario::activos()->orderBy('razon_social')->get();
        $articulos     = Articulo::activos()->orderBy('nombre')->get();
        $ejercicio     = session('ejercicio_id')
            ? EjercicioFiscal::find(session('ejercicio_id'))
            : EjercicioFiscal::where('estado', 'activo')->first();

        // Mostrar todas las partidas activas (sin filtrar por tipo)
        $partidas = PartidaPresupuestaria::where('activo', true)
                        ->orderBy('codigo')
                        ->get();

        $solicitud = $request->solicitud
            ? SolicitudCompra::with('detalles.articulo')->find($request->solicitud)
            : null;

        return view('compras.ordenes.create',
            compact('solicitudes', 'beneficiarios', 'articulos', 'ejercicio', 'solicitud', 'partidas'));
    }

    // ── GUARDAR ───────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'concepto'                 => 'required|string|max:500',
            'fecha_emision'            => 'required|date',
            'lineas'                   => 'required|array|min:1',
            'lineas.*.descripcion'     => 'required|string',
            'lineas.*.cantidad'        => 'required|numeric|min:0.01',
            'lineas.*.precio_unitario' => 'required|numeric|min:0',
            'partida_presupuestaria_id'=> 'nullable|exists:partidas_presupuestarias,id',
            'retenciones'              => 'nullable|array',
            'retenciones.*'            => 'integer|exists:retenciones,id',
        ]);

        $idsRetenciones = $request->input('retenciones', []);

        $ejercicio = EjercicioFiscal::where('estado', 'activo')->firstOrFail();

        // Validar saldo si se seleccionó partida
        if ($request->partida_presupuestaria_id) {
            $partida  = PartidaPresupuestaria::findOrFail($request->partida_presupuestaria_id);
            $total    = round(collect($request->lineas)->sum(fn($l) => ($l['cantidad'] ?? 0) * ($l['precio_unitario'] ?? 0)), 2);

            if ($total > (float) $partida->saldo_actual) {
                return back()
                    ->withErrors(['partida_presupuestaria_id' =>
                        'El total de la OC (Bs. ' . number_format($total, 2) .
                        ') supera el saldo disponible en la partida (Bs. ' .
                        number_format($partida->saldo_actual, 2) . ').'])
                    ->withInput();
            }
        }

        $subtotalPrevio = collect($request->lineas)->sum(fn($l) => ($l['cantidad'] ?? 0) * ($l['precio_unitario'] ?? 0));
        $totalBruto     = round($subtotalPrevio, 2);
        $totalRetencion = $this->calcularTotalRetenciones($idsRetenciones, $totalBruto);
        $montoNeto      = round($totalBruto - $totalRetencion, 2);

        DB::transaction(function () use ($request, $ejercicio, $idsRetenciones, $totalBruto, $totalRetencion, $montoNeto) {
            $subtotal = $totalBruto;
            $total    = $totalBruto;

            $oc = OrdenCompra::create([
                'numero'                   => OrdenCompra::generarNumero(now()->year),
                'solicitud_compra_id'      => $request->solicitud_compra_id ?: null,
                'ejercicio_fiscal_id'      => $ejercicio->id,
                'partida_presupuestaria_id'=> $request->partida_presupuestaria_id ?: null,
                'beneficiario_id'          => $request->beneficiario_id ?: null,
                'proveedor_nombre'         => $request->proveedor_nombre,
                'proveedor_rif'            => $request->proveedor_rif,
                'concepto'                 => $request->concepto,
                'fecha_emision'            => $request->fecha_emision,
                'fecha_entrega_estimada'   => $request->fecha_entrega_estimada ?: null,
                'subtotal'                 => $subtotal,
                'iva_porcentaje'           => 0,
                'iva_monto'                => 0,
                'total'                    => $totalBruto,
                'monto_retencion'          => $totalRetencion,
                'monto_neto'               => $montoNeto,
                'estado'                   => 'emitida',
                'modalidad'                => $request->modalidad ?? 'compra_directa',
                'numero_contrato'          => $request->numero_contrato,
                'condiciones'              => $request->condiciones,
                'creado_por'               => auth()->id(),
            ]);

            // Guardar renglones
            foreach ($request->lineas as $i => $linea) {
                if (!($linea['descripcion'] ?? null)) continue;
                OrdenDetalle::create([
                    'orden_compra_id'  => $oc->id,
                    'articulo_id'      => $linea['articulo_id'] ?: null,
                    'descripcion'      => $linea['descripcion'],
                    'unidad_medida'    => $linea['unidad_medida'] ?? 'unidad',
                    'cantidad'         => $linea['cantidad'],
                    'precio_unitario'  => $linea['precio_unitario'],
                    'subtotal'         => $linea['cantidad'] * $linea['precio_unitario'],
                    'orden'            => $i + 1,
                ]);
            }

            // ── RESERVA PRESUPUESTARIA al emitir (solo marcador, sin descontar saldo) ──
            if ($oc->partida_presupuestaria_id) {
                $partida  = PartidaPresupuestaria::find($oc->partida_presupuestaria_id);
                $saldoAct = (float) $partida->saldo_actual;

                MovimientoPartida::create([
                    'numero'                    => MovimientoPartida::generarNumero(now()->year),
                    'partida_presupuestaria_id' => $partida->id,
                    'ejercicio_fiscal_id'       => $ejercicio->id,
                    'tipo'                      => 'compromiso',
                    'concepto'                  => 'Compromiso OC ' . $oc->numero . ' — ' . $oc->concepto,
                    'monto'                     => $total,
                    'fecha_movimiento'          => now(),
                    'referencia'                => $oc->numero,
                    'saldo_anterior'            => $saldoAct,
                    'saldo_posterior'           => $saldoAct, // sin cambio — se descuenta al recibir
                    'estado'                    => 'confirmado',
                    'creado_por'                => auth()->id(),
                ]);
            }

            // Marcar solicitud como procesada
            if ($request->solicitud_compra_id) {
                SolicitudCompra::find($request->solicitud_compra_id)?->update(['estado' => 'procesada']);
            }

            // Guardar retenciones en tabla polimórfica
            $this->guardarRetenciones($oc, $idsRetenciones, $totalBruto);
        });

        return redirect()->route('compras.ordenes.index')
            ->with('success', 'Orden de compra emitida. El saldo se descontará de la partida al registrar la recepción.');
    }

    // ── DETALLE ───────────────────────────────────────────────────────
    public function show(OrdenCompra $orden)
    {
        $orden->load(['solicitud', 'beneficiario', 'partida', 'detalles.articulo', 'recepciones', 'creadoPor', 'retenciones.retencion']);
        return view('compras.ordenes.show', compact('orden'));
    }

    // ── CAMBIAR ESTADO ────────────────────────────────────────────────
    public function cambiarEstado(Request $request, OrdenCompra $orden)
    {
        $request->validate(['estado' => 'required|in:confirmada,en_transito,completada,anulada']);

        if ($request->estado === 'anulada') {
            $request->validate(['motivo_anulacion' => 'required|string|min:5']);
        }

        DB::transaction(function () use ($request, $orden) {
            $estadoAnterior = $orden->estado;

            $orden->update([
                'estado'           => $request->estado,
                'motivo_anulacion' => $request->estado === 'anulada' ? $request->motivo_anulacion : $orden->motivo_anulacion,
            ]);

            // ── RESTAURAR SALDO al anular solo si ya hubo recepción ──────
            // El saldo solo se descuenta al registrar recepción, no al emitir.
            // Si se anula antes de recibir, no hay saldo que restaurar.
            if ($request->estado === 'anulada'
                && $orden->partida_presupuestaria_id
                && !in_array($estadoAnterior, ['completada', 'anulada']))
            {
                // Verificar si ya hubo descuento en recepción (MovimientoPartida tipo='pago')
                $totalEjecutado = MovimientoPartida::where('referencia', $orden->numero)
                    ->where('tipo', 'pago')
                    ->sum('monto');

                if ($totalEjecutado > 0) {
                    $partida   = PartidaPresupuestaria::find($orden->partida_presupuestaria_id);
                    $saldoAnt  = (float) $partida->saldo_actual;
                    $saldoPost = $saldoAnt + $totalEjecutado;

                    $partida->increment('saldo_actual', $totalEjecutado);

                    MovimientoPartida::create([
                        'numero'                    => MovimientoPartida::generarNumero(now()->year),
                        'partida_presupuestaria_id' => $partida->id,
                        'ejercicio_fiscal_id'       => $orden->ejercicio_fiscal_id,
                        'tipo'                      => 'anulacion',
                        'concepto'                  => 'Anulación OC ' . $orden->numero . ' — ' . $request->motivo_anulacion,
                        'monto'                     => $totalEjecutado,
                        'fecha_movimiento'          => now(),
                        'referencia'                => $orden->numero,
                        'saldo_anterior'            => $saldoAnt,
                        'saldo_posterior'           => $saldoPost,
                        'estado'                    => 'confirmado',
                        'creado_por'                => auth()->id(),
                    ]);
                }
            }
        });

        return back()->with('success', 'Estado actualizado a ' . ucfirst($request->estado) . '.');
    }
}
