<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partidas_presupuestarias', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();          // ej: 4.02.01.00
            $table->string('generica', 10)->nullable();      // primer nivel: 4
            $table->string('especifica', 10)->nullable();    // segundo nivel: 4.02
            $table->string('subespecifica', 10)->nullable(); // tercer nivel: 4.02.01
            $table->string('descripcion', 300);
            $table->enum('tipo', ['ingreso', 'gasto'])->default('gasto');
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('generica');
            $table->index('especifica');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partidas_presupuestarias');
    }
};
