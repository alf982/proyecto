<?php

namespace App\Http\Controllers\Presupuesto;

use App\Http\Controllers\Controller;
use App\Http\Requests\Presupuesto\AnularRequest;
use App\Http\Requests\Presupuesto\StoreCausacionRequest;
use App\Models\Causacion;
use App\Models\Compromiso;
use App\Models\EjercicioFiscal;
use App\Models\UnidadEjecutora;
use App\Services\CatalogoCache;
use App\Services\Presupuesto\CausacionService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Controlador de Causaciones
 * 
 * Gestiona el reconocimiento contable de la deuda adquirida mediante un compromiso.
 * Delega la lógica de negocio (aplicación de retenciones, cálculo de saldo causado, anulación)
 * al `CausacionService`.
 * 
 * Ciclo de Estado:
 * 1. Borrador (Creada automáticamente al aprobar un Compromiso, o manualmente referenciando un Compromiso)
 * 2. Aprobada (Validada, con factura registrada y retenciones aplicadas. Lista para Pago)
 * 3. Pagada (Se procesó la orden de pago)
 * 4. Anulada (Revierte el estatus del Compromiso a 'Aprobado')
 */
class CausacionController extends Controller implements HasMiddleware
{
    /**
     * Inyección de dependencia del servicio de negocio.
     */
    public function __construct(private readonly CausacionService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:causaciones.ver',    only: ['index', 'show']),
            new Middleware('can:causaciones.crear',  only: ['create', 'store']),
            new Middleware('can:causaciones.aprobar',only: ['aprobar']),
            new Middleware('can:causaciones.anular', only: ['anular']),
            new Middleware('can:pagos.procesar',     only: ['pagar']),
        ];
    }

    // ── LISTADO ──────────────────────────────────────────────────────
    
    /**
     * Muestra el listado de Causaciones.
     * Incluye una "bandeja de entrada" con las causaciones en borrador pendientes de revisión.
     */
    public function index(Request $request)
    {
        $ejercicioId = session('ejercicio_id');

        $q = Causacion::with(['ejercicioFiscal', 'unidadEjecutora', 'partida', 'compromiso'])
            ->when($ejercicioId, fn($q) => $q->where('ejercicio_fiscal_id', $ejercicioId))
            ->when($request->unidad,    fn($q, $v) => $q->where('unidad_ejecutora_id', $v))
            ->when($request->estado,    fn($q, $v) => $q->where('estado', $v))
            ->when($request->search,    fn($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('numero', 'like', "%$v%")
                  ->orWhere('beneficiario', 'like', "%$v%")
                  ->orWhere('concepto', 'like', "%$v%");
            }))
            ->orderByDesc('numero')
            ->paginate(15)
            ->withQueryString();

        $ejercicios = CatalogoCache::ejercicios();
        $unidades   = CatalogoCache::unidades();

        // Causaciones en borrador pendientes de aprobación
        $causacionesPendientes = Causacion::where('estado', 'borrador')
            ->with(['unidadEjecutora:id,nombre', 'partida:id,codigo,descripcion', 'compromiso:id,numero'])
            ->select(['id', 'numero', 'beneficiario', 'monto_causado', 'fecha_causacion', 'concepto', 'unidad_ejecutora_id', 'partida_presupuestaria_id', 'compromiso_id'])
            ->orderByDesc('id')
            ->get();

        return view('presupuesto.causaciones.index', compact('q', 'ejercicios', 'unidades', 'causacionesPendientes'));
    }

    // ── CREAR ──────────────────────────────────────────────────────
    
    /**
     * Formulario para crear causación desde cero vinculándola a un Compromiso Aprobado.
     * (Normalmente las causaciones nacen automáticas en borrador al aprobar el compromiso,
     * pero este método sirve para causaciones parciales).
     */
    public function create(Request $request)
    {
        $compromisos = Compromiso::with(['partida', 'unidadEjecutora'])
            ->where('estado', 'aprobado')
            ->whereDoesntHave('causaciones', fn($q) => $q->whereNotIn('estado', ['anulada']))
            ->orderByDesc('fecha_compromiso')
            ->get();

        $compromisoSeleccionado = $request->compromiso_id
            ? Compromiso::with(['partida', 'unidadEjecutora'])->find($request->compromiso_id)
            : null;

        return view('presupuesto.causaciones.create', compact('compromisos', 'compromisoSeleccionado'));
    }

    // ── GUARDAR ───────────────────────────────────────────────────────
    
    /**
     * Procesa el guardado de la causación. 
     * Se comunica con CausacionService para validar que no exceda el monto comprometido
     * y aplica las retenciones enviadas en el Request.
     */
    public function store(StoreCausacionRequest $request)
    {
        $compromiso     = Compromiso::with('partida')->findOrFail($request->compromiso_id);
        $idsRetenciones = $request->input('retenciones', []);

        try {
            $this->service->crear($compromiso, $request->all(), $idsRetenciones);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['monto_causado' => $e->getMessage()])->withInput();
        }

        return redirect()->route('presupuesto.causaciones.index')
            ->with('success', 'Causación registrada correctamente. Lista para aprobación.');
    }

    // ── DETALLE ───────────────────────────────────────────────────────
    
    /**
     * Vista de detalle (Show) de la causación.
     */
    public function show(Causacion $causacion)
    {
        $causacion->load(['ejercicioFiscal', 'unidadEjecutora', 'partida', 'compromiso', 'proyecto', 'creadoPor', 'aprobadoPor', 'pagos']);
        return view('presupuesto.causaciones.show', compact('causacion'));
    }

    // ── EDITAR ────────────────────────────────────────────────────────
    
    /**
     * Formulario para editar datos de la causación (facturas, fechas).
     * Solo disponible en estado borrador.
     */
    public function edit(Causacion $causacion)
    {
        if (!$causacion->esBorrador()) {
            return redirect()->route('presupuesto.causaciones.show', $causacion)
                ->with('error', 'Solo se pueden editar causaciones en estado Borrador.');
        }
        return view('presupuesto.causaciones.edit', compact('causacion'));
    }

    // ── ACTUALIZAR ────────────────────────────────────────────────────
    
    /**
     * Guarda la edición básica.
     */
    public function update(Request $request, Causacion $causacion)
    {
        if (!$causacion->esBorrador()) {
            return back()->with('error', 'Solo editables en Borrador.');
        }

        $request->validate([
            'tipo_documento'        => 'required|in:factura,contrato,recibo,planilla,otro',
            'numero_documento'      => 'nullable|string|max:60',
            'fecha_documento'       => 'nullable|date',
            'descripcion_documento' => 'nullable|string|max:300',
            'concepto'              => 'required|string|max:500',
            'fecha_causacion'       => 'required|date',
            'monto_causado'         => 'required|numeric|min:0.01',
            'monto_retencion'       => 'nullable|numeric|min:0',
            'observaciones'         => 'nullable|string',
        ]);

        $causacion->update([
            'tipo_documento'        => $request->tipo_documento,
            'numero_documento'      => $request->numero_documento,
            'fecha_documento'       => $request->fecha_documento,
            'descripcion_documento' => $request->descripcion_documento,
            'concepto'              => $request->concepto,
            'fecha_causacion'       => $request->fecha_causacion,
            'monto_causado'         => $request->monto_causado,
            'monto_retencion'       => $request->monto_retencion ?? 0,
            'observaciones'         => $request->observaciones,
        ]);

        return redirect()->route('presupuesto.causaciones.show', $causacion)
            ->with('success', 'Causación actualizada correctamente.');
    }

    // ── APROBAR ───────────────────────────────────────────────────────
    
    /**
     * Verifica la validez del documento y consolida la deuda.
     * Delega al Service que marca la causación y genera el Pago en estado borrador.
     */
    public function aprobar(Causacion $causacion)
    {
        try {
            $this->service->aprobar($causacion);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('presupuesto.causaciones.show', $causacion)
            ->with('success', 'Causación aprobada y lista para pago.');
    }

    // ── PAGAR (marcar como lista para pago) ──────────────────────────
    
    /**
     * Alias de marcaje manual a estado 'pagada'. 
     * Normalmente este estado se cambia automáticamente desde el PagoController,
     * pero existe aquí como utilidad rápida si el módulo de caja es manual.
     */
    public function pagar(Causacion $causacion)
    {
        if (!$causacion->esAprobada()) {
            return back()->with('error', 'Solo se pueden pagar causaciones Aprobadas.');
        }

        $causacion->update([
            'estado'     => 'pagada',
            'fecha_pago' => now()->toDateString(),
        ]);

        return redirect()->route('presupuesto.causaciones.show', $causacion)
            ->with('success', 'Causación marcada como pagada.');
    }

    // ── ANULAR ────────────────────────────────────────────────────────
    
    /**
     * Proceso de reverso del devengado.
     * Delega al Service, quien verifica que no existan pagos procesados.
     * Al anular la Causación, se reversa el estado del Compromiso a 'Aprobado'
     * y las causaciones quedan en 'Anulado'.
     */
    public function anular(AnularRequest $request, Causacion $causacion)
    {

        try {
            $this->service->anular($causacion, $request->motivo_anulacion);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('presupuesto.causaciones.index')
            ->with('success', 'Causación anulada. El compromiso fue restaurado a Aprobado.');
    }
}
