<?php

namespace Database\Factories;

use App\Enums\GeneralHealthRating;
use App\Models\MedicalHistory;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalHistory>
 */
class MedicalHistoryFactory extends Factory
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
            'has_diabetes' => false,
            'has_hypertension' => false,
            'has_heart_disease' => false,
            'has_hiv_hepatitis' => false,
            'has_coagulation_problems' => false,
            'has_seizures' => false,
            'general_health_rating' => fake()->randomElement(GeneralHealthRating::cases()),
            'last_medical_exam' => fake()->randomElement(['Hace 6 meses', 'Hace un año', 'Nunca', null]),
            'allergies' => fake()->boolean(30) ? 'Penicilina' : null,
            'current_medications' => null,
            'has_been_hospitalized_or_operated' => false,
            'hospitalization_details' => null,
            'oral_hygiene_times_per_day' => fake()->numberBetween(1, 3),
            'smokes' => false,
            'drinks_alcohol' => fake()->boolean(20),
            'prolonged_bleeding_history' => false,
            'weight_loss_products_history' => false,
            'is_pregnant' => null,
            'pregnancy_time' => null,
            'additional_health_notes' => null,
        ];
    }
}
