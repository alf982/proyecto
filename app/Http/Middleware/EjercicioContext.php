<?php
namespace App\Http\Middleware;

use App\Models\EjercicioFiscal;
use Closure;
use Illuminate\Http\Request;

class EjercicioContext
{
    public function handle(Request $request, Closure $next)
    {
        // Solo aplica a usuarios autenticados
        if (!auth()->check()) {
            return $next($request);
        }

        $session     = $request->session();
        $ejercicioId = $session->get('ejercicio_id');

        // Si no hay ejercicio en sesión, usar el activo automáticamente
        if (!$ejercicioId) {
            $ejercicio = EjercicioFiscal::where('estado', 'activo')->first();
            if ($ejercicio) {
                $session->put('ejercicio_id', $ejercicio->id);
                $ejercicioId = $ejercicio->id;
            }
        }

        $ejercicio = $ejercicioId
            ? EjercicioFiscal::find($ejercicioId)
            : null;

        // Si el ejercicio ya no existe, limpiar sesión y buscar uno activo
        if (!$ejercicio) {
            $session->forget('ejercicio_id');
            $ejercicio = EjercicioFiscal::where('estado', 'activo')->first();
            if ($ejercicio) {
                $session->put('ejercicio_id', $ejercicio->id);
            }
        }

        // Compartir con todas las vistas y el contenedor
        view()->share('ejercicioActual', $ejercicio);
        app()->instance('ejercicioActual', $ejercicio);

        return $next($request);
    }
}
