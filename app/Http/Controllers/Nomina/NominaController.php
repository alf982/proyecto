<?php
namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Http\Requests\Nomina\StoreNominaRequest;
use App\Jobs\CalcularNominaJob;
use App\Models\ConceptoNomina;
use App\Models\Empleado;
use App\Models\EjercicioFiscal;
use App\Models\MovimientoPartida;
use App\Models\Nomina;
use App\Models\NominaDetalle;
use App\Models\PartidaPresupuestaria;
use App\Services\CatalogoCache;
use App\Traits\GuardaRetenciones;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

/**
 * Controlador de Generación y Pago de Nómina
 * 
 * Centraliza la lógica financiera del talento humano.
 * Operaciones clave:
 * 1. Cálculo Masivo: Offloaded a `CalcularNominaJob` vía Queues para evitar timeouts.
 * 2. Transacción de Pago: Operación atómica que asegura que el pago de la nómina
 *    debilite instantáneamente el saldo de la Partida Presupuestaria asignada.
 */
class NominaController extends Controller implements HasMiddleware
{
    use GuardaRetenciones;
    
    public static function middleware(): array
    {
        return [
            new Middleware('can:nomina.ver', only: ['index', 'show']),
            new Middleware('can:nomina.crear', only: ['create', 'store']),
            new Middleware('can:nomina.aprobar', only: ['aprobar']),
            new Middleware('can:nomina.pagar', only: ['pagar']),
            new Middleware('can:nomina.anular', only: ['anular']),
        ];
    }
    
    /**
     * Bandeja principal de corridas de nómina con filtros por estado.
     */
    public function index(Request $request)
    {
        $nominas = Nomina::with(['ejercicioFiscal', 'creadoPor'])
            ->when($request->estado,      fn($q, $v) => $q->where('estado', $v))
            ->when($request->tipo_nomina, fn($q, $v) => $q->where('tipo_nomina', $v))
            ->orderByDesc('id')
            ->paginate(20)->withQueryString();
            
        return view('nomina.nominas.index', compact('nominas'));
    }

    public function create()
    {
        $ejercicio = session('ejercicio_id')
            ? \App\Models\EjercicioFiscal::find(session('ejercicio_id'))
            : EjercicioFiscal::where('estado', 'activo')->first();
            
        $partidas = CatalogoCache::partidas();
        return view('nomina.nominas.create', compact('ejercicio', 'partidas'));
    }

    /**
     * Genera la corrida salarial.
     * El cálculo real se delega a un Job en background (Queue) debido a
     * la intensidad de iterar sobre todos los empleados y conceptos activos.
     */
    public function store(StoreNominaRequest $request)
    {
        $idsRetenciones = $request->input('retenciones', []);

        $ejercicio = session('ejercicio_id')
            ? EjercicioFiscal::findOrFail(session('ejercicio_id'))
            : EjercicioFiscal::where('estado', 'activo')->firstOrFail();

        $nomina = DB::transaction(function () use ($request, $ejercicio) {
            return Nomina::create([
                'numero'                    => Nomina::generarNumero(now()->year),
                'ejercicio_fiscal_id'       => $ejercicio->id,
                'partida_presupuestaria_id' => $request->partida_presupuestaria_id ?: null,
                'tipo_nomina'               => $request->tipo_nomina,
                'periodo_inicio'            => $request->periodo_inicio,
                'periodo_fin'               => $request->periodo_fin,
                'estado'                    => 'borrador',
                'observaciones'             => $request->observaciones,
                'creado_por'                => auth()->id(),
            ]);
        });

        // Calcular en segundo plano (queue worker procesa el job)
        // Las retenciones se aplican dentro del Job, después del cálculo del total_neto
        CalcularNominaJob::dispatch($nomina, $idsRetenciones);

        return redirect()->route('nomina.nominas.show', $nomina)
            ->with('info', 'Nómina creada. El cálculo se está procesando en segundo plano — recarga en unos segundos para ver los resultados.');
    }

    private function calcularNomina(Nomina $nomina): void
    {
        $empleados = Empleado::activos()->with('cargo')->get();
        $conceptos = ConceptoNomina::activos()->get();
        $totalAsignaciones = 0;
        $totalDeducciones  = 0;

        foreach ($empleados as $empleado) {
            $salarioBase    = (float) $empleado->cargo->salario_base;
            $asignaciones   = 0;
            $deducciones    = 0;
            $conceptosApp   = [];

            // Sueldo base siempre es asignación
            $asignaciones += $salarioBase;
            $conceptosApp[] = ['codigo' => 'SUELDO', 'nombre' => 'Sueldo Base', 'tipo' => 'asignacion', 'monto' => $salarioBase];

            foreach ($conceptos as $concepto) {
                // Filtrar por tipo de empleado
                if ($concepto->aplica_a !== 'todos' && $concepto->aplica_a !== $empleado->tipo . 's') continue;

                $monto = $concepto->calcularMonto($salarioBase);
                if ($concepto->tipo === 'asignacion') {
                    $asignaciones += $monto;
                } else {
                    $deducciones += $monto;
                }
                $conceptosApp[] = ['codigo' => $concepto->codigo, 'nombre' => $concepto->nombre, 'tipo' => $concepto->tipo, 'calculo' => $concepto->calculo, 'valor' => $concepto->valor, 'monto' => $monto];
            }

            $neto = $asignaciones - $deducciones;
            NominaDetalle::create([
                'nomina_id'           => $nomina->id,
                'empleado_id'         => $empleado->id,
                'salario_base'        => $salarioBase,
                'total_asignaciones'  => $asignaciones,
                'total_deducciones'   => $deducciones,
                'neto'                => $neto,
                'conceptos_aplicados' => $conceptosApp,
            ]);
            $totalAsignaciones += $asignaciones;
            $totalDeducciones  += $deducciones;
        }

        $nomina->update([
            'total_asignaciones' => $totalAsignaciones,
            'total_deducciones'  => $totalDeducciones,
            'total_neto'         => $totalAsignaciones - $totalDeducciones,
            'estado'             => 'calculada',
        ]);
    }

    public function show(Nomina $nomina)
    {
        $nomina->load(['ejercicioFiscal', 'creadoPor', 'aprobadoPor', 'partida', 'detalles.empleado.cargo']);
        $partidas = CatalogoCache::partidas();
        return view('nomina.nominas.show', compact('nomina', 'partidas'));
    }

    /**
     * Da el visto bueno administrativo antes del pago.
     */
    public function aprobar(Nomina $nomina)
    {
        abort_if($nomina->estado !== 'calculada', 403, 'Solo se pueden aprobar nóminas calculadas.');
        $nomina->update(['estado' => 'aprobada', 'aprobado_por' => auth()->id(), 'fecha_aprobacion' => now()]);
        return back()->with('success', 'Nómina aprobada correctamente.');
    }

    /**
     * Lógica Financiera Crítica (Transacción Atómica):
     * Vincula el módulo de Nómina con el Módulo de Presupuesto.
     * Descuenta el total_neto directamente del 'saldo_actual' de la partida
     * y registra el movimiento de gasto. Si no hay saldo, la DB hace rollback.
     */
    public function pagar(Request $request, Nomina $nomina)
    {
        abort_if($nomina->estado !== 'aprobada', 403, 'Solo se pueden pagar nóminas aprobadas.');

        $request->validate([
            'partida_presupuestaria_id' => 'required|exists:partidas_presupuestarias,id',
        ], [
            'partida_presupuestaria_id.required' => 'Debe seleccionar una partida presupuestaria para procesar el pago.',
        ]);

        DB::transaction(function () use ($request, $nomina) {
            $partida  = PartidaPresupuestaria::findOrFail($request->partida_presupuestaria_id);
            $monto    = (float) $nomina->total_neto;
            $saldoAnt = (float) $partida->saldo_actual;

            // Validación estricta de disponibilidad presupuestaria
            if ($monto > $saldoAnt) {
                throw new \Exception(
                    'Saldo insuficiente en la partida. Disponible: Bs. ' . number_format($saldoAnt, 2) .
                    ' / Requerido: Bs. ' . number_format($monto, 2)
                );
            }

            $saldoPost = $saldoAnt - $monto;
            
            // Deducción del saldo de la partida
            $partida->decrement('saldo_actual', $monto);

            // Registro histórico del gasto
            MovimientoPartida::create([
                'numero'                    => MovimientoPartida::generarNumero(now()->year),
                'partida_presupuestaria_id' => $partida->id,
                'ejercicio_fiscal_id'       => $nomina->ejercicio_fiscal_id,
                'tipo'                      => 'pago',
                'concepto'                  => 'Pago Nómina ' . $nomina->numero .
                                               ' (' . ucfirst(str_replace('_', ' ', $nomina->tipo_nomina)) . ')' .
                                               ' Período: ' . $nomina->periodo_inicio->format('d/m/Y') .
                                               ' — ' . $nomina->periodo_fin->format('d/m/Y'),
                'monto'                     => $monto,
                'fecha_movimiento'          => now(),
                'referencia'                => $nomina->numero,
                'saldo_anterior'            => $saldoAnt,
                'saldo_posterior'           => $saldoPost,
                'estado'                    => 'confirmado',
                'creado_por'                => auth()->id(),
            ]);

            $nomina->update([
                'estado'                    => 'pagada',
                'partida_presupuestaria_id' => $partida->id,
            ]);
        });

        return back()->with('success', 'Nómina pagada y saldo de partida descontado correctamente.');
    }

    public function anular(Nomina $nomina)
    {
        abort_if($nomina->estado === 'pagada', 403, 'No se puede anular una nómina pagada.');
        $nomina->update(['estado' => 'anulada']);
        return back()->with('success', 'Nómina anulada.');
    }
}
