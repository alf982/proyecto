<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convertir cualquier 'anulada' existente a 'rechazada'
        DB::statement("UPDATE solicitudes_despacho SET estado = 'rechazada', motivo_rechazo = CONCAT('[Anulada] ', COALESCE(motivo_rechazo,'')) WHERE estado = 'anulada'");

        // Eliminar 'anulada' del ENUM
        DB::statement("
            ALTER TABLE solicitudes_despacho
            MODIFY COLUMN estado ENUM('borrador','enviada','aprobada','entregada','rechazada')
            NOT NULL DEFAULT 'borrador'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE solicitudes_despacho
            MODIFY COLUMN estado ENUM('borrador','enviada','aprobada','entregada','rechazada','anulada')
            NOT NULL DEFAULT 'borrador'
        ");
    }
};
