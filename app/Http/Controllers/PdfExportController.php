<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Controlador que permite al usuario descargar archivos PDF
 * que fueron generados en segundo plano por un Job.
 *
 * Flujo:
 *   1. Usuario solicita un reporte pesado → Job despachado → respuesta inmediata
 *   2. El Job guarda el PDF en storage/app/pdf-exports/{userId}/
 *   3. El usuario recarga la página o entra a /pdf-exports para ver sus archivos listos
 *   4. Este controlador sirve la descarga del archivo
 */
class PdfExportController extends Controller
{
    /**
     * Lista los archivos PDF generados para el usuario actual.
     */
    public function index()
    {
        $userId    = auth()->id();
        $directorio = "pdf-exports/{$userId}";
        $archivos  = [];

        if (Storage::exists($directorio)) {
            $archivos = collect(Storage::files($directorio))
                ->map(fn($path) => [
                    'nombre'    => basename($path),
                    'tamano'    => Storage::size($path),
                    'fecha'     => Storage::lastModified($path),
                    'url'       => route('pdf-exports.download', ['archivo' => basename($path)]),
                ])
                ->sortByDesc('fecha')
                ->values();
        }

        return view('pdf-exports.index', compact('archivos'));
    }

    /**
     * Descarga un archivo PDF generado previamente.
     */
    public function download(string $archivo)
    {
        $userId = auth()->id();
        $path   = "pdf-exports/{$userId}/{$archivo}";

        abort_unless(Storage::exists($path), 404, 'El archivo no existe o ya fue eliminado.');

        // Validación de seguridad: solo el dueño puede descargar
        abort_unless(
            str_starts_with($path, "pdf-exports/{$userId}/"),
            403,
            'No tienes permiso para descargar este archivo.'
        );

        return Storage::download($path, $archivo, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Elimina un archivo PDF generado.
     */
    public function destroy(string $archivo)
    {
        $userId = auth()->id();
        $path   = "pdf-exports/{$userId}/{$archivo}";

        if (Storage::exists($path)) {
            Storage::delete($path);
        }

        return back()->with('success', 'Archivo eliminado.');
    }
}
