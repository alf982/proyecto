<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleado_historial_cargos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            // Cargo del sistema (si aplica)
            $table->foreignId('cargo_id')->nullable()->constrained('cargos')->nullOnDelete();
            // Cargo como texto libre (para instituciones externas)
            $table->string('cargo_texto', 200)->nullable();
            // Institución (null = esta misma institución)
            $table->string('institucion', 200)->nullable()
                  ->comment('null = institución actual; texto libre para empleos anteriores externos');
            $table->foreignId('unidad_ejecutora_id')->nullable()
                  ->constrained('unidades_ejecutoras')->nullOnDelete();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable()->comment('null = cargo actual');
            $table->string('motivo_cambio', 300)->nullable();
            $table->foreignId('registrado_por')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleado_historial_cargos');
    }
};
