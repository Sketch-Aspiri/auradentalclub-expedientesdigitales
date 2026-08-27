<?php

namespace App\Http\Requests\Concerns;

use App\Enums\OralHygieneLevel;
use App\Enums\UserRole;
use Illuminate\Validation\Rule;

/**
 * Reglas y mensajes compartidos entre StoreConsultationRequest y UpdateConsultationRequest —
 * evita que ambos Form Requests se desincronicen silenciosamente. `doctor_id` se valida
 * aparte en cada request porque su tratamiento difiere entre alta y edición.
 */
trait ValidatesConsultationData
{
    /**
     * @return array<string, array<int, mixed>>
     */
    protected function consultationRules(): array
    {
        return [
            'consultation_date' => ['required', 'date', 'before_or_equal:today'],

            // Signos vitales — rangos amplios pero que descartan errores de captura evidentes.
            'blood_pressure' => ['nullable', 'string', 'max:20'],
            'heart_rate' => ['nullable', 'integer', 'min:20', 'max:300'],
            'temperature' => ['nullable', 'numeric', 'min:30', 'max:45'],

            // Exploración bucal
            'soft_tissues_notes' => ['nullable', 'string', 'max:5000'],
            'gums_periodontium_notes' => ['nullable', 'string', 'max:5000'],
            'oral_hygiene_level' => ['nullable', Rule::enum(OralHygieneLevel::class)],

            // Motivo, diagnóstico y plan
            'chief_complaint' => ['required', 'string', 'max:5000'],
            'clinical_diagnosis' => ['required', 'string', 'max:5000'],
            'treatment_plan' => ['nullable', 'string', 'max:5000'],
            'prognosis' => ['nullable', 'string', 'max:2000'],
            'risks_and_complications' => ['nullable', 'string', 'max:5000'],
            'treatment_alternatives' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function doctorIdRule(): array
    {
        return [Rule::exists('users', 'id')->where('role', UserRole::Doctor->value)];
    }

    /**
     * @return array<string, string>
     */
    protected function consultationMessages(): array
    {
        return [
            'doctor_id.required' => 'Selecciona el doctor que atiende la consulta.',
            'doctor_id.exists' => 'El doctor seleccionado no es válido.',
            'consultation_date.required' => 'La fecha de la consulta es obligatoria.',
            'consultation_date.date' => 'La fecha de la consulta no es válida.',
            'consultation_date.before_or_equal' => 'La fecha de la consulta no puede ser futura.',
            'heart_rate.integer' => 'La frecuencia cardiaca debe ser un número entero.',
            'heart_rate.min' => 'La frecuencia cardiaca capturada no parece válida.',
            'heart_rate.max' => 'La frecuencia cardiaca capturada no parece válida.',
            'temperature.numeric' => 'La temperatura debe ser un número.',
            'temperature.min' => 'La temperatura capturada no parece válida.',
            'temperature.max' => 'La temperatura capturada no parece válida.',
            'chief_complaint.required' => 'El motivo de consulta es obligatorio.',
            'clinical_diagnosis.required' => 'El diagnóstico clínico es obligatorio.',
        ];
    }
}
