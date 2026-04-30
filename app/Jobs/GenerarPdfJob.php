<?php

namespace App\Jobs;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Job para generar PDFs pesados en segundo plano.
 *
 * Uso:
 *   GenerarPdfJob::dispatch('pdf.inventario_bienes', $datos, 'inventario-2024.pdf', $userId);
 *
 * Cuando termina, el archivo queda en storage/app/pdf-exports/{userId}/
 * y el usuario puede descargarlo sin bloquear el servidor.
 */
class GenerarPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120; // 2 minutos máximo por PDF

    public function __construct(
        private readonly string $view,
        private readonly array  $datos,
        private readonly string $nombreArchivo,
        private readonly int    $userId,
        private readonly string $orientacion = 'portrait',
        private readonly string $tamano      = 'letter',
    ) {}

    public function handle(): void
    {
        Log::info("GenerarPdfJob: Generando {$this->nombreArchivo} para usuario #{$this->userId}");

        $pdf = Pdf::loadView($this->view, $this->datos)
            ->setPaper($this->tamano, $this->orientacion);

        $directorio = "pdf-exports/{$this->userId}";
        Storage::makeDirectory($directorio);
        Storage::put("{$directorio}/{$this->nombreArchivo}", $pdf->output());

        Log::info("GenerarPdfJob: {$this->nombreArchivo} guardado en storage.");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("GenerarPdfJob: Falló la generación de {$this->nombreArchivo}: {$e->getMessage()}");
    }
}
