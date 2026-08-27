<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Support\PatientPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

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

    /**
     * Paciente con foto de identificación. Requiere `Storage::fake('local')` en la prueba
     * para no escribir en el disco real.
     */
    public function withPhoto(): static
    {
        return $this->afterCreating(function (Patient $patient) {
            $patient->forceFill([
                'photo_path' => UploadedFile::fake()->image('paciente.jpg', 400, 400)
                    ->store(PatientPhoto::DIRECTORY, PatientPhoto::DISK),
            ])->saveQuietly(); // sin disparar el evento `updated` de auditoría en el seeding
        });
    }
}
