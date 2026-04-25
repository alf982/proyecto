<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asientos_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asiento_contable_id')->constrained('asientos_contables')->cascadeOnDelete();
            $table->foreignId('cuenta_contable_id')->constrained('cuentas_contables')->restrictOnDelete();
            $table->string('descripcion', 300)->nullable();
            $table->decimal('debe', 18, 2)->default(0);
            $table->decimal('haber', 18, 2)->default(0);
            $table->unsignedSmallInteger('orden')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asientos_detalle');
    }
};
