<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalHistory extends Model
{
    /** @use HasFactory<\Database\Factories\MedicalHistoryFactory> */
    use Auditable, HasFactory;

    protected $fillable = [
        'patient_id',
        'has_diabetes',
        'has_hypertension',
        'has_heart_disease',
        'has_hiv_hepatitis',
        'has_coagulation_problems',
        'has_seizures',
        'allergies',
        'current_medications',
        'has_been_hospitalized_or_operated',
        'hospitalization_details',
        'oral_hygiene_times_per_day',
        'smokes',
        'drinks_alcohol',
        'prolonged_bleeding_history',
        'weight_loss_products_history',
        'is_pregnant',
        'pregnancy_time',
        'additional_health_notes',
    ];

    protected function casts(): array
    {
        return [
            'has_diabetes' => 'boolean',
            'has_hypertension' => 'boolean',
            'has_heart_disease' => 'boolean',
            'has_hiv_hepatitis' => 'boolean',
            'has_coagulation_problems' => 'boolean',
            'has_seizures' => 'boolean',
            // Campos clínicos de texto libre: cifrados en reposo (CLAUDE.md §5).
            'allergies' => 'encrypted',
            'current_medications' => 'encrypted',
            'has_been_hospitalized_or_operated' => 'boolean',
            'hospitalization_details' => 'encrypted',
            'oral_hygiene_times_per_day' => 'integer',
            'smokes' => 'boolean',
            'drinks_alcohol' => 'boolean',
            'prolonged_bleeding_history' => 'boolean',
            'weight_loss_products_history' => 'boolean',
            'is_pregnant' => 'boolean',
            'pregnancy_time' => 'encrypted',
            'additional_health_notes' => 'encrypted',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    protected function auditPatientId(): ?int
    {
        return $this->patient_id;
    }
}
