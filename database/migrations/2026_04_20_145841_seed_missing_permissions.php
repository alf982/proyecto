<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Permisos que se usan en código pero no existían en la BD */
    private array $nuevos = [
        // Módulo Almacén — Solicitudes de despacho/entrega
        'almacen.solicitudes.ver',
        'almacen.solicitudes.crear',
        'almacen.solicitudes.aprobar',

        // Nómina — sub-permisos usados en sidebar/dashboard
        'nomina.nominas.ver',
        'nomina.nominas.crear',
        'nomina.nominas.aprobar',
        'nomina.nominas.pagar',
        'nomina.cargos.ver',
        'nomina.cargos.crear',

        // Bienes — sub-permisos usados en sidebar y vistas
        'bienes.categorias.ver',
        'bienes.categorias.crear',
        'bienes.baja',
        'bienes.trasladar',

        // Ingresos — permisos referenciados en PdfController y sidebar
        'ingresos.ver',
        'ingresos.arqueos.ver',
        'ingresos.arqueos.aprobar',
        'ingresos.caja.ver',
        'ingresos.caja.crear',
        'ingresos.caja.editar',
        'ingresos.conceptos.ver',
        'ingresos.conceptos.crear',

        // Créditos — eliminar existe en etiquetas pero no en BD
        'creditos.eliminar',

        // Modificaciones — existen en BD pero les falta descripción
        // (ya existen, no necesitan crearse aquí)
        // Retenciones — ya existen en BD
    ];

    public function up(): void
    {
        $existentes = DB::table('permissions')->pluck('name')->toArray();
        $now = now();

        foreach ($this->nuevos as $name) {
            if (!in_array($name, $existentes)) {
                DB::table('permissions')->insert([
                    'name'       => $name,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Asignar a super-admin todos los permisos nuevos (vía wildcard de Spatie ya lo cubre,
        // pero también asignamos explícitamente a los roles estándar)
        $roles = [
            'jefe-compras' => ['almacen.solicitudes.ver', 'almacen.solicitudes.crear', 'almacen.solicitudes.aprobar'],
            'jefe-nomina'  => ['nomina.nominas.ver', 'nomina.nominas.crear', 'nomina.nominas.aprobar', 'nomina.nominas.pagar', 'nomina.cargos.ver', 'nomina.cargos.crear'],
            'jefe-bienes'  => ['bienes.categorias.ver', 'bienes.categorias.crear', 'bienes.baja', 'bienes.trasladar'],
            'cajero'       => ['ingresos.ver', 'ingresos.caja.ver', 'ingresos.caja.crear', 'ingresos.caja.editar', 'ingresos.conceptos.ver', 'ingresos.arqueos.ver'],
        ];

        foreach ($roles as $roleName => $permisos) {
            $rol = DB::table('roles')->where('name', $roleName)->first();
            if (!$rol) continue;

            foreach ($permisos as $perm) {
                $permId = DB::table('permissions')->where('name', $perm)->value('id');
                if (!$permId) continue;

                $existe = DB::table('role_has_permissions')
                    ->where('role_id', $rol->id)
                    ->where('permission_id', $permId)
                    ->exists();

                if (!$existe) {
                    DB::table('role_has_permissions')->insert([
                        'role_id'       => $rol->id,
                        'permission_id' => $permId,
                    ]);
                }
            }
        }

        // Asignar todos los nuevos permisos al rol administrador
        $admin = DB::table('roles')->where('name', 'administrador')->first();
        if ($admin) {
            $todosNuevos = DB::table('permissions')->whereIn('name', $this->nuevos)->get();
            foreach ($todosNuevos as $perm) {
                $existe = DB::table('role_has_permissions')
                    ->where('role_id', $admin->id)
                    ->where('permission_id', $perm->id)
                    ->exists();
                if (!$existe) {
                    DB::table('role_has_permissions')->insert([
                        'role_id'       => $admin->id,
                        'permission_id' => $perm->id,
                    ]);
                }
            }
        }

        // Limpiar cache de permisos de Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        DB::table('permissions')->whereIn('name', $this->nuevos)->delete();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
