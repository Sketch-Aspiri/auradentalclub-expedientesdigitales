<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dos preguntas de la sección "Diagnóstico" del consentimiento informado de la clínica que aún
 * no vivían en la historia clínica: autopercepción de salud y fecha del último examen médico.
 * El consentimiento las toma de aquí (foto fija al crearse) — ver Sprint 5.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_histories', function (Blueprint $table) {
            $table->enum('general_health_rating', ['excelente', 'buena', 'regular', 'mala'])
                ->nullable()
                ->after('has_seizures');
            // Texto libre: "hace 6 meses", "enero 2026", "nunca"... no siempre es una fecha.
            $table->string('last_medical_exam')->nullable()->after('general_health_rating');
        });
    }

    public function down(): void
    {
        Schema::table('medical_histories', function (Blueprint $table) {
            $table->dropColumn(['general_health_rating', 'last_medical_exam']);
        });
    }
};
