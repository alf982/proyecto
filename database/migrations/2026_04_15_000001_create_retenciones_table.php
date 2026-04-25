<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retenciones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 150);
            $table->enum('tipo', ['porcentaje', 'monto_fijo'])->default('porcentaje');
            $table->decimal('porcentaje', 7, 4)->nullable()->comment('Usado cuando tipo=porcentaje');
            $table->decimal('monto_fijo', 14, 2)->nullable()->comment('Usado cuando tipo=monto_fijo');
            $table->json('aplica_a')->nullable()
                ->comment('Módulos donde aplica esta retención');
            $table->enum('base_calculo', ['monto_bruto', 'monto_neto'])->default('monto_bruto');
            $table->boolean('obligatoria')->default(false)
                ->comment('Si true, se preselecciona automáticamente en los formularios');
            $table->boolean('activo')->default(true);
            $table->text('descripcion')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Registrar permisos para el módulo de retenciones
        $guardName = 'web';
        $permisos = [
            'retenciones.ver',
            'retenciones.crear',
            'retenciones.editar',
        ];
        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => $guardName]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('retenciones');

        // Eliminar los permisos al revertir
        Permission::whereIn('name', ['retenciones.ver', 'retenciones.crear', 'retenciones.editar'])->delete();
    }
};
