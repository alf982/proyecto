<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creditos_presupuestarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ejercicio_fiscal_id')->constrained('ejercicios_fiscales')->cascadeOnDelete();
            $table->foreignId('partida_presupuestaria_id')->constrained('partidas_presupuestarias')->cascadeOnDelete();
            $table->foreignId('unidad_ejecutora_id')->constrained('unidades_ejecutoras')->cascadeOnDelete();
            $table->foreignId('fuente_financiamiento_id')->nullable()->constrained('fuentes_financiamiento')->nullOnDelete();
            $table->foreignId('proyecto_sia_id')->nullable()->constrained('proyectos_sia')->nullOnDelete();
            $table->foreignId('actividad_id')->nullable()->constrained('actividades')->nullOnDelete();

            // Montos en bolívares
            $table->decimal('monto_aprobado', 18, 2)->default(0);
            $table->decimal('monto_modificado', 18, 2)->default(0); // después de modificaciones
            $table->decimal('monto_comprometido', 18, 2)->default(0);
            $table->decimal('monto_causado', 18, 2)->default(0);
            $table->decimal('monto_pagado', 18, 2)->default(0);

            $table->text('observaciones')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['ejercicio_fiscal_id', 'partida_presupuestaria_id', 'unidad_ejecutora_id', 'fuente_financiamiento_id'],
                'credito_unico'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creditos_presupuestarios');
    }
};
