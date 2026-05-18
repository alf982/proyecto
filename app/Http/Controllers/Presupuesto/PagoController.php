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

/**
 * Controlador de Pagos (Tesorería)
 * 
 * Gestiona el desembolso financiero y la ejecución presupuestaria final.
 * Registra transacciones bancarias o emisión de cheques para extinguir 
 * una deuda reconocida (Causación).
 * 
 * Ciclo de Estado:
 * 1. Pendiente (Se prepara la orden de pago, o se autogenera en borrador al causar)
 * 2. Procesado (Confirmación del banco, transferencia realizada. Etapa final)
 * 3. Anulado (Revierte el estado de la Causación a 'Aprobada' si el cheque rebotó o hubo error)
 */
class PagoController extends Controller implements HasMiddleware
{
    /**
     * Inyección del servicio de pagos para el manejo de lógica transaccional,
     * registro de asientos contables (si aplica) y control de presupuesto.
     */
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
    
    /**
     * Lista todas las transacciones de pago.
     * Incluye una "bandeja de entrada" para causar directamente órdenes pendientes.
     */
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
    
    /**
     * Prepara el formulario para emitir una nueva orden de pago,
     * basándose en una Causación aprobada.
     */
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
    
    /**
     * Almacena la orden de pago. La lógica principal y el descuento en la
     * partida presupuestaria (pagado) se delega al PagoService.
     */
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
    
    /**
     * Muestra el recibo o comprobante de egreso.
     */
    public function show(Pago $pago)
    {
        $pago->load(['causacion.partida', 'causacion.unidadEjecutora', 'ejercicioFiscal', 'unidadEjecutora', 'creadoPor', 'retenciones.retencion']);
        return view('presupuesto.pagos.show', compact('pago'));
    }

    // ── EDITAR ────────────────────────────────────────────────────────
    
    /**
     * Formulario para corregir referencias bancarias.
     */
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
    
    /**
     * Confirma que el dinero salió de la cuenta bancaria.
     * Esta es la última confirmación financiera.
     */
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
    
    /**
     * Reverso financiero. Se reintegra el saldo pagado al saldo causado,
     * marcando la causación nuevamente como 'aprobada' (pendiente por pago).
     */
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
