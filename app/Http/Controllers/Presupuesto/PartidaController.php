<?php

namespace App\Http\Controllers\Presupuesto;

use App\Http\Controllers\Controller;
use App\Models\CausacionRetencion;
use App\Models\CreditoPresupuestario;
use App\Models\Causacion;
use App\Models\Compromiso;
use App\Models\MovimientoPartida;
use App\Models\Pago;
use App\Models\PartidaPresupuestaria;
use App\Models\CuentaBancaria;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class PartidaController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:partidas.ver', only: ['index', 'show']),
            new Middleware('can:partidas.crear', only: ['create', 'store']),
            new Middleware('can:partidas.editar', only: ['edit', 'update']),
            new Middleware('can:partidas.eliminar', only: ['destroy']),
        ];
    }
    public function index(Request $request)
    {
        $partidas = PartidaPresupuestaria::when($request->search, fn($q, $s) =>
                $q->where('codigo', 'like', "%$s%")->orWhere('descripcion', 'like', "%$s%"))
            ->orderBy('codigo')
            ->paginate(20)->withQueryString();

        return view('presupuesto.partidas.index', compact('partidas'));
    }

    public function create()
    {
        $cuentas = CuentaBancaria::activas()->orderBy('nombre')->get();
        return view('presupuesto.partidas.create', compact('cuentas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo'             => ['required','string','max:20','unique:partidas_presupuestarias,codigo',
                                     'regex:/^4\.\d{2}\.\d{2}\.\d{2}\.\d{2}$/'],
            'descripcion'        => 'required|string|max:300',
            'cuenta_bancaria_id' => 'nullable|exists:cuentas_bancarias,id',
            'saldo_actual'       => 'nullable|numeric|min:0',
            'observaciones'      => 'nullable|string',
        ]);

        // Derivar niveles jerárquicos del código ONAPRE: 4.XX.XX.XX.XX
        $partes = explode('.', $data['codigo']);  // [4, XX, XX, XX, XX]
        $data['generica']      = $partes[0] . '.' . ($partes[1] ?? '00');           // 4.XX
        $data['especifica']    = $data['generica'] . '.' . ($partes[2] ?? '00');    // 4.XX.XX
        $data['subespecifica'] = $data['especifica'] . '.' . ($partes[3] ?? '00'); // 4.XX.XX.XX
        $data['activo']       = true;
        $data['saldo_actual'] = $data['saldo_actual'] ?? 0;
        // monto_aprobado se fija al crear y nunca cambia
        $data['monto_aprobado'] = $data['saldo_actual'];

        PartidaPresupuestaria::create($data);

        return redirect()->route('presupuesto.partidas.index')
            ->with('success', "Partida {$data['codigo']} creada correctamente.");
    }

    public function edit(PartidaPresupuestaria $partida)
    {
        $cuentas = CuentaBancaria::activas()->orderBy('nombre')->get();
        return view('presupuesto.partidas.edit', compact('partida', 'cuentas'));
    }

    public function update(Request $request, PartidaPresupuestaria $partida)
    {
        $data = $request->validate([
            'codigo'             => ['required','string','max:20',
                                     "unique:partidas_presupuestarias,codigo,{$partida->id}",
                                     'regex:/^4\.\d{2}\.\d{2}\.\d{2}\.\d{2}$/'],
            'descripcion'        => 'required|string|max:300',
            'cuenta_bancaria_id' => 'nullable|exists:cuentas_bancarias,id',
            'saldo_actual'       => 'nullable|numeric|min:0',
            'observaciones'      => 'nullable|string',
            'activo'             => 'boolean',
        ]);

        // Derivar niveles jerárquicos del código ONAPRE
        $partes = explode('.', $data['codigo']);
        $data['generica']      = $partes[0] . '.' . ($partes[1] ?? '00');
        $data['especifica']    = $data['generica'] . '.' . ($partes[2] ?? '00');
        $data['subespecifica'] = $data['especifica'] . '.' . ($partes[3] ?? '00');
        $data['activo'] = $request->boolean('activo', true);

        // Si monto_aprobado nunca fue fijado (0), establecerlo con el saldo_actual actual
        if ((float)$partida->monto_aprobado === 0.0 && isset($data['saldo_actual']) && (float)$data['saldo_actual'] > 0) {
            $data['monto_aprobado'] = $data['saldo_actual'];
        }
        // Nunca permitir que monto_aprobado baje si ya fue fijado

        $partida->update($data);

        return redirect()->route('presupuesto.partidas.index')
            ->with('success', "Partida {$partida->codigo} actualizada.");
    }

    public function destroy(PartidaPresupuestaria $partida)
    {
        $codigo = $partida->codigo;

        DB::transaction(function () use ($partida) {
            // 1. IDs de causaciones vinculadas a la partida
            $causacionIds = Causacion::where('partida_presupuestaria_id', $partida->id)
                ->pluck('id');

            // 2. Pagos vinculados a esas causaciones
            if ($causacionIds->isNotEmpty()) {
                $pagoIds = Pago::whereIn('causacion_id', $causacionIds)->pluck('id');

                // 3. Retenciones aplicadas a pagos y causaciones
                DB::table('retenciones_aplicadas')
                    ->where(function ($q) use ($pagoIds, $causacionIds) {
                        $q->where(function ($q2) use ($pagoIds) {
                            $q2->where('retencionable_type', 'App\Models\Pago')
                               ->whereIn('retencionable_id', $pagoIds);
                        })->orWhere(function ($q2) use ($causacionIds) {
                            $q2->where('retencionable_type', 'App\Models\Causacion')
                               ->whereIn('retencionable_id', $causacionIds);
                        });
                    })->delete();

                // 4. Pagos
                Pago::whereIn('id', $pagoIds)->forceDelete();
            }

            // 5. Causaciones
            Causacion::where('partida_presupuestaria_id', $partida->id)->forceDelete();

            // 6. Compromisos
            Compromiso::where('partida_presupuestaria_id', $partida->id)->forceDelete();

            // 7. Créditos presupuestarios
            CreditoPresupuestario::where('partida_presupuestaria_id', $partida->id)->forceDelete();

            // 8. Movimientos (partida principal y contrapartida)
            MovimientoPartida::where('partida_presupuestaria_id', $partida->id)
                ->orWhere('partida_contrapartida_id', $partida->id)
                ->forceDelete();

            // 9. Eliminar la partida misma (forceDelete para borrado físico)
            $partida->forceDelete();
        });

        return redirect()->route('presupuesto.partidas.index')
            ->with('success', "Partida {$codigo} y todos sus movimientos eliminados permanentemente.");
    }
}
