<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partidas_presupuestarias', function (Blueprint $table) {
            // Cuenta bancaria donde ingresan los fondos de esta partida
            $table->foreignId('cuenta_bancaria_id')
                  ->nullable()
                  ->after('tipo')
                  ->constrained('cuentas_bancarias')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('partidas_presupuestarias', function (Blueprint $table) {
            $table->dropForeign(['cuenta_bancaria_id']);
            $table->dropColumn('cuenta_bancaria_id');
        });
    }
};
