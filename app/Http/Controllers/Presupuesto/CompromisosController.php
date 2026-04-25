<?php
namespace App\Http\Controllers\Presupuesto;

use App\Http\Controllers\Controller;
use App\Models\Beneficiario;
use App\Models\Causacion;
use App\Models\Compromiso;
use App\Models\EjercicioFiscal;
use App\Models\MovimientoPartida;
use App\Models\PartidaPresupuestaria;
use App\Models\ProyectoSia;
use App\Models\UnidadEjecutora;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class CompromisosController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:compromisos.ver',    only: ['index', 'show']),
            new Middleware('can:compromisos.crear',  only: ['create', 'store', 'edit', 'update']),
            new Middleware('can:compromisos.aprobar',only: ['aprobar']),
            new Middleware('can:compromisos.anular', only: ['anular']),
        ];
    }

    // ── LISTADO ──────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $q = Compromiso::with(['ejercicioFiscal', 'unidadEjecutora', 'partida'])
            ->when($request->ejercicio, fn($q, $v) => $q->where('ejercicio_fiscal_id', $v))
            ->when($request->estado,    fn($q, $v) => $q->where('estado', $v))
            ->when($request->search,    fn($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('numero', 'like', "%$v%")->orWhere('beneficiario', 'like', "%$v%");
            }))
            ->orderByDesc('numero')
            ->paginate(15)->withQueryString();

        $ejercicios = EjercicioFiscal::orderByDesc('anio')->get();
        $unidades   = UnidadEjecutora::activas()->orderBy('nombre')->get();
        return view('presupuesto.compromisos.index', compact('q', 'ejercicios', 'unidades'));
    }

    // ── CREAR ─────────────────────────────────────────────────────────
    public function create()
    {
        $ejercicio     = EjercicioFiscal::where('estado', 'activo')->first();
        $unidades      = UnidadEjecutora::activas()->orderBy('nombre')->get();
        $partidas      = PartidaPresupuestaria::where('activo', true)->orderBy('codigo')->get();
        $proyectos     = ProyectoSia::whereIn('estado', ['activo', 'formulacion'])->orderBy('nombre')->get();
        $beneficiarios = Beneficiario::activos()->orderBy('razon_social')->get();

        return view('presupuesto.compromisos.create', compact('ejercicio', 'unidades', 'partidas', 'proyectos', 'beneficiarios'));
    }

    // ── GUARDAR ───────────────────────────────────────────────────────
    // Al guardar crea el Compromiso + genera automáticamente la Causación en borrador
    public function store(Request $request)
    {
        $request->validate([
            'partida_presupuestaria_id' => 'required|exists:partidas_presupuestarias,id',
            'unidad_ejecutora_id'       => 'required|exists:unidades_ejecutoras,id',
            'beneficiario_id'           => 'nullable|exists:beneficiarios,id',
            'beneficiario'              => 'nullable|string|max:200',
            'concepto'                  => 'required|string|max:500',
            'monto_sin_iva'             => 'nullable|numeric|min:0',
            'alicuota_iva'              => 'nullable|numeric|min:0|max:100',
            'monto'                     => 'required|numeric|min:0.01',
            'fecha_compromiso'          => 'required|date',
            'fecha_vencimiento'         => 'nullable|date|after_or_equal:fecha_compromiso',
            'tipo_documento'            => 'nullable|in:factura,contrato,recibo,planilla,otro',
            'numero_documento'          => 'nullable|string|max:60',
            'fecha_documento'           => 'nullable|date',
            'descripcion_documento'     => 'nullable|string|max:300',
            'observaciones'             => 'nullable|string',
        ]);

        // Resolver beneficiario
        if ($request->beneficiario_id) {
            $benModel  = Beneficiario::findOrFail($request->beneficiario_id);
            $nombreBen = $benModel->razon_social;
            $rifBen    = $benModel->rif;
        } else {
            if (empty(trim($request->beneficiario ?? ''))) {
                return back()->withErrors(['beneficiario' => 'Seleccione un beneficiario del catálogo o escriba el nombre.'])->withInput();
            }
            $nombreBen = ucwords(strtolower($request->beneficiario));
            $rifBen    = $request->rif_beneficiario;
        }

        $partida    = PartidaPresupuestaria::findOrFail($request->partida_presupuestaria_id);
        $disponible = (float) $partida->saldo_actual;

        if ((float) $request->monto > $disponible) {
            return back()
                ->withErrors(['monto' => 'Supera el saldo disponible en la partida: Bs. ' . number_format($disponible, 2)])
                ->withInput();
        }

        $ejercicio = EjercicioFiscal::where('estado', 'activo')->first();

        DB::transaction(function () use ($request, $partida, $ejercicio, $nombreBen, $rifBen) {
            $monto  = (float) $request->monto;
            $saldoAnt  = (float) $partida->saldo_actual;
            $saldoPost = $saldoAnt - $monto;

            // 1. Crear el Compromiso
            $compromiso = Compromiso::create([
                'numero'                    => Compromiso::generarNumero(now()->year),
                'ejercicio_fiscal_id'       => $ejercicio?->id,
                'unidad_ejecutora_id'       => $request->unidad_ejecutora_id,
                'partida_presupuestaria_id' => $partida->id,
                'proyecto_id'               => $request->proyecto_id ?: null,
                'beneficiario_id'           => $request->beneficiario_id ?: null,
                'beneficiario'              => $nombreBen,
                'rif_beneficiario'          => $rifBen,
                'concepto'                  => $request->concepto,
                'monto_sin_iva'             => $request->monto_sin_iva ?: null,
                'alicuota_iva'              => $request->alicuota_iva ?: null,
                'monto'                     => $monto,
                'tipo_documento'            => $request->tipo_documento,
                'numero_documento'          => $request->numero_documento,
                'fecha_documento'           => $request->fecha_documento,
                'descripcion_documento'     => $request->descripcion_documento,
                'fecha_compromiso'          => $request->fecha_compromiso,
                'fecha_vencimiento'         => $request->fecha_vencimiento,
                'observaciones'             => $request->observaciones,
                'estado'                    => 'borrador',
                'created_by'                => auth()->id(),
            ]);

            // 2. Generar movimiento presupuestario tipo 'compromiso'
            MovimientoPartida::create([
                'numero'                    => MovimientoPartida::generarNumero(now()->year),
                'partida_presupuestaria_id' => $partida->id,
                'cuenta_bancaria_id'        => $partida->cuenta_bancaria_id,
                'ejercicio_fiscal_id'       => $ejercicio?->id,
                'tipo'                      => 'compromiso',
                'concepto'                  => 'Compromiso ' . $compromiso->numero . ' — ' . $nombreBen,
                'monto'                     => $monto,
                'fecha_movimiento'          => $request->fecha_compromiso,
                'referencia'                => $compromiso->numero,
                'saldo_anterior'            => $saldoAnt,
                'saldo_posterior'           => $saldoPost,
                'estado'                    => 'confirmado',
                'creado_por'                => auth()->id(),
            ]);

            // 3. Descontar saldo de la partida
            $partida->update(['saldo_actual' => $saldoPost]);
        });

        return redirect()->route('presupuesto.compromisos.index')
            ->with('success', 'Compromiso registrado correctamente. Apruébalo para generar la causación.');
    }

    // ── DETALLE ───────────────────────────────────────────────────────
    public function show(Compromiso $compromiso)
    {
        $compromiso->load(['ejercicioFiscal', 'unidadEjecutora', 'partida', 'proyecto', 'creadoPor', 'aprobadoPor', 'causaciones']);
        return view('presupuesto.compromisos.show', compact('compromiso'));
    }

    // ── EDITAR ────────────────────────────────────────────────────────
    public function edit(Compromiso $compromiso)
    {
        if (!$compromiso->esBorrador()) {
            return redirect()->route('presupuesto.compromisos.show', $compromiso)
                ->with('error', 'Solo se pueden editar compromisos en Borrador.');
        }
        $proyectos = ProyectoSia::whereIn('estado', ['activo', 'formulacion'])->orderBy('nombre')->get();
        return view('presupuesto.compromisos.edit', compact('compromiso', 'proyectos'));
    }

    // ── ACTUALIZAR ────────────────────────────────────────────────────
    public function update(Request $request, Compromiso $compromiso)
    {
        if (!$compromiso->esBorrador()) return back()->with('error', 'Solo editables en Borrador.');

        $request->validate([
            'beneficiario'         => 'required|string|max:200',
            'concepto'             => 'required|string|max:500',
            'monto_sin_iva'        => 'nullable|numeric|min:0',
            'alicuota_iva'         => 'nullable|numeric|min:0|max:100',
            'monto'                => 'required|numeric|min:0.01',
            'fecha_compromiso'     => 'required|date',
            'tipo_documento'       => 'nullable|in:factura,contrato,recibo,planilla,otro',
            'numero_documento'     => 'nullable|string|max:60',
            'fecha_documento'      => 'nullable|date',
            'descripcion_documento'=> 'nullable|string|max:300',
        ]);

        $diferencia = (float) $request->monto - (float) $compromiso->monto;

        if ($diferencia > 0 && $compromiso->partida) {
            $saldoDisponible = (float) $compromiso->partida->saldo_actual;
            if ($diferencia > $saldoDisponible) {
                return back()->withErrors(['monto' => 'El incremento supera el saldo disponible: Bs. ' . number_format($saldoDisponible, 2)])->withInput();
            }
        }

        DB::transaction(function () use ($request, $compromiso, $diferencia) {
            $compromiso->update([
                'beneficiario'          => ucwords(strtolower($request->beneficiario)),
                'rif_beneficiario'      => $request->rif_beneficiario,
                'concepto'              => $request->concepto,
                'monto_sin_iva'         => $request->monto_sin_iva ?: null,
                'alicuota_iva'          => $request->alicuota_iva ?: null,
                'monto'                 => $request->monto,
                'tipo_documento'        => $request->tipo_documento,
                'numero_documento'      => $request->numero_documento,
                'fecha_documento'       => $request->fecha_documento,
                'descripcion_documento' => $request->descripcion_documento,
                'fecha_compromiso'      => $request->fecha_compromiso,
                'fecha_vencimiento'     => $request->fecha_vencimiento,
                'observaciones'         => $request->observaciones,
            ]);

            if (abs($diferencia) >= 0.01 && $compromiso->partida) {
                $compromiso->partida->decrement('saldo_actual', $diferencia);
            }

            // Sincronizar también la causación en borrador si existe
            $causacion = $compromiso->causaciones()->where('estado', 'borrador')->first();
            if ($causacion) {
                $causacion->update([
                    'beneficiario'          => ucwords(strtolower($request->beneficiario)),
                    'rif_beneficiario'      => $request->rif_beneficiario,
                    'concepto'              => $request->concepto,
                    'monto_sin_iva'         => $request->monto_sin_iva ?: null,
                    'alicuota_iva'          => $request->alicuota_iva ?: null,
                    'monto_causado'         => $request->monto,
                    'tipo_documento'        => $request->tipo_documento,
                    'numero_documento'      => $request->numero_documento,
                    'fecha_documento'       => $request->fecha_documento,
                    'descripcion_documento' => $request->descripcion_documento,
                    'fecha_causacion'       => $request->fecha_compromiso,
                ]);
            }
        });

        return redirect()->route('presupuesto.compromisos.show', $compromiso)
            ->with('success', 'Compromiso actualizado.');
    }

    // ── APROBAR ───────────────────────────────────────────────────────
    public function aprobar(Compromiso $compromiso)
    {
        if (!$compromiso->esBorrador()) return back()->with('error', 'Solo se aprueban compromisos en Borrador.');

        DB::transaction(function () use ($compromiso) {
            $compromiso->update([
                'estado'           => 'aprobado',
                'fecha_aprobacion' => now()->toDateString(),
                'aprobado_por'     => auth()->id(),
            ]);

            // Crear la Causación en borrador solo al aprobar el compromiso
            Causacion::create([
                'compromiso_id'             => $compromiso->id,
                'numero'                    => Causacion::generarNumero(now()->year),
                'ejercicio_fiscal_id'       => $compromiso->ejercicio_fiscal_id,
                'unidad_ejecutora_id'       => $compromiso->unidad_ejecutora_id,
                'partida_presupuestaria_id' => $compromiso->partida_presupuestaria_id,
                'proyecto_id'               => $compromiso->proyecto_id,
                'beneficiario'              => $compromiso->beneficiario,
                'rif_beneficiario'          => $compromiso->rif_beneficiario,
                'tipo_documento'            => $compromiso->tipo_documento ?? 'otro',
                'numero_documento'          => $compromiso->numero_documento,
                'fecha_documento'           => $compromiso->fecha_documento,
                'descripcion_documento'     => $compromiso->descripcion_documento,
                'concepto'                  => $compromiso->concepto,
                'fecha_causacion'           => $compromiso->fecha_compromiso,
                // Propagar desglose IVA desde el compromiso
                'monto_sin_iva'             => $compromiso->monto_sin_iva,
                'alicuota_iva'              => $compromiso->alicuota_iva,
                'monto_causado'             => $compromiso->monto,
                'monto_retencion'           => 0,
                'observaciones'             => $compromiso->observaciones,
                'estado'                    => 'borrador',
                'created_by'                => auth()->id(),
            ]);
        });

        return redirect()->route('presupuesto.compromisos.show', $compromiso->fresh())
            ->with('success', 'Compromiso aprobado. La causación fue generada en borrador — complétala desde el módulo de Causaciones.');
    }

    // ── ANULAR ────────────────────────────────────────────────────────
    public function anular(Request $request, Compromiso $compromiso)
    {
        if ($compromiso->esAnulado()) return back()->with('error', 'Ya está anulado.');
        $request->validate(['motivo_anulacion' => 'required|string|min:10']);

        DB::transaction(function () use ($request, $compromiso) {
            // Anular causaciones en borrador o aprobadas (no las pagadas)
            $compromiso->causaciones()
                ->whereIn('estado', ['borrador', 'aprobada'])
                ->update([
                    'estado'           => 'anulada',
                    'motivo_anulacion' => 'Anulado con el compromiso: ' . $request->motivo_anulacion,
                ]);

            $compromiso->update([
                'estado'           => 'anulado',
                'motivo_anulacion' => $request->motivo_anulacion,
            ]);

            // Devolver el saldo a la partida
            if ($compromiso->partida) {
                $compromiso->partida->increment('saldo_actual', (float) $compromiso->monto);
            }
        });

        return redirect()->route('presupuesto.compromisos.index')
            ->with('success', 'Compromiso y su causación anulados. Saldo liberado en la partida.');
    }
}
