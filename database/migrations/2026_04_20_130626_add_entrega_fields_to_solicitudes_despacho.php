<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_despacho', function (Blueprint $table) {
            // Campos para registrar la entrega real (paso 3 del flujo)
            $table->string('recibido_por')->nullable()->after('motivo_rechazo')
                  ->comment('Nombre de la persona que recibió los artículos en la oficina');
            $table->dateTime('fecha_entrega')->nullable()->after('recibido_por')
                  ->comment('Fecha y hora en que se realizó la entrega física');
            $table->text('observaciones_entrega')->nullable()->after('fecha_entrega')
                  ->comment('Notas del almacenista al momento de la entrega');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_despacho', function (Blueprint $table) {
            $table->dropColumn(['recibido_por', 'fecha_entrega', 'observaciones_entrega']);
        });
    }
};
