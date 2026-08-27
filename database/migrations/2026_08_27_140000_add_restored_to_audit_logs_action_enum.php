<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Añade `restored` al enum `audit_logs.action` para poder auditar la restauración de un
 * registro clínico soft-deleted (pacientes, consultas). El trait Auditable engancha el
 * evento `restored` de Eloquent automáticamente en los modelos con SoftDeletes.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE audit_logs MODIFY COLUMN action ENUM('viewed', 'created', 'updated', 'deleted', 'restored') NOT NULL");
    }

    public function down(): void
    {
        // Revertir con filas `restored` existentes las corrompería (MySQL las truncaría a '').
        if (DB::table('audit_logs')->where('action', 'restored')->exists()) {
            throw new RuntimeException(
                'No se puede revertir: hay eventos `restored` en audit_logs. '
                .'Revertirlo destruiría rastro de auditoría clínica.'
            );
        }

        DB::statement("ALTER TABLE audit_logs MODIFY COLUMN action ENUM('viewed', 'created', 'updated', 'deleted') NOT NULL");
    }
};
