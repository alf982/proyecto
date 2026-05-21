<?php
namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Controlador de Cargos y Tabuladores
 * 
 * Gestiona el catálogo de cargos de la institución.
 * Es crítico mantener la coherencia del salario_base ya que impacta
 * directamente en los cálculos de la próxima nómina generada.
 */
class CargoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:nomina.ver', only: ['index', 'show']),
            new Middleware('can:nomina.crear', only: ['create', 'store']),
            new Middleware('can:nomina.empleados.editar', only: ['edit', 'update']),
        ];
    }
    
    /**
     * Muestra el tabulador salarial base actual.
     */
    public function index()
    {
        $cargos = Cargo::withCount('empleados')->orderBy('codigo')->get();
        return view('nomina.cargos.index', compact('cargos'));
    }

    public function create() { return view('nomina.cargos.create'); }

    public function store(Request $request)
    {
        $request->validate([
            'codigo'       => 'required|unique:cargos,codigo|max:20',
            'nombre'       => 'required|string|max:150',
            'nivel'        => 'required|in:directivo,profesional,tecnico,administrativo,obrero',
            'salario_base' => 'required|numeric|min:0',
        ]);
        
        Cargo::create([...$request->only(['codigo', 'nombre', 'nivel', 'salario_base']), 'activo' => true]);
        
        return redirect()->route('nomina.cargos.index')->with('success', 'Cargo creado correctamente.');
    }

    public function edit(Cargo $cargo) { return view('nomina.cargos.edit', compact('cargo')); }

    /**
     * Actualiza el cargo. Modificar el salario_base afectará a todos los empleados
     * en este cargo en las próximas nóminas que se corran.
     */
    public function update(Request $request, Cargo $cargo)
    {
        $request->validate([
            'codigo'       => 'required|max:20|unique:cargos,codigo,' . $cargo->id,
            'nombre'       => 'required|string|max:150',
            'nivel'        => 'required|in:directivo,profesional,tecnico,administrativo,obrero',
            'salario_base' => 'required|numeric|min:0',
        ]);
        
        $cargo->update([...$request->only(['codigo', 'nombre', 'nivel', 'salario_base']), 'activo' => (bool)$request->activo]);
        
        return redirect()->route('nomina.cargos.index')->with('success', 'Cargo actualizado.');
    }
}
