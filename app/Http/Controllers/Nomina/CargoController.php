<?php
namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use Illuminate\Http\Request;

class CargoController extends Controller
{
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
