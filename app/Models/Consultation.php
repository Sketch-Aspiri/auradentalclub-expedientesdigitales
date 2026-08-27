<?php

namespace App\Models;

use App\Enums\OralHygieneLevel;
use App\Models\Concerns\Auditable;
use Database\Factories\ConsultationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultation extends Model
{
    /** @use HasFactory<ConsultationFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'consultation_date',
        'blood_pressure',
        'heart_rate',
        'temperature',
        'soft_tissues_notes',
        'gums_periodontium_notes',
        'oral_hygiene_level',
        'chief_complaint',
        'clinical_diagnosis',
        'treatment_plan',
        'prognosis',
        'risks_and_complications',
        'treatment_alternatives',
    ];

    protected function casts(): array
    {
        return [
            'consultation_date' => 'date',
            'heart_rate' => 'integer',
            'temperature' => 'decimal:1',
            'oral_hygiene_level' => OralHygieneLevel::class,
            // Notas clínicas de texto libre: cifradas en reposo (CLAUDE.md §5).
            'soft_tissues_notes' => 'encrypted',
            'gums_periodontium_notes' => 'encrypted',
            'chief_complaint' => 'encrypted',
            'clinical_diagnosis' => 'encrypted',
            'treatment_plan' => 'encrypted',
            'prognosis' => 'encrypted',
            'risks_and_complications' => 'encrypted',
            'treatment_alternatives' => 'encrypted',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    protected function auditPatientId(): ?int
    {
        return $this->patient_id;
    }
}
