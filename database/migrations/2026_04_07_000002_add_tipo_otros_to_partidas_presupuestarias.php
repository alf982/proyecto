<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // En MySQL, para cambiar un ENUM hay que usar una sentencia raw
        DB::statement("ALTER TABLE partidas_presupuestarias MODIFY COLUMN tipo ENUM('ingreso','gasto','otros') NOT NULL DEFAULT 'gasto'");

        Schema::table('partidas_presupuestarias', function (Blueprint $table) {
            // Código libre cuando el tipo es "otros"
            $table->string('codigo_personalizado', 50)
                  ->nullable()
                  ->after('tipo')
                  ->comment('Código manual cuando tipo = otros');
        });
    }

    public function down(): void
    {
        Schema::table('partidas_presupuestarias', function (Blueprint $table) {
            $table->dropColumn('codigo_personalizado');
        });

        // Revertir el ENUM (primero garantizar que no haya filas con 'otros')
        DB::statement("ALTER TABLE partidas_presupuestarias MODIFY COLUMN tipo ENUM('ingreso','gasto') NOT NULL DEFAULT 'gasto'");
    }
};
