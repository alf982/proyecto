<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('beneficiarios', function (Blueprint $table) {
            $table->id();
            $table->string('rif', 15)->unique();
            $table->string('razon_social', 200);
            $table->string('nombre_comercial', 200)->nullable();
            $table->enum('tipo', ['proveedor', 'contratista', 'funcionario', 'otro'])->default('proveedor');
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('direccion', 300)->nullable();
            // Datos bancarios
            $table->string('banco_nombre', 100)->nullable();
            $table->string('banco_cuenta', 30)->nullable();
            $table->enum('banco_tipo_cuenta', ['corriente', 'ahorro', 'otro'])->nullable();
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void { Schema::dropIfExists('beneficiarios'); }
};
