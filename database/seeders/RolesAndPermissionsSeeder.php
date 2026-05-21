<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permisos por módulo ────────────────────────────────────────
        $permisos = [
            // Dashboard
            'dashboard.ver',

            // Usuarios
            'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar',

            // Roles / Auditoría
            'roles.ver', 'roles.gestionar',

            // Unidades Ejecutoras
            'unidades.ver', 'unidades.crear', 'unidades.editar', 'unidades.eliminar',

            // Beneficiarios
            'beneficiarios.ver', 'beneficiarios.crear', 'beneficiarios.editar',

            // ── Presupuesto ──────────────────────────────────────────────
            'ejercicios.ver',       'ejercicios.crear',       'ejercicios.editar',       'ejercicios.cerrar',
            'partidas.ver',         'partidas.crear',         'partidas.editar',         'partidas.eliminar',
            'proyectos.ver',        'proyectos.crear',        'proyectos.editar',        'proyectos.eliminar',
            'creditos.ver',         'creditos.crear',         'creditos.editar',
            'compromisos.ver',      'compromisos.crear',      'compromisos.aprobar',     'compromisos.anular',
            'causaciones.ver',      'causaciones.crear',      'causaciones.aprobar',     'causaciones.anular',
            'pagos.ver',            'pagos.crear',            'pagos.procesar',          'pagos.anular',
            'modificaciones.ver',   'modificaciones.crear',   'modificaciones.aprobar',  'modificaciones.anular',
            'presupuesto.reportes',

            // ── Tesorería ──────────────────────────────────────────────
            'tesoreria.cuentas.ver',  'tesoreria.cuentas.crear', 'tesoreria.cuentas.editar',
            'tesoreria.ordenes.ver',  'tesoreria.ordenes.crear', 'tesoreria.ordenes.aprobar', 'tesoreria.ordenes.pagar',

            // ── Contabilidad ─────────────────────────────────────────────
            'contabilidad.cuentas.ver',   'contabilidad.cuentas.crear',   'contabilidad.cuentas.editar',
            'contabilidad.periodos.ver',  'contabilidad.periodos.crear',  'contabilidad.periodos.cerrar',
            'contabilidad.asientos.ver',  'contabilidad.asientos.crear',  'contabilidad.asientos.aprobar', 'contabilidad.asientos.anular',
            'contabilidad.reportes',

            // ── Compras y Almacén ────────────────────────────────────────
            'compras.almacenes.ver',    'compras.almacenes.crear',    'compras.almacenes.editar',
            'compras.articulos.ver',    'compras.articulos.crear',    'compras.articulos.editar',
            'compras.solicitudes.ver',  'compras.solicitudes.crear',  'compras.solicitudes.aprobar',
            'compras.ordenes.ver',      'compras.ordenes.crear',      'compras.ordenes.aprobar',
            'compras.recepciones.ver',  'compras.recepciones.crear',

            // ── Almacén — Solicitudes de Despacho ────────────────────────
            'almacen.solicitudes.ver',  'almacen.solicitudes.crear',  'almacen.solicitudes.aprobar',

            // ── Bienes Nacionales ────────────────────────────────────────
            'bienes.ver', 'bienes.crear', 'bienes.editar',

            // ── Nómina y Personal ────────────────────────────────────────
            'nomina.ver',             'nomina.crear',           'nomina.aprobar',  'nomina.pagar',  'nomina.anular',
            'nomina.empleados.ver',   'nomina.empleados.crear', 'nomina.empleados.editar',
            'nomina.conceptos.ver',   'nomina.conceptos.crear', 'nomina.conceptos.editar',

            // ── Retenciones Fiscales ─────────────────────────────────────
            'retenciones.ver',          'retenciones.crear',          'retenciones.editar',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // ── ROLES ──────────────────────────────────────────────────────

        // Super Admin — acceso total (gestionado por Gate::before en AppServiceProvider)
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

        // ── Administrador ─────────────────────────────────────────────
        $administrador = Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        $administrador->syncPermissions([
            'dashboard.ver',
            'usuarios.ver', 'usuarios.crear', 'usuarios.editar',
            'roles.ver', 'roles.gestionar',
            'unidades.ver', 'unidades.crear', 'unidades.editar',
            'beneficiarios.ver', 'beneficiarios.crear', 'beneficiarios.editar',
            'ejercicios.ver', 'ejercicios.crear', 'ejercicios.editar',
            'partidas.ver', 'partidas.crear', 'partidas.editar',
            'proyectos.ver', 'proyectos.crear', 'proyectos.editar',
            'creditos.ver', 'creditos.crear', 'creditos.editar',
            'compromisos.ver', 'compromisos.crear', 'compromisos.aprobar',
            'causaciones.ver', 'causaciones.crear', 'causaciones.aprobar',
            'pagos.ver', 'pagos.crear', 'pagos.procesar',
            'modificaciones.ver', 'modificaciones.crear', 'modificaciones.aprobar',
            'presupuesto.reportes',
            'tesoreria.cuentas.ver', 'tesoreria.ordenes.ver',
            'contabilidad.cuentas.ver', 'contabilidad.asientos.ver', 'contabilidad.reportes',
            'compras.almacenes.ver', 'compras.articulos.ver', 'compras.solicitudes.ver',
            'compras.ordenes.ver', 'compras.recepciones.ver',
            'almacen.solicitudes.ver', 'almacen.solicitudes.crear', 'almacen.solicitudes.aprobar',
            'bienes.ver',
            'retenciones.ver', 'retenciones.crear', 'retenciones.editar',
        ]);

        // ── Analista de Presupuesto ───────────────────────────────────
        $ppto = Role::firstOrCreate(['name' => 'analista-presupuesto', 'guard_name' => 'web']);
        $ppto->syncPermissions([
            'dashboard.ver',
            'unidades.ver', 'beneficiarios.ver',
            'ejercicios.ver', 'ejercicios.crear', 'ejercicios.editar',
            'partidas.ver', 'partidas.crear', 'partidas.editar',
            'proyectos.ver', 'proyectos.crear', 'proyectos.editar',
            'creditos.ver', 'creditos.crear', 'creditos.editar',
            'compromisos.ver', 'compromisos.crear', 'compromisos.aprobar', 'compromisos.anular',
            'causaciones.ver', 'causaciones.crear', 'causaciones.aprobar', 'causaciones.anular',
            'pagos.ver', 'pagos.crear', 'pagos.procesar', 'pagos.anular',
            'modificaciones.ver', 'modificaciones.crear', 'modificaciones.aprobar', 'modificaciones.anular',
            'presupuesto.reportes',
            'retenciones.ver',
        ]);

        // ── Tesorero ──────────────────────────────────────────────────
        $tesorero = Role::firstOrCreate(['name' => 'tesorero', 'guard_name' => 'web']);
        $tesorero->syncPermissions([
            'dashboard.ver',
            'unidades.ver', 'beneficiarios.ver',
            'ejercicios.ver', 'causaciones.ver', 'pagos.ver',
            'tesoreria.cuentas.ver', 'tesoreria.cuentas.crear', 'tesoreria.cuentas.editar',
            'tesoreria.ordenes.ver', 'tesoreria.ordenes.crear', 'tesoreria.ordenes.aprobar', 'tesoreria.ordenes.pagar',
        ]);

        // ── Analista Contable ─────────────────────────────────────────
        $contador = Role::firstOrCreate(['name' => 'analista-contable', 'guard_name' => 'web']);
        $contador->syncPermissions([
            'dashboard.ver',
            'unidades.ver', 'ejercicios.ver',
            'contabilidad.cuentas.ver', 'contabilidad.cuentas.crear', 'contabilidad.cuentas.editar',
            'contabilidad.periodos.ver', 'contabilidad.periodos.crear', 'contabilidad.periodos.cerrar',
            'contabilidad.asientos.ver', 'contabilidad.asientos.crear', 'contabilidad.asientos.aprobar', 'contabilidad.asientos.anular',
            'contabilidad.reportes',
        ]);

        // ── Jefe de Compras ───────────────────────────────────────────
        $compras = Role::firstOrCreate(['name' => 'jefe-compras', 'guard_name' => 'web']);
        $compras->syncPermissions([
            'dashboard.ver',
            'unidades.ver', 'beneficiarios.ver',
            'compras.almacenes.ver', 'compras.almacenes.crear', 'compras.almacenes.editar',
            'compras.articulos.ver', 'compras.articulos.crear', 'compras.articulos.editar',
            'compras.solicitudes.ver', 'compras.solicitudes.crear', 'compras.solicitudes.aprobar',
            'compras.ordenes.ver', 'compras.ordenes.crear', 'compras.ordenes.aprobar',
            'compras.recepciones.ver', 'compras.recepciones.crear',
            'almacen.solicitudes.ver', 'almacen.solicitudes.crear', 'almacen.solicitudes.aprobar',
        ]);

        // ── Jefe de Bienes Nacionales ─────────────────────────────────
        $bienes = Role::firstOrCreate(['name' => 'jefe-bienes', 'guard_name' => 'web']);
        $bienes->syncPermissions([
            'dashboard.ver', 'unidades.ver',
            'bienes.ver', 'bienes.crear', 'bienes.editar',
        ]);

        // ── Jefe de Nómina ────────────────────────────────────────────
        $nominaRole = Role::firstOrCreate(['name' => 'jefe-nomina', 'guard_name' => 'web']);
        $nominaRole->syncPermissions([
            'dashboard.ver', 'unidades.ver',
            'nomina.ver', 'nomina.crear', 'nomina.aprobar', 'nomina.pagar', 'nomina.anular',
            'nomina.empleados.ver', 'nomina.empleados.crear', 'nomina.empleados.editar',
            'nomina.conceptos.ver', 'nomina.conceptos.crear', 'nomina.conceptos.editar',
        ]);

        // ── Consultor / Solo lectura ──────────────────────────────────
        $consultor = Role::firstOrCreate(['name' => 'consultor', 'guard_name' => 'web']);
        $consultor->syncPermissions([
            'dashboard.ver',
            'unidades.ver', 'beneficiarios.ver',
            'ejercicios.ver', 'partidas.ver', 'proyectos.ver', 'creditos.ver',
            'compromisos.ver', 'causaciones.ver', 'pagos.ver', 'modificaciones.ver',
            'presupuesto.reportes',
            'tesoreria.cuentas.ver', 'tesoreria.ordenes.ver',
            'contabilidad.cuentas.ver', 'contabilidad.asientos.ver', 'contabilidad.reportes',
            'compras.almacenes.ver', 'compras.articulos.ver', 'compras.solicitudes.ver',
            'compras.ordenes.ver', 'compras.recepciones.ver',
            'almacen.solicitudes.ver',
            'bienes.ver',
            'nomina.ver', 'nomina.empleados.ver',
            'retenciones.ver',
        ]);

        // ── Usuario Super Admin por defecto ───────────────────────────
        // Buscar por email o por cédula para evitar constraint violations
        $admin = User::where('email', 'admin@sia.gov.ve')
                     ->orWhere('cedula', 'V-00000000')
                     ->first();

        if (!$admin) {
            $admin = User::create([
                'name'              => 'Administrador SIA',
                'email'             => 'admin@sia.gov.ve',
                'cedula'            => 'V-00000000',
                'password'          => bcrypt('Admin@SIA2024'),
                'activo'            => true,
                'email_verified_at' => now(),
            ]);
        } else {
            // Actualizar para asegurar datos correctos
            $admin->update([
                'name'              => 'Administrador SIA',
                'email'             => 'admin@sia.gov.ve',
                'activo'            => true,
                'email_verified_at' => $admin->email_verified_at ?? now(),
            ]);
        }
        $admin->syncRoles(['super-admin']);

        $this->command->info('✅ Roles, permisos y usuario admin creados/actualizados.');
        $this->command->info('   Roles: super-admin, administrador, analista-presupuesto,');
        $this->command->info('          tesorero, analista-contable, jefe-compras, jefe-bienes,');
        $this->command->info('          jefe-nomina, cajero, consultor');
        $this->command->info('📧 Email: admin@sia.gov.ve  |  🔑 Password: Admin@SIA2024');
    }
}
