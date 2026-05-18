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

/**
 * Controlador del Catálogo de Partidas Presupuestarias
 * 
 * Gestiona el árbol de clasificación de cuentas de gasto e ingresos
 * basándose en el clasificador presupuestario nacional (Ej: ONAPRE en Venezuela).
 * Maneja la creación de partidas, su relación con cuentas bancarias,
 * y una lógica estricta de borrado en cascada para mantenimientos correctivos.
 */
class PartidaController extends Controller implements HasMiddleware
{
    /**
     * Middleware de autorización de Spatie para restringir rutas.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:partidas.ver', only: ['index', 'show']),
            new Middleware('can:partidas.crear', only: ['create', 'store']),
            new Middleware('can:partidas.editar', only: ['edit', 'update']),
            new Middleware('can:partidas.eliminar', only: ['destroy']),
        ];
    }

    /**
     * Muestra el catálogo de partidas con soporte para búsqueda.
     */
    public function index(Request $request)
    {
        $partidas = PartidaPresupuestaria::when($request->search, fn($q, $s) =>
                $q->where('codigo', 'like', "%$s%")->orWhere('descripcion', 'like', "%$s%"))
            ->orderBy('codigo')
            ->paginate(20)->withQueryString();

        return view('presupuesto.partidas.index', compact('partidas'));
    }

    /**
     * Formulario de creación de una nueva partida.
     */
    public function create()
    {
        $cuentas = CuentaBancaria::activas()->orderBy('nombre')->get();
        return view('presupuesto.partidas.create', compact('cuentas'));
    }

    /**
     * Almacena una nueva partida presupuestaria.
     * Calcula automáticamente la jerarquía (Genérica, Específica, Sub-específica)
     * extrayendo los fragmentos del código formato: 4.XX.XX.XX.XX
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo'             => ['required','string','max:20','unique:partidas_presupuestarias,codigo',
                                     'regex:/^4\.\d{2}\.\d{2}\.\d{2}\.\d{2}$/'], // Validar formato presupuestario estricto
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
        
        // monto_aprobado se fija al crear y sirve de base inmutable (Presupuesto Ordinario)
        $data['monto_aprobado'] = $data['saldo_actual'];

        PartidaPresupuestaria::create($data);

        return redirect()->route('presupuesto.partidas.index')
            ->with('success', "Partida {$data['codigo']} creada correctamente.");
    }

    /**
     * Formulario para editar una partida.
     */
    public function edit(PartidaPresupuestaria $partida)
    {
        $cuentas = CuentaBancaria::activas()->orderBy('nombre')->get();
        return view('presupuesto.partidas.edit', compact('partida', 'cuentas'));
    }

    /**
     * Actualiza los datos de una partida presupuestaria.
     * Recalcula la jerarquía ONAPRE en caso de que el código haya cambiado.
     */
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
        // Nota de diseño: Nunca permitir que monto_aprobado baje o se altere por UI si ya fue fijado.
        // Debe alterarse solo por medio de 'Modificaciones Presupuestarias'.

        $partida->update($data);

        return redirect()->route('presupuesto.partidas.index')
            ->with('success', "Partida {$partida->codigo} actualizada.");
    }

    /**
     * ELIMINACIÓN FÍSICA EN CASCADA.
     * [PELIGRO]: Esta función realiza un borrado FORZADO profundo.
     * Solo debe usarse en mantenimientos tempranos o cuando el usuario 
     * tiene privilegio explícito para corregir un catálogo erróneo.
     */
    public function destroy(PartidaPresupuestaria $partida)
    {
        $codigo = $partida->codigo;

        // Se usa una transacción para asegurar que la cascada no quede a medias.
        DB::transaction(function () use ($partida) {
            // 1. IDs de causaciones vinculadas a la partida
            $causacionIds = Causacion::where('partida_presupuestaria_id', $partida->id)
                ->pluck('id');

            // 2. Pagos vinculados a esas causaciones
            if ($causacionIds->isNotEmpty()) {
                $pagoIds = Pago::whereIn('causacion_id', $causacionIds)->pluck('id');

                // 3. Eliminar Retenciones aplicadas a pagos y causaciones
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

                // 4. Borrar Pagos físicamente
                Pago::whereIn('id', $pagoIds)->forceDelete();
            }

            // 5. Borrar Causaciones físicamente
            Causacion::where('partida_presupuestaria_id', $partida->id)->forceDelete();

            // 6. Borrar Compromisos
            Compromiso::where('partida_presupuestaria_id', $partida->id)->forceDelete();

            // 7. Borrar Créditos presupuestarios asignados a la partida
            CreditoPresupuestario::where('partida_presupuestaria_id', $partida->id)->forceDelete();

            // 8. Borrar Movimientos Presupuestarios (Donde es origen o destino)
            MovimientoPartida::where('partida_presupuestaria_id', $partida->id)
                ->orWhere('partida_contrapartida_id', $partida->id)
                ->forceDelete();

            // 9. Finalmente, eliminar la partida misma (forceDelete para borrado físico)
            $partida->forceDelete();
        });

        return redirect()->route('presupuesto.partidas.index')
            ->with('success', "Partida {$codigo} y todos sus movimientos eliminados permanentemente.");
    }
}
