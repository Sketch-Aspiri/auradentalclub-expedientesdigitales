<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sex = fake()->randomElement(['M', 'F']);

        return [
            'full_name' => $sex === 'M' ? fake()->firstNameMale().' '.fake()->lastName().' '.fake()->lastName() : fake()->firstNameFemale().' '.fake()->lastName().' '.fake()->lastName(),
            'birth_date' => fake()->dateTimeBetween('-80 years', '-5 years'),
            'sex' => $sex,
            'occupation' => fake()->jobTitle(),
            'marital_status' => fake()->randomElement(['Soltero/a', 'Casado/a', 'Divorciado/a', 'Viudo/a', 'Unión libre']),
            'address' => fake()->address(),
            'phone' => fake()->numerify('55########'),
            'email' => fake()->unique()->safeEmail(),
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->numerify('55########'),
        ];
    }
}
