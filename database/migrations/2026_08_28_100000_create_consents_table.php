<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consentimiento informado (CLAUDE.md §4.5, §6): formulario genérico con `type` (general /
 * extracción, ampliable), datos clínicos del acto autorizado, y firmas digitales de paciente,
 * médico y hasta dos testigos.
 *
 * Ciclo de vida (confirmado con el cliente el 2026-08-28):
 *  - borrador: signed_at NULL — editable y eliminable.
 *  - firmado: signed_at NOT NULL — inmutable; solo se puede anular o imprimir.
 *  - anulado: voided_at NOT NULL — inmutable; se corrige creando un consentimiento nuevo.
 *
 * Los campos de texto clínico se cifran en reposo (CLAUDE.md §5). Las firmas se guardan como
 * ruta a un archivo en el disco privado `local` (App\Support\SignatureImage), nunca en `public/`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consents', function (Blueprint $table) {
            $table->id();
            // RESTRICT, no CASCADE: el purgado de un expediente clínico (NOM-004) debe pasar
            // siempre por Patient::forceDeleting() para dejar rastro en audit_logs — mismo
            // patrón que consultations / medical_histories / odontogram_records.
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained('users');

            $table->enum('type', ['general', 'extraction']);
            $table->enum('given_by', ['paciente', 'representante_legal', 'familiar']);
            $table->string('relationship')->nullable();

            // Texto clínico libre: cifrado en reposo (CLAUDE.md §5).
            $table->text('diagnosis');
            $table->text('proposed_treatment');
            $table->text('specific_risks');

            $table->boolean('authorizes_photos_xrays')->default(false);
            $table->boolean('patient_accepts')->default(false);

            // Rutas a las firmas en el disco privado `local` (nunca URL directa).
            $table->string('patient_signature_path')->nullable();
            $table->string('doctor_signature_path')->nullable();
            $table->string('witness1_name')->nullable();
            $table->string('witness1_signature_path')->nullable();
            $table->string('witness2_name')->nullable();
            $table->string('witness2_signature_path')->nullable();

            $table->timestamp('signed_at')->nullable();

            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('void_reason')->nullable(); // cifrado en reposo (puede citar contexto clínico).

            $table->softDeletes();
            $table->timestamps();

            $table->index(['patient_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consents');
    }
};
