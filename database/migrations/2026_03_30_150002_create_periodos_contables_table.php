<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos_contables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ejercicio_fiscal_id')->constrained('ejercicios_fiscales')->cascadeOnDelete();
            $table->smallInteger('anio');
            $table->tinyInteger('mes'); // 1-12
            $table->string('nombre', 40); // "Enero 2026"
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->enum('estado', ['abierto','cerrado'])->default('abierto');
            $table->timestamp('fecha_cierre')->nullable();
            $table->foreignId('cerrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['ejercicio_fiscal_id','anio','mes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos_contables');
    }
};
