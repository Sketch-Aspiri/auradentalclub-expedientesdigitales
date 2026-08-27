<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Odontograma con historial: cada fila es un hallazgo registrado en un momento dado
     * sobre un diente (numeración FDI) o una superficie concreta de ese diente. El estado
     * "vigente" de una superficie es la fila más reciente (recorded_at, luego id) no
     * archivada. No se actualiza en sitio — corregir es registrar de nuevo, y un error de
     * captura se archiva con soft delete. Ver CLAUDE.md §4.4 y §6 (esquema ampliado en
     * sesión: por superficie + historial, confirmado con el cliente el 2026-08-27).
     */
    public function up(): void
    {
        Schema::create('odontogram_records', function (Blueprint $table) {
            $table->id();
            // RESTRICT, no CASCADE: el purgado de un expediente clínico (NOM-004) debe pasar
            // siempre por Patient::forceDeleting() para dejar rastro en audit_logs — mismo
            // patrón que consultations / medical_histories (migración del 2026_08_27_120000).
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('recorded_by')->constrained('users');

            // Numeración FDI de dentición permanente: 11-18, 21-28, 31-38, 41-48.
            $table->unsignedTinyInteger('tooth_number');

            // null = hallazgo sobre el diente completo (corona, extracción, implante...).
            $table->enum('surface', ['mesial', 'distal', 'oclusal', 'vestibular', 'lingual'])->nullable();

            $table->enum('status', [
                'sano', 'caries', 'obturado', 'sellador', 'fractura',
                'corona', 'endodoncia', 'protesis_fija', 'implante',
                'movilidad', 'extraido', 'ausente',
            ]);

            // Nota clínica de texto libre: cifrada en reposo (CLAUDE.md §5).
            $table->text('note')->nullable();

            // Fecha clínica del hallazgo (puede no ser hoy: carga de expediente histórico).
            $table->date('recorded_at');

            $table->softDeletes();
            $table->timestamps();

            $table->index(['patient_id', 'tooth_number']);
            $table->index(['patient_id', 'tooth_number', 'surface']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odontogram_records');
    }
};
