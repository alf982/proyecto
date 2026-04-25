<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cuentas_bancarias', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 150);            // denominación interna
            $table->string('banco', 100);
            $table->string('numero_cuenta', 30)->unique();
            $table->enum('tipo', ['corriente', 'ahorro', 'fondo', 'otro'])->default('corriente');
            $table->string('moneda', 10)->default('VES');
            $table->decimal('saldo_inicial', 18, 2)->default(0);
            $table->decimal('saldo_actual', 18, 2)->default(0);
            $table->date('fecha_apertura')->nullable();
            $table->enum('estado', ['activa', 'inactiva', 'bloqueada'])->default('activa');
            $table->string('firmante_1', 150)->nullable();
            $table->string('firmante_2', 150)->nullable();
            $table->foreignId('ejercicio_fiscal_id')->nullable()->constrained('ejercicios_fiscales')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void { Schema::dropIfExists('cuentas_bancarias'); }
};
