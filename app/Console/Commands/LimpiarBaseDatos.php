<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LimpiarBaseDatos extends Command
{
    protected $signature   = 'sia:limpiar-bd {--confirmar : Omite la pregunta de confirmación}';
    protected $description = 'Elimina todos los datos transaccionales y de usuarios, conservando solo roles y permisos. Para uso en preparación de implantación.';

    /**
     * Tablas que CONSERVAR (Spatie Permission + estructura de sesiones).
     * TODO lo demás se trunca.
     */
    private array $conservar = [
        'migrations',
        'roles',
        'permissions',
        'role_has_permissions',
        'model_has_roles',
        'model_has_permissions',
        'cache',
        'cache_locks',
        'job_batches',
    ];

    /**
     * Orden de limpieza respetando FK (hijos antes que padres).
     */
    private array $tablas = [
        // Cola y auditoría
        'jobs',
        'failed_jobs',
        'audits',
        'sessions',
        'password_reset_tokens',

        // Ingresos / Caja
        'arqueos_caja',
        'ingresos',
        'cajas',
        'conceptos_ingreso',

        // Nómina
        'empleado_bonificaciones',
        'nominas_detalle',
        'nominas',
        'empleado_familiares',
        'empleado_formaciones',
        'empleado_historial_cargos',
        'empleados',
        'cargos',
        'conceptos_nomina',

        // Retenciones
        'retenciones_aplicadas',
        'retenciones',

        // Contabilidad
        'asientos_detalle',
        'asientos_contables',
        'conciliacion_detalles',
        'conciliaciones_bancarias',
        'periodos_contables',
        'cuentas_contables',

        // Bienes
        'movimientos_bien',
        'bienes',
        'categorias_bien',

        // Almacén / Compras
        'solicitudes_despacho_detalle',
        'solicitudes_despacho',
        'recepciones_detalle',
        'recepciones_bienes',
        'inventario_movimientos',
        'ordenes_compra_detalle',
        'ordenes_compra',
        'solicitudes_compra_detalle',
        'solicitudes_compra',
        'articulos',
        'almacenes',

        // Presupuesto (hijos primero)
        'movimientos_partidas',
        'ordenes_pago_detalle',
        'ordenes_pago',
        'pagos',
        'causaciones',
        'compromisos',
        'modificaciones_presupuestarias',
        'creditos_presupuestarios',
        'partidas_presupuestarias',

        // Banco / Beneficiarios
        'movimientos_bancarios',
        'cuentas_bancarias',
        'beneficiarios',

        // Configuración fiscal / estructural
        'actividades',
        'proyectos_sia',
        'fuentes_financiamiento',
        'ejercicios_fiscales',
        'unidades_ejecutoras',

        // Usuarios (al final para no romper FK de auditores etc.)
        'users',
    ];

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <fg=red;options=bold>⚠  ADVERTENCIA: OPERACIÓN IRREVERSIBLE ⚠</>');
        $this->line('  Se eliminarán <options=bold>TODOS</> los datos transaccionales, catálogos y usuarios.');
        $this->line('  Solo se conservarán los <options=bold>roles y permisos</>.');
        $this->newLine();

        if (!$this->option('confirmar')) {
            if (!$this->confirm('  ¿Estás seguro de que deseas continuar?', false)) {
                $this->info('  Operación cancelada.');
                return 0;
            }
            $second = $this->ask('  Escribe "LIMPIAR" para confirmar definitivamente');
            if ($second !== 'LIMPIAR') {
                $this->error('  Confirmación incorrecta. Operación cancelada.');
                return 1;
            }
        }

        $this->newLine();
        $this->info('  Iniciando limpieza...');

        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

        $errores = [];
        foreach ($this->tablas as $tabla) {
            try {
                DB::table($tabla)->truncate();
                $this->line("  <fg=green>✓</> {$tabla}");
            } catch (\Throwable $e) {
                $errores[] = $tabla;
                $this->line("  <fg=yellow>⚠</> {$tabla} — omitida ({$e->getMessage()})");
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

        $this->newLine();

        if (empty($errores)) {
            $this->info('  ✅ Base de datos limpiada correctamente.');
        } else {
            $this->warn('  ⚠ Limpieza completada con advertencias en: ' . implode(', ', $errores));
        }

        $this->line('  Roles y permisos conservados intactos.');
        $this->line('  El sistema está listo para configuración inicial de implantación.');
        $this->newLine();

        return 0;
    }
}
