<?php
namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Models\ConceptoNomina;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ConceptoNominaController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:nomina.conceptos.ver', only: ['index', 'show']),
            new Middleware('can:nomina.conceptos.crear', only: ['create', 'store']),
            new Middleware('can:nomina.conceptos.editar', only: ['edit', 'update', 'destroy']),
        ];
    }
    public function index()
    {
        $conceptos = ConceptoNomina::orderBy('tipo')->orderBy('codigo')->get();
        return view('nomina.conceptos.index', compact('conceptos'));
    }

    public function create() { return view('nomina.conceptos.create'); }

    public function store(Request $request)
    {
        $request->validate([
            'codigo'   => 'required|unique:conceptos_nomina,codigo|max:20',
            'nombre'   => 'required|string|max:150',
            'tipo'     => 'required|in:asignacion,deduccion',
            'calculo'  => 'required|in:fijo,porcentaje',
            'valor'    => 'required|numeric|min:0',
            'aplica_a' => 'required|in:todos,fijos,contratados,obreros',
        ]);
        ConceptoNomina::create([
            ...$request->only(['codigo', 'nombre', 'tipo', 'calculo', 'valor', 'aplica_a', 'descripcion']),
            'es_obligatorio' => (bool)$request->es_obligatorio,
            'activo'         => true,
        ]);
        return redirect()->route('nomina.conceptos.index')->with('success', 'Concepto de nómina creado.');
    }

    public function edit(ConceptoNomina $concepto) { return view('nomina.conceptos.edit', compact('concepto')); }

    public function update(Request $request, ConceptoNomina $concepto)
    {
        $request->validate([
            'codigo'   => 'required|max:20|unique:conceptos_nomina,codigo,' . $concepto->id,
            'nombre'   => 'required|string|max:150',
            'tipo'     => 'required|in:asignacion,deduccion',
            'calculo'  => 'required|in:fijo,porcentaje',
            'valor'    => 'required|numeric|min:0',
            'aplica_a' => 'required|in:todos,fijos,contratados,obreros',
        ]);
        $concepto->update([
            ...$request->only(['codigo', 'nombre', 'tipo', 'calculo', 'valor', 'aplica_a', 'descripcion']),
            'es_obligatorio' => (bool)$request->es_obligatorio,
            'activo'         => (bool)$request->activo,
        ]);
        return redirect()->route('nomina.conceptos.index')->with('success', 'Concepto actualizado correctamente.');
    }
}
