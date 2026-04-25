<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class, // 1. Roles y permisos primero
            DatosBaseSeeder::class,           // 2. Catálogos base (fuentes, cargos, conceptos...)
            PruebaIntegralSeeder::class,      // 3. Datos de ejemplo del flujo presupuestario
        ]);
    }
}
