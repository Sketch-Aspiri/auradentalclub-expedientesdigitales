<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users');
            $table->date('consultation_date');

            // Signos vitales
            $table->string('blood_pressure', 20)->nullable();
            $table->unsignedSmallInteger('heart_rate')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();

            // Exploración bucal
            $table->text('soft_tissues_notes')->nullable();
            $table->text('gums_periodontium_notes')->nullable();
            $table->enum('oral_hygiene_level', ['buena', 'regular', 'mala'])->nullable();

            // Motivo, diagnóstico y plan (notas clínicas: cifradas en reposo, CLAUDE.md §5)
            $table->text('chief_complaint')->nullable();
            $table->text('clinical_diagnosis')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('prognosis')->nullable();
            $table->text('risks_and_complications')->nullable();
            $table->text('treatment_alternatives')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['patient_id', 'consultation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
