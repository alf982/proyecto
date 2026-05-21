<?php

namespace App\Http\Controllers\Retenciones;

use App\Http\Controllers\Controller;
use App\Models\Retencion;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RetencionCalculoController extends Controller implements HasMiddleware
{
    /**
     * Sólo usuarios con permiso de crear causaciones o pagos pueden
     * llamar al endpoint de cálculo de retenciones.
     * Este endpoint es interno (AJAX), accedido desde formularios de causación y pago.
     */
    public static function middleware(): array
    {
        return [
            // El operador | indica OR: basta con tener uno de los dos permisos.
            // Usuarios que crean causaciones o pagos pueden calcular retenciones.
            new Middleware('permission:causaciones.crear|pagos.crear'),
        ];
    }

    /**
     * Recibe un monto_base y una lista de retencion_ids.
     * Devuelve JSON con el desglose de cada retención y el total retenido.
     *
     * Body esperado:
     * {
     *   "monto_base": 1500.00,
     *   "retenciones": [1, 3, 5]
     * }
     */
    public function calcular(Request $request)
    {
        $validated = $request->validate([
            'monto_base'    => 'required|numeric|min:0',
            'retenciones'   => 'required|array',
            'retenciones.*' => 'integer|exists:retenciones,id',
        ]);

        $montoBase   = (float) $validated['monto_base'];
        $retenciones = Retencion::activas()
            ->whereIn('id', $validated['retenciones'])
            ->get();

        $desglose     = [];
        $totalRetenido = 0.0;
        $montoActual   = $montoBase;

        foreach ($retenciones as $ret) {
            // Si la base es 'monto_neto', usamos el monto ya descontado hasta ese punto
            $base   = $ret->base_calculo === 'monto_neto' ? $montoActual : $montoBase;
            $monto  = $ret->calcularMonto($base);

            $desglose[] = [
                'id'             => $ret->id,
                'codigo'         => $ret->codigo,
                'nombre'         => $ret->nombre,
                'tipo_label'     => $ret->tipo_label,
                'monto_base'     => $base,
                'monto_retenido' => $monto,
            ];

            $totalRetenido += $monto;
            $montoActual   -= $monto;
        }

        return response()->json([
            'monto_base'    => $montoBase,
            'total_retenido'=> round($totalRetenido, 2),
            'monto_neto'    => round($montoBase - $totalRetenido, 2),
            'desglose'      => $desglose,
        ]);
    }
}
