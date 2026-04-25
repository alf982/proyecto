<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyectos_sia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ejercicio_fiscal_id')->constrained('ejercicios_fiscales')->cascadeOnDelete();
            $table->foreignId('unidad_ejecutora_id')->constrained('unidades_ejecutoras')->cascadeOnDelete();
            $table->string('codigo', 30);
            $table->string('nombre', 300);
            $table->text('descripcion')->nullable();
            $table->text('objetivo')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->enum('estado', ['formulacion', 'activo', 'suspendido', 'terminado'])->default('formulacion');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['ejercicio_fiscal_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyectos_sia');
    }
};
