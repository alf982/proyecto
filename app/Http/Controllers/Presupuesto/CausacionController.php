<?php

namespace App\Http\Controllers\Presupuesto;

use App\Http\Controllers\Controller;
use App\Models\Causacion;
use App\Models\Compromiso;
use App\Models\EjercicioFiscal;
use App\Models\MovimientoPartida;
use App\Models\UnidadEjecutora;
use App\Traits\GuardaRetenciones;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class CausacionController extends Controller implements HasMiddleware
{
    use GuardaRetenciones;
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

        $ejercicios = EjercicioFiscal::orderBy('anio', 'desc')->get();
        $unidades   = UnidadEjecutora::activas()->orderBy('nombre')->get();

        return view('presupuesto.causaciones.index', compact('q', 'ejercicios', 'unidades'));
    }

    // ── CREAR ──────────────────────────────────────────────────────
    public function create(Request $request)
    {
        // Compromisos aprobados disponibles SIN causación activa aún
        $compromisos = Compromiso::with(['partida', 'unidadEjecutora'])
            ->where('estado', 'aprobado')
            ->whereDoesntHave('causaciones', fn($q) => $q->whereNotIn('estado', ['anulada']))
            ->orderByDesc('fecha_compromiso')
            ->get();

        // Si viene desde el show de un compromiso
        $compromisoSeleccionado = $request->compromiso_id
            ? Compromiso::with(['partida', 'unidadEjecutora'])->find($request->compromiso_id)
            : null;

        return view('presupuesto.causaciones.create', compact('compromisos', 'compromisoSeleccionado'));
    }

    // ── GUARDAR ───────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'compromiso_id'         => 'required|exists:compromisos,id',
            'tipo_documento'        => 'required|in:factura,contrato,recibo,planilla,otro',
            'numero_documento'      => 'nullable|string|max:60',
            'fecha_documento'       => 'nullable|date',
            'descripcion_documento' => 'nullable|string|max:300',
            'concepto'              => 'required|string|max:500',
            'fecha_causacion'       => 'required|date',
            'monto_sin_iva'         => 'nullable|numeric|min:0',
            'alicuota_iva'          => 'nullable|numeric|min:0|max:100',
            'monto_causado'         => 'required|numeric|min:0.01',
            'monto_retencion'       => 'nullable|numeric|min:0',
            'observaciones'         => 'nullable|string',
            'retenciones'           => 'nullable|array',
            'retenciones.*'         => 'integer|exists:retenciones,id',
        ]);

        $idsRetenciones = $request->input('retenciones', []);

        $compromiso = Compromiso::with('partida')->findOrFail($request->compromiso_id);

        if (!$compromiso->esAprobado()) {
            return back()->withErrors(['compromiso_id' => 'El compromiso debe estar Aprobado.'])->withInput();
        }

        // ── GUARDIA ANTI-DUPLICACIÓN ──────────────────────────────────────
        $causacionExistente = $compromiso->causaciones()
            ->whereNotIn('estado', ['anulada'])
            ->first();

        if ($causacionExistente) {
            return redirect()
                ->route('presupuesto.causaciones.show', $causacionExistente)
                ->with('error', 'Este compromiso ya tiene una causación registrada (' . $causacionExistente->numero . '). Edita o anula la existente.');
        }
        // ─────────────────────────────────────────────────────────
        $montoCausado = (float) $request->monto_causado;
        $totalRetencion = $this->calcularTotalRetenciones($idsRetenciones, $montoCausado);
        if ($montoCausado > (float) $compromiso->monto) {
            return back()
                ->withErrors(['monto_causado' => 'El monto causado (Bs. ' . number_format($montoCausado, 2) . ') supera el monto del compromiso (Bs. ' . number_format($compromiso->monto, 2) . ').'])
                ->withInput();
        }

        $ejercicio = EjercicioFiscal::where('estado', 'activo')->first();

        DB::transaction(function () use ($request, $compromiso, $ejercicio, $montoCausado) {
            $causacion = Causacion::create([
                'compromiso_id'             => $compromiso->id,
                'numero'                    => Causacion::generarNumero(now()->year),
                'ejercicio_fiscal_id'       => $compromiso->ejercicio_fiscal_id ?? $ejercicio?->id,
                'unidad_ejecutora_id'       => $compromiso->unidad_ejecutora_id,
                'partida_presupuestaria_id' => $compromiso->partida_presupuestaria_id,
                'proyecto_id'               => $compromiso->proyecto_id,
                'beneficiario'              => $compromiso->beneficiario,
                'rif_beneficiario'          => $compromiso->rif_beneficiario,
                'tipo_documento'            => $request->tipo_documento,
                'numero_documento'          => $request->numero_documento,
                'fecha_documento'           => $request->fecha_documento,
                'descripcion_documento'     => $request->descripcion_documento,
                'concepto'                  => $request->concepto,
                'fecha_causacion'           => $request->fecha_causacion,
                'monto_sin_iva'             => $request->monto_sin_iva ?: null,
                'alicuota_iva'              => $request->alicuota_iva ?: null,
                'monto_causado'             => $montoCausado,
                'monto_retencion'           => $totalRetencion,
                'observaciones'             => $request->observaciones,
                'estado'                    => 'borrador',
                'created_by'                => auth()->id(),
            ]);

            // Guardar retenciones en tabla polimórfica
            $this->guardarRetenciones($causacion, $idsRetenciones, $montoCausado);

            // Cambiar estado del compromiso a 'causado'
            $compromiso->update(['estado' => 'causado']);

            // Registrar movimiento de causación en la partida
            if ($compromiso->partida) {
                $saldoAnt  = (float) $compromiso->partida->saldo_actual;
                // El saldo ya fue descontado al crear el compromiso, solo registramos el movimiento de trazabilidad
                MovimientoPartida::create([
                    'numero'                    => MovimientoPartida::generarNumero(now()->year),
                    'partida_presupuestaria_id' => $compromiso->partida_presupuestaria_id,
                    'cuenta_bancaria_id'        => $compromiso->partida->cuenta_bancaria_id,
                    'ejercicio_fiscal_id'       => $compromiso->ejercicio_fiscal_id ?? $ejercicio?->id,
                    'tipo'                      => 'causacion',
                    'concepto'                  => 'Causación ' . $causacion->numero . ' del compromiso ' . $compromiso->numero,
                    'monto'                     => $montoCausado,
                    'fecha_movimiento'          => $request->fecha_causacion,
                    'referencia'                => $causacion->numero,
                    'saldo_anterior'            => $saldoAnt,
                    'saldo_posterior'           => $saldoAnt, // ya descontado en compromiso
                    'estado'                    => 'confirmado',
                    'creado_por'                => auth()->id(),
                ]);
            }
        });

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
        if (!$causacion->esBorrador()) {
            return back()->with('error', 'Solo se aprueban causaciones en Borrador.');
        }

        DB::transaction(function () use ($causacion) {
            $causacion->update([
                'estado'           => 'aprobada',
                'fecha_aprobacion' => now()->toDateString(),
                'aprobado_por'     => auth()->id(),
            ]);

            // Si tiene compromiso vinculado en estado 'aprobado', avanzarlo a 'causado'
            if ($causacion->compromiso_id) {
                $compromiso = Compromiso::find($causacion->compromiso_id);
                if ($compromiso?->esAprobado()) {
                    $compromiso->update(['estado' => 'causado']);
                }
            }
        });

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
    public function anular(Request $request, Causacion $causacion)
    {
        if ($causacion->esPagada()) {
            return back()->with('error', 'No se pueden anular causaciones ya pagadas.');
        }

        $request->validate(['motivo_anulacion' => 'required|string|min:10']);

        DB::transaction(function () use ($request, $causacion) {
            $causacion->update([
                'estado'           => 'anulada',
                'motivo_anulacion' => $request->motivo_anulacion,
            ]);

            // Revertir el compromiso a "aprobado" para que pueda volver a causarse
            if ($causacion->compromiso) {
                $causacion->compromiso->update(['estado' => 'aprobado']);
            }
        });

        return redirect()->route('presupuesto.causaciones.index')
            ->with('success', 'Causación anulada. El compromiso fue restaurado a Aprobado.');
    }
}
