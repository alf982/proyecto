<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Campos de Salud en la tabla empleados ──────────────
        Schema::table('empleados', function (Blueprint $table) {
            $table->enum('tipo_sangre', ['A+','A-','B+','B-','AB+','AB-','O+','O-'])
                  ->nullable()->after('curriculum_path');
            $table->boolean('tiene_discapacidad')->default(false)->after('tipo_sangre');
            $table->string('tipo_discapacidad', 200)->nullable()->after('tiene_discapacidad');
            $table->string('condicion_medica', 300)->nullable()->after('tipo_discapacidad');
            // Contacto de emergencia (aprovechamos la migración)
            $table->string('contacto_emergencia_nombre', 150)->nullable()->after('condicion_medica');
            $table->string('contacto_emergencia_parentesco', 80)->nullable()->after('contacto_emergencia_nombre');
            $table->string('contacto_emergencia_telefono', 30)->nullable()->after('contacto_emergencia_parentesco');
        });

        // ── 2. Tabla de Formación Adicional ────────────────────────
        Schema::create('empleado_formaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();

            $table->enum('tipo', [
                'curso',
                'certificacion',
                'diplomado',
                'postgrado',
                'maestria',
                'doctorado',
                'idioma',
                'otro',
            ]);

            $table->string('nombre', 200);              // Nombre del curso / certificación
            $table->string('institucion', 200)->nullable(); // Institución que lo otorgó
            $table->string('pais', 80)->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('en_curso')->default(false);
            $table->integer('duracion_horas')->nullable();  // Horas académicas

            // Para idiomas
            $table->enum('nivel_idioma', ['basico','intermedio','avanzado','nativo'])->nullable();

            $table->string('numero_registro', 100)->nullable();  // Nro. de constancia/certificado
            $table->text('descripcion')->nullable();
            $table->string('documento_path', 500)->nullable();    // Archivo adjunto (PDF)

            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleado_formaciones');

        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_sangre',
                'tiene_discapacidad',
                'tipo_discapacidad',
                'condicion_medica',
                'contacto_emergencia_nombre',
                'contacto_emergencia_parentesco',
                'contacto_emergencia_telefono',
            ]);
        });
    }
};
