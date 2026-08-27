<?php

namespace Database\Factories;

use App\Enums\ToothStatus;
use App\Enums\ToothSurface;
use App\Enums\UserRole;
use App\Models\OdontogramRecord;
use App\Models\Patient;
use App\Models\User;
use App\Support\Dentition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OdontogramRecord>
 */
class OdontogramRecordFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement([
            ToothStatus::Caries, ToothStatus::Obturado, ToothStatus::Sellador, ToothStatus::Fractura,
        ]);

        return [
            'patient_id' => Patient::factory(),
            'recorded_by' => User::factory()->role(UserRole::Doctor),
            'tooth_number' => fake()->randomElement(Dentition::all()),
            'surface' => fake()->randomElement(ToothSurface::cases()),
            'status' => $status,
            'note' => null,
            'recorded_at' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
        ];
    }

    /**
     * Hallazgo sobre el diente completo (corona, endodoncia, extracción...).
     */
    public function wholeTooth(?ToothStatus $status = null): static
    {
        return $this->state(fn () => [
            'surface' => null,
            'status' => $status ?? fake()->randomElement([
                ToothStatus::Corona, ToothStatus::Endodoncia, ToothStatus::Implante, ToothStatus::Extraido,
            ]),
        ]);
    }

    public function forSurface(ToothSurface $surface, ToothStatus $status): static
    {
        return $this->state(fn () => [
            'surface' => $surface,
            'status' => $status,
        ]);
    }
}
