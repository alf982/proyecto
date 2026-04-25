<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partidas_presupuestarias', function (Blueprint $table) {
            // Hacer tipo nullable (puede haber data existente)
            DB::statement("ALTER TABLE partidas_presupuestarias MODIFY tipo ENUM('gasto','ingreso','otros') NULL DEFAULT NULL");
            // Eliminar campo personalizado (fue añadido recientemente, sin data importante)
            $table->dropColumn('codigo_personalizado');
        });
    }

    public function down(): void
    {
        Schema::table('partidas_presupuestarias', function (Blueprint $table) {
            DB::statement("ALTER TABLE partidas_presupuestarias MODIFY tipo ENUM('gasto','ingreso','otros') NOT NULL DEFAULT 'ingreso'");
            $table->string('codigo_personalizado', 50)->nullable()->after('tipo');
        });
    }
};
