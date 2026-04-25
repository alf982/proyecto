<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            // ── Datos civiles ──────────────────────────────────────
            $table->enum('estado_civil', ['soltero','casado','divorciado','viudo','concubinato'])
                  ->nullable()->after('email');
            $table->string('nacionalidad', 30)->nullable()->after('estado_civil');
            $table->date('fecha_nacimiento')->nullable()->after('nacionalidad');
            $table->string('lugar_nacimiento', 150)->nullable()->after('fecha_nacimiento');

            // ── Grado académico ───────────────────────────────────
            $table->enum('nivel_instruccion', [
                'sin_instruccion','primaria','secundaria','tsu','universitario','postgrado','doctorado'
            ])->nullable()->after('lugar_nacimiento');
            $table->string('titulo', 200)->nullable()->after('nivel_instruccion');
            $table->string('institucion_educativa', 200)->nullable()->after('titulo');

            // ── Dirección ─────────────────────────────────────────
            $table->string('estado_residencia', 80)->nullable()->after('institucion_educativa');
            $table->string('municipio', 80)->nullable()->after('estado_residencia');
            $table->string('parroquia', 80)->nullable()->after('municipio');
            $table->text('direccion_completa')->nullable()->after('parroquia');

            // ── Curriculum ────────────────────────────────────────
            $table->string('curriculum_path', 500)->nullable()->after('direccion_completa');
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn([
                'estado_civil','nacionalidad','fecha_nacimiento','lugar_nacimiento',
                'nivel_instruccion','titulo','institucion_educativa',
                'estado_residencia','municipio','parroquia','direccion_completa',
                'curriculum_path',
            ]);
        });
    }
};
