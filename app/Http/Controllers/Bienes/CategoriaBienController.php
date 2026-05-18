<?php
namespace App\Http\Controllers\Bienes;

use App\Http\Controllers\Controller;
use App\Models\CategoriaBien;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Controlador de Categorías de Bienes Nacionales
 * 
 * Gestiona el catálogo de clasificación de activos fijos.
 * Estas categorías son fundamentales para el módulo ya que dictan
 * los porcentajes de depreciación anual y la vida útil estimada de los bienes.
 */
class CategoriaBienController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:bienes.ver', only: ['index', 'show']),
            new Middleware('can:bienes.crear', only: ['create', 'store']),
            new Middleware('can:bienes.editar', only: ['edit', 'update', 'destroy']),
        ];
    }
    
    /**
     * Muestra el catálogo de categorías con el conteo de bienes asignados.
     */
    public function index()
    {
        // Carga el conteo de bienes para evitar subconsultas en la vista
        $categorias = CategoriaBien::withCount('bienes')->orderBy('codigo')->get();
        return view('bienes.categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('bienes.categorias.create');
    }

    /**
     * Registra una nueva categoría con sus parámetros contables de depreciación.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codigo'            => 'required|unique:categorias_bien,codigo|max:20',
            'nombre'            => 'required|string|max:150',
            'vida_util_anios'   => 'required|integer|min:1|max:99',
            'tasa_depreciacion' => 'required|numeric|min:0|max:100',
        ]);
        
        CategoriaBien::create($request->only(['codigo', 'nombre', 'vida_util_anios', 'tasa_depreciacion', 'descripcion']) + ['activo' => true]);
        
        return redirect()->route('bienes.categorias.index')->with('success', 'Categoría creada correctamente.');
    }

    public function edit(CategoriaBien $categoria)
    {
        return view('bienes.categorias.edit', compact('categoria'));
    }

    /**
     * Actualiza la categoría. Nota: los cambios en tasa de depreciación
     * solo afectan a los bienes que se registren o deprecien a partir de ahora,
     * o requieren recálculo del módulo contable.
     */
    public function update(Request $request, CategoriaBien $categoria)
    {
        $request->validate([
            'codigo'            => 'required|max:20|unique:categorias_bien,codigo,' . $categoria->id,
            'nombre'            => 'required|string|max:150',
            'vida_util_anios'   => 'required|integer|min:1|max:99',
            'tasa_depreciacion' => 'required|numeric|min:0|max:100',
        ]);
        
        $categoria->update($request->only(['codigo', 'nombre', 'vida_util_anios', 'tasa_depreciacion', 'descripcion']) + ['activo' => (bool)$request->activo]);
        
        return redirect()->route('bienes.categorias.index')->with('success', 'Categoría actualizada.');
    }
}
