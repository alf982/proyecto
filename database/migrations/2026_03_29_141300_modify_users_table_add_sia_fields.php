<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('cedula', 20)->nullable()->unique()->after('name');
            $table->string('telefono', 20)->nullable()->after('cedula');
            $table->unsignedBigInteger('unidad_ejecutora_id')->nullable()->after('telefono');
            $table->boolean('activo')->default(true)->after('unidad_ejecutora_id');
            $table->timestamp('ultimo_acceso')->nullable()->after('activo');

            $table->foreign('unidad_ejecutora_id')
                  ->references('id')
                  ->on('unidades_ejecutoras')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unidad_ejecutora_id']);
            $table->dropColumn(['cedula', 'telefono', 'unidad_ejecutora_id', 'activo', 'ultimo_acceso']);
        });
    }
};
