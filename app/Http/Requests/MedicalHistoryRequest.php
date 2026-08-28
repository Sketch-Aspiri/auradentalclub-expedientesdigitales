<?php

namespace App\Http\Requests;

use App\Enums\GeneralHealthRating;
use App\Models\MedicalHistory;
use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicalHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Patient $patient */
        $patient = $this->route('patient');
        $medicalHistory = $patient->medicalHistory;

        return $medicalHistory
            ? $this->user()->can('update', $medicalHistory)
            : $this->user()->can('create', MedicalHistory::class);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'has_diabetes' => ['required', 'boolean'],
            'has_hypertension' => ['required', 'boolean'],
            'has_heart_disease' => ['required', 'boolean'],
            'has_hiv_hepatitis' => ['required', 'boolean'],
            'has_coagulation_problems' => ['required', 'boolean'],
            'has_seizures' => ['required', 'boolean'],
            'general_health_rating' => ['nullable', Rule::enum(GeneralHealthRating::class)],
            'last_medical_exam' => ['nullable', 'string', 'max:255'],
            'allergies' => ['nullable', 'string', 'max:2000'],
            'current_medications' => ['nullable', 'string', 'max:2000'],
            'has_been_hospitalized_or_operated' => ['required', 'boolean'],
            'hospitalization_details' => ['nullable', 'string', 'max:2000'],

            'oral_hygiene_times_per_day' => ['nullable', 'integer', 'min:0', 'max:20'],
            'smokes' => ['required', 'boolean'],
            'drinks_alcohol' => ['required', 'boolean'],

            'prolonged_bleeding_history' => ['required', 'boolean'],
            'weight_loss_products_history' => ['required', 'boolean'],
            'is_pregnant' => ['nullable', 'boolean'],
            'pregnancy_time' => ['nullable', 'string', 'max:255'],
            'additional_health_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'oral_hygiene_times_per_day.integer' => 'Ingresa un número de veces válido.',
            'oral_hygiene_times_per_day.max' => 'Ese número de veces al día no parece válido.',
        ];
    }
}
