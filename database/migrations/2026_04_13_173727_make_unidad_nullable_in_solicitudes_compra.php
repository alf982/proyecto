<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_compra', function (Blueprint $table) {
            // El módulo de Solicitudes de Compra ahora es de uso interno del almacén,
            // ya no requiere una unidad ejecutora solicitante.
            $table->foreignId('unidad_ejecutora_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_compra', function (Blueprint $table) {
            $table->foreignId('unidad_ejecutora_id')->nullable(false)->change();
        });
    }
};
