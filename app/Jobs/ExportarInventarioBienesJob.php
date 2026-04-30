<?php

namespace App\Jobs;

use App\Models\Bien;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Job para exportar el inventario completo de bienes a PDF.
 * El inventario puede tener cientos de filas — no debe bloquear el HTTP request.
 */
class ExportarInventarioBienesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 180;

    public function __construct(
        private readonly int    $userId,
        private readonly ?int   $categoriaId  = null,
        private readonly ?int   $unidadId     = null,
        private readonly ?string $estado      = null,
    ) {}

    public function handle(): void
    {
        Log::info("ExportarInventarioBienesJob: Iniciando exportación para usuario #{$this->userId}");

        $bienes = Bien::with(['categoria', 'unidadEjecutora'])
            ->when($this->categoriaId, fn($q) => $q->where('categoria_bien_id', $this->categoriaId))
            ->when($this->unidadId,    fn($q) => $q->where('unidad_ejecutora_id', $this->unidadId))
            ->when($this->estado,      fn($q) => $q->where('estado', $this->estado))
            ->orderBy('numero_inventario')
            ->get();

        $nombreArchivo = 'inventario-bienes-' . now()->format('Y-m-d-His') . '.pdf';

        $pdf = Pdf::loadView('pdf.inventario_bienes', compact('bienes'))
            ->setPaper('legal', 'landscape');

        $directorio = "pdf-exports/{$this->userId}";
        Storage::makeDirectory($directorio);
        Storage::put("{$directorio}/{$nombreArchivo}", $pdf->output());

        Log::info("ExportarInventarioBienesJob: {$nombreArchivo} generado. {$bienes->count()} bienes.");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("ExportarInventarioBienesJob: Falló para usuario #{$this->userId}: {$e->getMessage()}");
    }
}
