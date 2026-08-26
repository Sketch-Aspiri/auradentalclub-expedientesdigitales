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
        Schema::create('medical_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->unique()->constrained()->cascadeOnDelete();

            // Antecedentes patológicos
            $table->boolean('has_diabetes')->default(false);
            $table->boolean('has_hypertension')->default(false);
            $table->boolean('has_heart_disease')->default(false);
            $table->boolean('has_hiv_hepatitis')->default(false);
            $table->boolean('has_coagulation_problems')->default(false);
            $table->boolean('has_seizures')->default(false);
            $table->text('allergies')->nullable();
            $table->text('current_medications')->nullable();
            $table->boolean('has_been_hospitalized_or_operated')->default(false);
            $table->text('hospitalization_details')->nullable();

            // Antecedentes no patológicos
            $table->unsignedTinyInteger('oral_hygiene_times_per_day')->nullable();
            $table->boolean('smokes')->default(false);
            $table->boolean('drinks_alcohol')->default(false);

            // Adicionales (consentimiento extendido)
            $table->boolean('prolonged_bleeding_history')->default(false);
            $table->boolean('weight_loss_products_history')->default(false);
            $table->boolean('is_pregnant')->nullable();
            $table->text('pregnancy_time')->nullable();
            $table->text('additional_health_notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_histories');
    }
};
