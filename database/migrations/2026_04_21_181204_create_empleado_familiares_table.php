<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleado_familiares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->string('nombre_completo', 150);
            $table->enum('parentesco', ['hijo','conyuge','padre','madre','hermano','otro']);
            $table->string('cedula', 20)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('telefono', 30)->nullable();
            // Carga familiar = aplica para cálculo de beneficios en nómina
            $table->boolean('es_carga_familiar')->default(false);
            $table->string('observaciones', 300)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleado_familiares');
    }
};
