<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ejercicios_fiscales', function (Blueprint $table) {
            $table->id();
            $table->year('anio')->unique();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->enum('estado', ['borrador', 'activo', 'cerrado'])->default('borrador');
            $table->text('observaciones')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cerrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_cierre')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ejercicios_fiscales');
    }
};
