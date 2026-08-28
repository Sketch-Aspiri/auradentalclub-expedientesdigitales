<?php

namespace Database\Factories;

use App\Enums\ConsentGiver;
use App\Enums\ConsentType;
use App\Enums\UserRole;
use App\Models\Consent;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Consent>
 */
class ConsentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => User::factory()->role(UserRole::Doctor),
            'type' => ConsentType::General,
            'given_by' => ConsentGiver::Patient,
            'relationship' => null,
            'diagnosis' => 'Caries de segundo grado en órgano dentario 36 con compromiso dentinario.',
            'treatment_plan' => 'Remoción de tejido cariado y restauración con resina compuesta.',
            'prognosis' => 'Favorable con controles semestrales.',
            'risks_complications' => 'Sensibilidad postoperatoria, necesidad futura de endodoncia si la caries es más profunda de lo previsto.',
            'management_alternatives' => 'Incrustación cerámica o corona en caso de destrucción coronaria mayor.',
            'authorizes_photos_xrays' => true,
            'patient_accepts' => true,
            'signed_at' => null,
            'voided_at' => null,
            'voided_by' => null,
            'void_reason' => null,
        ];
    }

    /**
     * Consentimiento de extracción, otorgado por un familiar.
     */
    public function extraction(): static
    {
        return $this->state(fn () => [
            'type' => ConsentType::Extraction,
            'given_by' => ConsentGiver::FamilyMember,
            'relationship' => 'Madre',
            'diagnosis' => 'Tercer molar inferior izquierdo (38) retenido con pericoronaritis recurrente.',
            'treatment_plan' => 'Extracción quirúrgica del órgano dentario 38 bajo anestesia local.',
            'risks_complications' => 'Inflamación, dolor, sangrado, parestesia transitoria del nervio dentario inferior, alveolitis.',
        ]);
    }

    /**
     * Consentimiento ya firmado. Las rutas de firma son ficticias; las pruebas que sirven la
     * firma deben usar Storage::fake('local') y colocar el archivo.
     */
    public function signed(): static
    {
        return $this->state(fn () => [
            'signed_at' => now()->subDay(),
            'patient_signature_path' => 'consents/signatures/'.fake()->uuid().'.png',
            'doctor_signature_path' => 'consents/signatures/'.fake()->uuid().'.png',
        ]);
    }

    /**
     * Consentimiento firmado y luego anulado.
     */
    public function voided(): static
    {
        return $this->signed()->state(fn () => [
            'voided_at' => now(),
            'voided_by' => User::factory()->role(UserRole::Administrador),
            'void_reason' => 'Se capturó el diente equivocado; se reemplaza por un consentimiento nuevo.',
        ]);
    }
}
