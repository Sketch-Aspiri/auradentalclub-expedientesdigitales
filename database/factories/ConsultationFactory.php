<?php

namespace Database\Factories;

use App\Enums\OralHygieneLevel;
use App\Enums\UserRole;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Consultation>
 */
class ConsultationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => User::factory()->role(UserRole::Doctor),
            'consultation_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'blood_pressure' => fake()->randomElement(['120/80', '110/70', '130/85', '118/76']),
            'heart_rate' => fake()->numberBetween(58, 92),
            'temperature' => fake()->randomFloat(1, 36.0, 37.3),
            'soft_tissues_notes' => null,
            'gums_periodontium_notes' => null,
            'oral_hygiene_level' => fake()->randomElement(OralHygieneLevel::cases()),
            'chief_complaint' => 'Dolor en molar inferior derecho al masticar',
            'clinical_diagnosis' => 'Caries profunda en órgano dentario 46',
            'treatment_plan' => 'Tratamiento de conductos y restauración con corona',
            'prognosis' => 'Favorable con seguimiento',
            'risks_and_complications' => null,
            'treatment_alternatives' => 'Extracción y rehabilitación con implante',
        ];
    }
}
