<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Compromisos: credito_presupuestario_id ya no es requerido
        Schema::table('compromisos', function (Blueprint $table) {
            $table->foreignId('credito_presupuestario_id')
                  ->nullable()->change();
        });

        // Causaciones: también era requerido, hacerlo nullable
        Schema::table('causaciones', function (Blueprint $table) {
            $table->foreignId('credito_presupuestario_id')
                  ->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('compromisos', function (Blueprint $table) {
            $table->foreignId('credito_presupuestario_id')
                  ->nullable(false)->change();
        });

        Schema::table('causaciones', function (Blueprint $table) {
            $table->foreignId('credito_presupuestario_id')
                  ->nullable(false)->change();
        });
    }
};
