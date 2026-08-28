<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Añade `signed` y `voided` al enum `audit_logs.action` para auditar la firma y la anulación
 * de un consentimiento informado (Sprint 5) como eventos distintos de un `updated` genérico.
 * Mismo patrón que 2026_08_27_140000_add_restored_to_audit_logs_action_enum.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE audit_logs MODIFY COLUMN action ENUM('viewed', 'created', 'updated', 'deleted', 'restored', 'signed', 'voided') NOT NULL");
    }

    public function down(): void
    {
        // Revertir con filas `signed` / `voided` existentes las corrompería (MySQL las truncaría a '').
        if (DB::table('audit_logs')->whereIn('action', ['signed', 'voided'])->exists()) {
            throw new RuntimeException(
                'No se puede revertir: hay eventos `signed` / `voided` en audit_logs. '
                .'Revertirlo destruiría rastro de auditoría clínica.'
            );
        }

        DB::statement("ALTER TABLE audit_logs MODIFY COLUMN action ENUM('viewed', 'created', 'updated', 'deleted', 'restored') NOT NULL");
    }
};
