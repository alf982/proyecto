<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retenciones_aplicadas', function (Blueprint $table) {
            $table->id();

            // Relación polimórfica: permite vincular a Causacion, Pago, OrdenCompra, Nomina, etc.
            $table->morphs('retencionable'); // genera retencionable_type + retencionable_id + índice

            $table->foreignId('retencion_id')
                ->constrained('retenciones')
                ->restrictOnDelete();

            $table->decimal('monto_base', 14, 2)->comment('Monto sobre el que se calculó la retención');
            $table->decimal('monto_retenido', 14, 2)->comment('Monto final descontado');
            $table->decimal('porcentaje_aplicado', 7, 4)->nullable()
                ->comment('Porcentaje vigente al momento de aplicar');

            $table->timestamps();

            // Evitar duplicados: misma retención dos veces sobre el mismo registro
            $table->unique(['retencionable_type', 'retencionable_id', 'retencion_id'], 'unique_retencion_por_registro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retenciones_aplicadas');
    }
};
