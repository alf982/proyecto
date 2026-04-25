<?php

namespace App\Http\Controllers\Presupuesto;

use App\Http\Controllers\Controller;
use App\Models\EjercicioFiscal;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class EjercicioFiscalController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:ejercicios.ver', only: ['index', 'show']),
            new Middleware('can:ejercicios.crear', only: ['create', 'store']),
            new Middleware('can:ejercicios.editar', only: ['edit', 'update', 'activar']),
            new Middleware('can:ejercicios.cerrar', only: ['cerrar']),
            new Middleware('can:ejercicios.editar', only: ['destroy']),
        ];
    }
    public function index()
    {
        $ejercicios = EjercicioFiscal::with('creadoPor')
            ->orderByDesc('anio')->paginate(15);
        return view('presupuesto.ejercicios.index', compact('ejercicios'));
    }

    public function create()
    {
        return view('presupuesto.ejercicios.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'anio'          => 'required|integer|min:2000|max:2100|unique:ejercicios_fiscales,anio',
            'fecha_inicio'  => 'required|date',
            'fecha_fin'     => 'required|date|after:fecha_inicio',
            'observaciones' => 'nullable|string',
        ]);

        EjercicioFiscal::create($data + [
            'estado'     => 'borrador',
            'creado_por' => auth()->id(),
        ]);

        return redirect()->route('presupuesto.ejercicios.index')
            ->with('success', "Ejercicio fiscal {$data['anio']} creado correctamente.");
    }

    public function edit(EjercicioFiscal $ejercicio)
    {
        return view('presupuesto.ejercicios.edit', compact('ejercicio'));
    }

    public function update(Request $request, EjercicioFiscal $ejercicio)
    {
        if ($ejercicio->estado === 'cerrado') {
            return back()->with('error', 'No se puede modificar un ejercicio cerrado.');
        }

        $data = $request->validate([
            'anio'          => "required|integer|min:2000|max:2100|unique:ejercicios_fiscales,anio,{$ejercicio->id}",
            'fecha_inicio'  => 'required|date',
            'fecha_fin'     => 'required|date|after:fecha_inicio',
            'observaciones' => 'nullable|string',
        ]);

        $ejercicio->update($data);

        return redirect()->route('presupuesto.ejercicios.index')
            ->with('success', "Ejercicio {$ejercicio->anio} actualizado.");
    }

    public function activar(EjercicioFiscal $ejercicio)
    {
        DB::transaction(function () use ($ejercicio) {
            EjercicioFiscal::where('estado', 'activo')->update(['estado' => 'borrador']);
            $ejercicio->update(['estado' => 'activo']);
        });

        return back()->with('success', "Ejercicio {$ejercicio->anio} activado como ejercicio fiscal vigente.");
    }

    public function cerrar(EjercicioFiscal $ejercicio)
    {
        $ejercicio->update([
            'estado'      => 'cerrado',
            'cerrado_por' => auth()->id(),
            'fecha_cierre'=> now(),
        ]);

        return back()->with('success', "Ejercicio {$ejercicio->anio} cerrado definitivamente.");
    }

    public function destroy(EjercicioFiscal $ejercicio)
    {
        if ($ejercicio->estado !== 'borrador') {
            return back()->with('error', 'Solo se pueden eliminar ejercicios en borrador.');
        }
        $ejercicio->delete();
        return redirect()->route('presupuesto.ejercicios.index')
            ->with('success', "Ejercicio {$ejercicio->anio} eliminado.");
    }
}
