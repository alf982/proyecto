<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categorias_bien', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 150);
            $table->unsignedTinyInteger('vida_util_anios')->default(5);
            $table->decimal('tasa_depreciacion', 8, 4)->default(20.0000);
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('bienes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_inventario', 30)->unique();
            $table->foreignId('categoria_bien_id')->constrained('categorias_bien')->restrictOnDelete();
            $table->foreignId('unidad_ejecutora_id')->constrained('unidades_ejecutoras')->restrictOnDelete();
            $table->string('descripcion', 300);
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('serial', 100)->nullable();
            $table->smallInteger('anio_adquisicion')->nullable();
            $table->decimal('valor_adquisicion', 18, 2)->default(0);
            $table->decimal('valor_actual', 18, 2)->default(0);
            $table->string('ubicacion', 200)->nullable();
            $table->string('responsable', 150)->nullable();
            $table->enum('estado', ['activo', 'en_reparacion', 'dado_de_baja', 'extraviado'])->default('activo');
            $table->date('fecha_incorporacion');
            $table->date('fecha_baja')->nullable();
            $table->string('motivo_baja')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('movimientos_bien', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')->constrained('bienes')->cascadeOnDelete();
            $table->enum('tipo', ['incorporacion', 'traslado', 'reasignacion', 'baja', 'reparacion', 'devolucion']);
            $table->foreignId('unidad_origen_id')->nullable()->constrained('unidades_ejecutoras')->nullOnDelete();
            $table->foreignId('unidad_destino_id')->nullable()->constrained('unidades_ejecutoras')->nullOnDelete();
            $table->string('motivo', 400);
            $table->date('fecha');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('movimientos_bien');
        Schema::dropIfExists('bienes');
        Schema::dropIfExists('categorias_bien');
    }
};
