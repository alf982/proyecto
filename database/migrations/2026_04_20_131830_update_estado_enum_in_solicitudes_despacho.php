<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Paso 1: Ampliar el ENUM para incluir AMBOS valores temporalmente
        DB::statement("
            ALTER TABLE solicitudes_despacho
            MODIFY COLUMN estado ENUM('borrador','enviada','aprobada','despachada','entregada','rechazada','anulada')
            NOT NULL DEFAULT 'borrador'
        ");

        // Paso 2: Migrar datos — renombrar 'despachada' → 'entregada'
        DB::statement("UPDATE solicitudes_despacho SET estado = 'entregada' WHERE estado = 'despachada'");

        // Paso 3: Eliminar el valor obsoleto 'despachada' del ENUM
        DB::statement("
            ALTER TABLE solicitudes_despacho
            MODIFY COLUMN estado ENUM('borrador','enviada','aprobada','entregada','rechazada','anulada')
            NOT NULL DEFAULT 'borrador'
        ");
    }

    public function down(): void
    {
        // Paso 1: Ampliar ENUM con ambos valores
        DB::statement("
            ALTER TABLE solicitudes_despacho
            MODIFY COLUMN estado ENUM('borrador','enviada','aprobada','despachada','entregada','rechazada','anulada')
            NOT NULL DEFAULT 'borrador'
        ");

        // Paso 2: Revertir datos
        DB::statement("UPDATE solicitudes_despacho SET estado = 'despachada' WHERE estado = 'entregada'");

        // Paso 3: Restaurar ENUM original
        DB::statement("
            ALTER TABLE solicitudes_despacho
            MODIFY COLUMN estado ENUM('borrador','enviada','aprobada','despachada','rechazada','anulada')
            NOT NULL DEFAULT 'borrador'
        ");
    }
};
