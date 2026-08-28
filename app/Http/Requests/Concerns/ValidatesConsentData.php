<?php

namespace App\Http\Requests\Concerns;

use App\Enums\ConsentGiver;
use App\Enums\ConsentType;
use App\Enums\UserRole;
use Illuminate\Validation\Rule;

/**
 * Reglas y mensajes compartidos entre StoreConsentRequest y UpdateConsentRequest — evita que
 * ambos Form Requests se desincronicen. `doctor_id` se valida aparte en cada request porque su
 * tratamiento difiere entre alta y edición (igual patrón que ValidatesConsultationData).
 */
trait ValidatesConsentData
{
    /**
     * @return array<string, array<int, mixed>>
     */
    protected function consentRules(): array
    {
        return [
            'type' => ['required', Rule::enum(ConsentType::class)],
            'given_by' => ['required', Rule::enum(ConsentGiver::class)],
            'relationship' => ['nullable', 'required_unless:given_by,'.ConsentGiver::Patient->value, 'string', 'max:255'],

            // Sección "Diagnóstico y plan" de la hoja: el plan y los riesgos son el núcleo;
            // diagnóstico, pronóstico y alternativas quedan opcionales.
            'diagnosis' => ['nullable', 'string', 'max:5000'],
            'treatment_plan' => ['required', 'string', 'max:5000'],
            'prognosis' => ['nullable', 'string', 'max:5000'],
            'risks_complications' => ['required', 'string', 'max:5000'],
            'management_alternatives' => ['nullable', 'string', 'max:5000'],

            'authorizes_photos_xrays' => ['boolean'],
            'patient_accepts' => ['boolean'],
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function doctorIdRule(): array
    {
        return [Rule::exists('users', 'id')->where('role', UserRole::Doctor->value)];
    }

    /**
     * @return array<string, string>
     */
    protected function consentMessages(): array
    {
        return [
            'doctor_id.required' => 'Selecciona el doctor responsable del consentimiento.',
            'doctor_id.exists' => 'El doctor seleccionado no es válido.',
            'type.required' => 'El tipo de consentimiento es obligatorio.',
            'given_by.required' => 'Indica quién otorga el consentimiento.',
            'relationship.required_unless' => 'El parentesco es obligatorio cuando no firma el propio paciente.',
            'treatment_plan.required' => 'El plan de tratamiento es obligatorio.',
            'risks_complications.required' => 'Los riesgos y complicaciones posibles son obligatorios.',
        ];
    }
}
