<?php
namespace App\Http\Controllers\Tesoreria;

use App\Http\Controllers\Controller;
use App\Models\CuentaBancaria;
use App\Models\EjercicioFiscal;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Controlador de Cuentas Bancarias (Tesorería)
 * 
 * Gestiona el catálogo de cuentas bancarias de la institución.
 * Este submódulo es clave para la conciliación bancaria, ya que 
 * conecta la ejecución presupuestaria (Partidas) con la realidad 
 * financiera en bancos (Saldos líquidos).
 * 
 * Responsabilidades:
 * - Registro y edición de cuentas (Corriente, Ahorro, Fondos de Terceros).
 * - Seguimiento del saldo_actual (Que se afecta con los Pagos y Depósitos).
 * - Vinculación con Ejercicios Fiscales y Partidas Presupuestarias.
 */
class CuentaBancariaController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:tesoreria.cuentas.ver', only: ['index', 'show']),
            new Middleware('can:tesoreria.cuentas.crear', only: ['create', 'store']),
            new Middleware('can:tesoreria.cuentas.editar', only: ['edit', 'update', 'destroy']),
        ];
    }
    
    /**
     * Muestra el listado de cuentas bancarias.
     * Muestra indicadores de Saldo Total y vinculación con partidas.
     */
    public function index(Request $request)
    {
        $cuentas = CuentaBancaria::with(['ejercicioFiscal'])
            ->withCount('partidasPresupuestarias')
            ->when($request->estado, fn($q, $v) => $q->where('estado', $v))
            ->orderBy('nombre')
            ->paginate(20)->withQueryString();

        $totalSaldo = CuentaBancaria::activas()->sum('saldo_actual');
        $totalPartidas = \App\Models\PartidaPresupuestaria::whereNotNull('cuenta_bancaria_id')->count();

        return view('tesoreria.cuentas.index', compact('cuentas', 'totalSaldo', 'totalPartidas'));
    }

    public function create()
    {
        $ejercicios = EjercicioFiscal::orderByDesc('anio')->get();
        return view('tesoreria.cuentas.create', compact('ejercicios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'         => 'required|string|max:150',
            'banco'          => 'required|string|max:100',
            'numero_cuenta'  => 'required|string|max:30|unique:cuentas_bancarias,numero_cuenta',
            'tipo'           => 'required|in:corriente,ahorro,fondo,otro',
            'moneda'         => 'required|string|max:10',
            'saldo_inicial'  => 'required|numeric|min:0',
            'fecha_apertura' => 'nullable|date',
            'firmante_1'     => 'nullable|string|max:150',
            'firmante_2'     => 'nullable|string|max:150',
            'ejercicio_fiscal_id' => 'nullable|exists:ejercicios_fiscales,id',
            'observaciones'  => 'nullable|string',
        ]);

        CuentaBancaria::create([
            'codigo'         => CuentaBancaria::generarCodigo(),
            'nombre'         => $request->nombre,
            'banco'          => $request->banco,
            'numero_cuenta'  => $request->numero_cuenta,
            'tipo'           => $request->tipo,
            'moneda'         => $request->moneda,
            'saldo_inicial'  => $request->saldo_inicial,
            'saldo_actual'   => $request->saldo_inicial,
            'fecha_apertura' => $request->fecha_apertura,
            'estado'         => 'activa',
            'firmante_1'     => $request->firmante_1,
            'firmante_2'     => $request->firmante_2,
            'ejercicio_fiscal_id' => $request->ejercicio_fiscal_id,
            'observaciones'  => $request->observaciones,
            'creado_por'     => auth()->id(),
        ]);

        return redirect()->route('tesoreria.cuentas.index')
            ->with('success', 'Cuenta bancaria registrada correctamente.');
    }

    /**
     * Vista de detalle de la cuenta.
     * Muestra los últimos movimientos financieros y las partidas presupuestarias fondeadas por esta cuenta.
     */
    public function show(CuentaBancaria $cuenta)
    {
        $cuenta->load([
            'movimientos' => fn($q) => $q->orderByDesc('fecha_movimiento')->limit(20),
            'partidasPresupuestarias' => fn($q) => $q->orderBy('codigo'),
        ]);
        $movimientosPendientes = $cuenta->movimientos()->where('estado', 'pendiente')->count();
        $saldoDesdePartidas    = $cuenta->partidasPresupuestarias->sum('saldo_actual');
        $totalPartidas         = $cuenta->partidasPresupuestarias->count();
        return view('tesoreria.cuentas.show',
            compact('cuenta', 'movimientosPendientes', 'saldoDesdePartidas', 'totalPartidas'));
    }

    public function edit(CuentaBancaria $cuenta)
    {
        $ejercicios = EjercicioFiscal::orderByDesc('anio')->get();
        return view('tesoreria.cuentas.edit', compact('cuenta', 'ejercicios'));
    }

    public function update(Request $request, CuentaBancaria $cuenta)
    {
        $request->validate([
            'nombre'         => 'required|string|max:150',
            'banco'          => 'required|string|max:100',
            'numero_cuenta'  => 'required|string|max:30|unique:cuentas_bancarias,numero_cuenta,'.$cuenta->id,
            'tipo'           => 'required|in:corriente,ahorro,fondo,otro',
            'moneda'         => 'required|string|max:10',
            'firmante_1'     => 'nullable|string|max:150',
            'firmante_2'     => 'nullable|string|max:150',
            'estado'         => 'required|in:activa,inactiva,bloqueada',
            'observaciones'  => 'nullable|string',
        ]);

        $cuenta->update($request->except(['codigo', 'saldo_inicial', 'saldo_actual', 'creado_por']));

        return redirect()->route('tesoreria.cuentas.index')
            ->with('success', 'Cuenta actualizada correctamente.');
    }

    public function destroy(CuentaBancaria $cuenta)
    {
        if ($cuenta->movimientos()->exists()) {
            return back()->with('error', 'No se puede eliminar una cuenta con movimientos registrados.');
        }
        $cuenta->delete();
        return redirect()->route('tesoreria.cuentas.index')
            ->with('success', 'Cuenta bancaria eliminada.');
    }
}
