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
use App\Traits\GuardaRetenciones;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

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
    public function index(Request $request)
    {
        $ejercicioId = session('ejercicio_id');

        $nominas = Nomina::with(['ejercicioFiscal', 'creadoPor'])
            ->when($ejercicioId, fn($q) => $q->where('ejercicio_fiscal_id', $ejercicioId))
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
        $partidas = PartidaPresupuestaria::where('activo', true)
            ->orderBy('codigo')
            ->get();
        return view('nomina.nominas.create', compact('ejercicio', 'partidas'));
    }

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

        // Calcular de forma síncrona (no requiere queue worker)
        CalcularNominaJob::dispatchSync($nomina);

        // Guardar retenciones con el total_neto ya calculado por el Job
        if (!empty($idsRetenciones)) {
            $nomina->refresh(); // trae el total_neto actualizado
            $this->guardarRetenciones($nomina, $idsRetenciones, (float) $nomina->total_neto);
        }

        return redirect()->route('nomina.nominas.show', $nomina)
            ->with('success', 'Nómina creada y calculada correctamente.');
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
        $partidas = PartidaPresupuestaria::where('activo', true)
            ->orderBy('codigo')
            ->get();
        return view('nomina.nominas.show', compact('nomina', 'partidas'));
    }

    public function aprobar(Nomina $nomina)
    {
        abort_if($nomina->estado !== 'calculada', 403, 'Solo se pueden aprobar nóminas calculadas.');
        $nomina->update(['estado' => 'aprobada', 'aprobado_por' => auth()->id(), 'fecha_aprobacion' => now()]);
        return back()->with('success', 'Nómina aprobada correctamente.');
    }

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

            if ($monto > $saldoAnt) {
                throw new \Exception(
                    'Saldo insuficiente en la partida. Disponible: Bs. ' . number_format($saldoAnt, 2) .
                    ' / Requerido: Bs. ' . number_format($monto, 2)
                );
            }

            $saldoPost = $saldoAnt - $monto;
            $partida->decrement('saldo_actual', $monto);

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
