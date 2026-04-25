<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cargos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 150);
            $table->enum('nivel', ['directivo', 'profesional', 'tecnico', 'administrativo', 'obrero'])->default('administrativo');
            $table->decimal('salario_base', 18, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('cedula', 15)->unique();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->foreignId('cargo_id')->constrained('cargos')->restrictOnDelete();
            $table->foreignId('unidad_ejecutora_id')->constrained('unidades_ejecutoras')->restrictOnDelete();
            $table->date('fecha_ingreso');
            $table->enum('tipo', ['fijo', 'contratado', 'obrero'])->default('fijo');
            $table->string('banco', 80)->nullable();
            $table->string('numero_cuenta', 30)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'jubilado', 'retirado'])->default('activo');
            $table->date('fecha_egreso')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('conceptos_nomina', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 150);
            $table->enum('tipo', ['asignacion', 'deduccion'])->default('asignacion');
            $table->enum('calculo', ['fijo', 'porcentaje'])->default('fijo');
            $table->decimal('valor', 12, 4)->default(0);
            $table->enum('aplica_a', ['todos', 'fijos', 'contratados', 'obreros'])->default('todos');
            $table->boolean('es_obligatorio')->default(false);
            $table->boolean('activo')->default(true);
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('nominas', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 25)->unique();
            $table->foreignId('ejercicio_fiscal_id')->constrained('ejercicios_fiscales')->restrictOnDelete();
            $table->enum('tipo_nomina', ['ordinaria', 'vacacional', 'utilidades', 'bono', 'liquidacion'])->default('ordinaria');
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->decimal('total_asignaciones', 18, 2)->default(0);
            $table->decimal('total_deducciones', 18, 2)->default(0);
            $table->decimal('total_neto', 18, 2)->default(0);
            $table->enum('estado', ['borrador', 'calculada', 'aprobada', 'pagada', 'anulada'])->default('borrador');
            $table->text('observaciones')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('nominas_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nomina_id')->constrained('nominas')->cascadeOnDelete();
            $table->foreignId('empleado_id')->constrained('empleados')->restrictOnDelete();
            $table->decimal('salario_base', 18, 2);
            $table->decimal('total_asignaciones', 18, 2)->default(0);
            $table->decimal('total_deducciones', 18, 2)->default(0);
            $table->decimal('neto', 18, 2)->default(0);
            $table->json('conceptos_aplicados')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('nominas_detalle');
        Schema::dropIfExists('nominas');
        Schema::dropIfExists('conceptos_nomina');
        Schema::dropIfExists('empleados');
        Schema::dropIfExists('cargos');
    }
};
