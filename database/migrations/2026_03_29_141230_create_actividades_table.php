<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_sia_id')->constrained('proyectos_sia')->cascadeOnDelete();
            $table->string('codigo', 30);
            $table->string('nombre', 300);
            $table->text('descripcion')->nullable();
            $table->text('meta')->nullable();
            $table->string('unidad_medida', 100)->nullable();
            $table->decimal('cantidad_meta', 15, 2)->nullable();
            $table->enum('estado', ['formulacion', 'activo', 'suspendida', 'terminada'])->default('formulacion');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['proyecto_sia_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};
