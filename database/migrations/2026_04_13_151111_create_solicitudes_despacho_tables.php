<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Solicitudes de Despacho (cabecera) ───────────────────────
        Schema::create('solicitudes_despacho', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->foreignId('unidad_ejecutora_id')->constrained('unidades_ejecutoras');
            $table->string('motivo', 500);
            $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])->default('media');
            $table->date('fecha_requerida')->nullable();
            $table->enum('estado', ['borrador', 'enviada', 'aprobada', 'despachada', 'rechazada', 'anulada'])
                  ->default('enviada');
            $table->text('observaciones')->nullable();
            $table->text('motivo_rechazo')->nullable();
            $table->foreignId('solicitado_por')->constrained('users');
            $table->foreignId('aprobado_por')->nullable()->constrained('users');
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->timestamp('fecha_despacho')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ── Detalle de cada solicitud ─────────────────────────────────
        Schema::create('solicitudes_despacho_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_despacho_id')->constrained('solicitudes_despacho')->cascadeOnDelete();
            $table->foreignId('articulo_id')->constrained('articulos');
            $table->decimal('cantidad_solicitada', 12, 2);
            $table->decimal('cantidad_despachada', 12, 2)->nullable();
            $table->string('observacion', 300)->nullable();
            $table->unsignedSmallInteger('orden')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_despacho_detalle');
        Schema::dropIfExists('solicitudes_despacho');
    }
};
