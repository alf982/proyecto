<?php

namespace App\Http\Controllers;

use App\Models\CategoriaBien;
use App\Models\ArqueoCaja;
use App\Models\Bien;
use App\Models\Causacion;
use App\Models\Compromiso;
use App\Models\Ingreso;
use App\Models\Nomina;
use App\Models\NominaDetalle;
use App\Models\OrdenCompra;
use App\Models\OrdenPago;
use App\Models\Pago;
use App\Models\RecepcionBienes;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    // ── CAUSACIÓN ─────────────────────────────────────────────────────────────

    public function causacion(Causacion $causacion)
    {
        $this->authorize('causaciones.ver');
        $causacion->load(['ejercicioFiscal', 'unidadEjecutora', 'partida', 'credito', 'proyecto', 'creadoPor', 'aprobadoPor']);

        $pdf = Pdf::loadView('pdf.causacion', compact('causacion'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream("causacion-{$causacion->numero}.pdf");
    }

    // ── PAGO ──────────────────────────────────────────────────────────────────

    public function pago(Pago $pago)
    {
        $this->authorize('pagos.ver');
        $pago->load(['causacion.partida', 'causacion.proyecto', 'ejercicioFiscal', 'unidadEjecutora', 'creadoPor', 'retenciones.retencion']);

        $pdf = Pdf::loadView('pdf.pago', compact('pago'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream("pago-{$pago->numero}.pdf");
    }

    // ── COMPROBANTE DE RETENCIÓN ──────────────────────────────────────────────

    public function retencionAplicada(\App\Models\RetencionAplicada $retencionAplicada)
    {
        $this->authorize('pagos.ver');
        $retencionAplicada->load(['retencion', 'retencionable']);

        $pdf = Pdf::loadView('pdf.comprobante_retencion', compact('retencionAplicada'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream("comprobante-retencion-{$retencionAplicada->id}.pdf");
    }

    // ── ORDEN DE PAGO ─────────────────────────────────────────────────────────

    public function ordenPago(OrdenPago $orden)
    {
        $this->authorize('tesoreria.ordenes.ver');
        $orden->load(['unidadEjecutora', 'beneficiario', 'causacion', 'detalles', 'creadoPor', 'revisadoPor', 'aprobadoPor']);

        $pdf = Pdf::loadView('pdf.orden_pago', compact('orden'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream("orden-pago-{$orden->numero}.pdf");
    }

    // ── NÓMINA (reporte general) ─────────────────────────────────────

    public function nomina(Nomina $nomina)
    {
        $this->authorize('nomina.ver');
        $nomina->load(['ejercicioFiscal', 'creadoPor', 'aprobadoPor', 'detalles.empleado.cargo']);

        $pdf = Pdf::loadView('pdf.nomina', compact('nomina'))
            ->setPaper('legal', 'landscape');

        return $pdf->stream("nomina-{$nomina->numero}.pdf");
    }

    // ── RECIBO INDIVIDUAL POR EMPLEADO ───────────────────────────
    public function reciboEmpleado(Nomina $nomina, NominaDetalle $detalle)
    {
        $this->authorize('nomina.ver');
        abort_unless($detalle->nomina_id === $nomina->id, 403);

        $nomina->load(['aprobadoPor']);
        $detalle->load(['empleado.cargo']);

        $pdf = Pdf::loadView('pdf.nomina_recibo', compact('nomina', 'detalle'))
            ->setPaper('letter', 'portrait');

        $empleadoNombre = str_replace(' ', '_', $detalle->empleado?->nombre_completo ?? 'empleado');
        return $pdf->stream("recibo-{$nomina->numero}-{$empleadoNombre}.pdf");
    }

    // ── RECIBO DE INGRESO ─────────────────────────────────────────────────────

    public function recibo(Ingreso $ingreso)
    {
        $this->authorize('ingresos.ver');
        $ingreso->load(['caja.unidadEjecutora', 'concepto', 'creadoPor']);

        $pdf = Pdf::loadView('pdf.recibo_ingreso', compact('ingreso'))
            ->setPaper([0, 0, 595, 420], 'portrait'); // A5 landscape

        return $pdf->stream("recibo-{$ingreso->numero_recibo}.pdf");
    }

    // ── ARQUEO DE CAJA ────────────────────────────────────────────────────────

    public function arqueo(ArqueoCaja $arqueo)
    {
        $this->authorize('ingresos.arqueos.ver');
        $arqueo->load(['caja.unidadEjecutora', 'creadoPor', 'aprobadoPor']);

        $pdf = Pdf::loadView('pdf.arqueo_caja', compact('arqueo'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream("arqueo-caja-{$arqueo->id}.pdf");
    }

    // ── ORDEN DE COMPRA ───────────────────────────────────────────────────────

    public function ordenCompra(OrdenCompra $orden)
    {
        $this->authorize('compras.ordenes.ver');
        $orden->load(['detalles.articulo', 'creadoPor', 'beneficiario', 'partida', 'solicitud']);

        $pdf = Pdf::loadView('pdf.orden_compra', compact('orden'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream("orden-compra-{$orden->numero}.pdf");
    }

    // ── ACTA DE RECEPCIÓN DE BIENES ──────────────────────────────────────────

    public function recepcionBienes(RecepcionBienes $recepcion)
    {
        $this->authorize('compras.recepciones.ver');
        $recepcion->load(['orden.beneficiario', 'detalles.articulo', 'detalles.ordenDetalle', 'creadoPor']);

        $pdf = Pdf::loadView('pdf.recepcion_bienes', compact('recepcion'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream("recepcion-{$recepcion->numero}.pdf");
    }

    // ── INVENTARIO DE BIENES (síncrono — stream directo) ──────────────────────
    /**
     * Genera el PDF del inventario de bienes directamente y lo muestra
     * en el visor del navegador (mismo comportamiento que los otros PDFs).
     */
    public function inventarioBienes(Request $request)
    {
        $this->authorize('bienes.ver');

        $bienes = Bien::with(['categoria', 'unidadEjecutora'])
            ->when($request->integer('categoria') ?: null, fn($q, $v) => $q->where('categoria_bien_id', $v))
            ->when($request->integer('unidad') ?: null,    fn($q, $v) => $q->where('unidad_ejecutora_id', $v))
            ->when($request->estado ?: null,               fn($q, $v) => $q->where('estado', $v))
            ->orderBy('numero_inventario')
            ->get();

        $nombreArchivo = 'inventario-bienes-' . now()->format('Y-m-d') . '.pdf';

        $pdf = Pdf::loadView('pdf.inventario_bienes', compact('bienes'))
            ->setPaper('legal', 'landscape');

        return $pdf->stream($nombreArchivo);
    }
    // ── COMPROMISO PRESUPUESTARIO ──────────────────────────────────────────
    public function compromiso(Compromiso $compromiso)
    {
        $this->authorize('compromisos.ver');
        $compromiso->load(['ejercicioFiscal', 'unidadEjecutora', 'partida', 'proyecto', 'creadoPor', 'aprobadoPor', 'beneficiarioModel']);

        $pdf = Pdf::loadView('pdf.compromiso', compact('compromiso'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream("compromiso-{$compromiso->numero}.pdf");
    }
}
