<?php
namespace App\Http\Controllers\Presupuesto;

use App\Http\Controllers\Controller;
use App\Http\Requests\Presupuesto\AnularRequest;
use App\Http\Requests\Presupuesto\ProcesarPagoRequest;
use App\Http\Requests\Presupuesto\StorePagoRequest;
use App\Models\Causacion;
use App\Models\EjercicioFiscal;
use App\Models\Pago;
use App\Services\CatalogoCache;
use App\Services\Presupuesto\PagoService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PagoController extends Controller implements HasMiddleware
{
    public function __construct(private readonly PagoService $service) {}

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

        $ejercicios = CatalogoCache::ejercicios();
        $pendientes = Pago::where('estado', 'pendiente')->count();

        // Solo columnas necesarias para el resumen del listado
        $causacionesPendientes = Causacion::where('estado', 'aprobada')
            ->with(['unidadEjecutora:id,nombre', 'partida:id,codigo,descripcion'])
            ->select(['id', 'numero', 'beneficiario', 'monto_causado', 'fecha_causacion', 'unidad_ejecutora_id', 'partida_presupuestaria_id'])
            ->orderByDesc('id')
            ->get();

        return view('presupuesto.pagos.index', compact('q', 'ejercicios', 'pendientes', 'causacionesPendientes'));
    }

    // ── CREAR ─────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        $causaciones = Causacion::where('estado', 'aprobada')
            ->with(['unidadEjecutora', 'partida', 'compromiso.beneficiarioModel'])
            ->orderByDesc('id')
            ->get();

        $causacion = $request->causacion_id
            ? Causacion::with(['ejercicioFiscal', 'unidadEjecutora', 'partida', 'compromiso.beneficiarioModel'])->find($request->causacion_id)
            : null;

        $ejercicio = EjercicioFiscal::where('estado', 'activo')->first();

        return view('presupuesto.pagos.create', compact('causaciones', 'causacion', 'ejercicio'));
    }

    // ── GUARDAR ───────────────────────────────────────────────────────
    public function store(StorePagoRequest $request)
    {
        $causacion      = Causacion::with('partida')->findOrFail($request->causacion_id);
        $idsRetenciones = $request->input('retenciones', []);

        try {
            $this->service->crear($causacion, $request->all(), $idsRetenciones);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['causacion_id' => $e->getMessage()])->withInput();
        }

        return redirect()->route('presupuesto.pagos.index')
            ->with('success', 'Pago registrado y partida presupuestaria actualizada correctamente.');
    }

    // ── DETALLE ───────────────────────────────────────────────────────
    public function show(Pago $pago)
    {
        $pago->load(['causacion.partida', 'causacion.unidadEjecutora', 'ejercicioFiscal', 'unidadEjecutora', 'creadoPor', 'retenciones.retencion']);
        return view('presupuesto.pagos.show', compact('pago'));
    }

    // ── EDITAR ────────────────────────────────────────────────────────
    public function edit(Pago $pago)
    {
        $pago->load(['causacion', 'ejercicioFiscal', 'unidadEjecutora']);
        return view('presupuesto.pagos.edit', compact('pago'));
    }

    // ── ACTUALIZAR ────────────────────────────────────────────────────
    public function update(Request $request, Pago $pago)
    {
        // Placeholder: si se necesita en el futuro
        return back()->with('error', 'Operación no disponible.');
    }

    // ── PROCESAR ─────────────────────────────────────────────────────
    public function procesar(ProcesarPagoRequest $request, Pago $pago)
    {

        try {
            $this->service->procesar($pago, $request->all());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('presupuesto.pagos.show', $pago)
            ->with('success', 'Pago procesado correctamente.');
    }

    // ── ANULAR ────────────────────────────────────────────────────────
    public function anular(AnularRequest $request, Pago $pago)
    {

        try {
            $this->service->anular($pago, $request->motivo_anulacion);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('presupuesto.pagos.index')
            ->with('success', 'Pago anulado y causación revertida a Aprobada.');
    }
}
