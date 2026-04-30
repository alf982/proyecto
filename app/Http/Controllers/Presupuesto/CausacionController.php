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

class CausacionController extends Controller implements HasMiddleware
{
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

        return view('presupuesto.causaciones.index', compact('q', 'ejercicios', 'unidades'));
    }

    // ── CREAR ──────────────────────────────────────────────────────
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
    public function show(Causacion $causacion)
    {
        $causacion->load(['ejercicioFiscal', 'unidadEjecutora', 'partida', 'compromiso', 'proyecto', 'creadoPor', 'aprobadoPor', 'pagos']);
        return view('presupuesto.causaciones.show', compact('causacion'));
    }

    // ── EDITAR ────────────────────────────────────────────────────────
    public function edit(Causacion $causacion)
    {
        if (!$causacion->esBorrador()) {
            return redirect()->route('presupuesto.causaciones.show', $causacion)
                ->with('error', 'Solo se pueden editar causaciones en estado Borrador.');
        }
        return view('presupuesto.causaciones.edit', compact('causacion'));
    }

    // ── ACTUALIZAR ────────────────────────────────────────────────────
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
