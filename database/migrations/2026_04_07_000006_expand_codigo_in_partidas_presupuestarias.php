<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partidas_presupuestarias', function (Blueprint $table) {
            // El formato 00-00-00-000-00-00-00 tiene 21 chars; usamos 30 con margen
            $table->string('codigo', 30)->change();
            $table->string('generica', 10)->nullable()->change();
            $table->string('especifica', 15)->nullable()->change();
            $table->string('subespecifica', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('partidas_presupuestarias', function (Blueprint $table) {
            $table->string('codigo', 20)->change();
            $table->string('generica', 10)->nullable()->change();
            $table->string('especifica', 20)->nullable()->change();
            $table->string('subespecifica', 30)->nullable()->change();
        });
    }
};
