<?php

namespace App\Models;

use App\Enums\ConsentGiver;
use App\Enums\ConsentStatus;
use App\Enums\ConsentType;
use App\Models\Concerns\Auditable;
use Database\Factories\ConsentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Consentimiento informado (Sprint 5, CLAUDE.md §4.5).
 *
 * Estados (derivados, no almacenados): borrador → firmado → anulado. Un consentimiento firmado
 * es inmutable; corregirlo es anularlo (con motivo) y crear uno nuevo. La autorización de cada
 * transición vive en ConsentPolicy; las firmas se persisten desde App\Livewire\Consents\SignConsent.
 */
class Consent extends Model
{
    /** @use HasFactory<ConsentFactory> */
    use Auditable, HasFactory, SoftDeletes;

    /**
     * Se omiten a propósito del fillable: `*_signature_path`, `signed_at`, `voided_*` y
     * `health_snapshot` — los asigna el componente de firma / la acción de anular / el
     * controlador al crear, nunca asignación masiva.
     */
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'type',
        'given_by',
        'relationship',
        'diagnosis',
        'treatment_plan',
        'prognosis',
        'risks_complications',
        'management_alternatives',
        'authorizes_photos_xrays',
        'patient_accepts',
    ];

    protected function casts(): array
    {
        return [
            'type' => ConsentType::class,
            'given_by' => ConsentGiver::class,
            'authorizes_photos_xrays' => 'boolean',
            'patient_accepts' => 'boolean',
            'signed_at' => 'datetime',
            'voided_at' => 'datetime',
            // Texto clínico libre / datos de salud: cifrados en reposo (CLAUDE.md §5).
            'diagnosis' => 'encrypted',
            'treatment_plan' => 'encrypted',
            'prognosis' => 'encrypted',
            'risks_complications' => 'encrypted',
            'management_alternatives' => 'encrypted',
            'void_reason' => 'encrypted',
            'health_snapshot' => 'encrypted:array',
        ];
    }

    /**
     * Copia fija de las respuestas de salud del paciente (sección "Diagnóstico" de la hoja de
     * consentimiento) tomadas de su historia clínica al momento de crear el consentimiento.
     * Si el paciente aún no tiene historia clínica, devuelve un arreglo vacío.
     *
     * @return array<string, mixed>
     */
    public static function snapshotHealthFrom(?MedicalHistory $history): array
    {
        if ($history === null) {
            return [];
        }

        return [
            'general_health_rating' => $history->general_health_rating?->value,
            'last_medical_exam' => $history->last_medical_exam,
            'prolonged_bleeding_history' => $history->prolonged_bleeding_history,
            'weight_loss_products_history' => $history->weight_loss_products_history,
            'current_medications' => $history->current_medications,
            'allergies' => $history->allergies,
            'is_pregnant' => $history->is_pregnant,
            'pregnancy_time' => $history->pregnancy_time,
            'additional_health_notes' => $history->additional_health_notes,
        ];
    }

    public function isDraft(): bool
    {
        return $this->signed_at === null && $this->voided_at === null;
    }

    public function isSigned(): bool
    {
        return $this->signed_at !== null && $this->voided_at === null;
    }

    public function isVoided(): bool
    {
        return $this->voided_at !== null;
    }

    public function status(): ConsentStatus
    {
        return match (true) {
            $this->isVoided() => ConsentStatus::Voided,
            $this->isSigned() => ConsentStatus::Signed,
            default => ConsentStatus::Draft,
        };
    }

    public function recordSigned(): void
    {
        $this->logAudit('signed');
    }

    public function recordVoided(): void
    {
        $this->logAudit('voided');
    }

    /**
     * @param  Builder<Consent>  $query
     */
    public function scopeOrderedForHistory($query): void
    {
        $query->orderByDesc('created_at')->orderByDesc('id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    /**
     * Rutas de las cuatro firmas posibles, para servirlas y para purgarlas del disco.
     *
     * @return array<string, string|null>
     */
    public function signaturePaths(): array
    {
        return [
            'patient' => $this->patient_signature_path,
            'doctor' => $this->doctor_signature_path,
            'witness1' => $this->witness1_signature_path,
            'witness2' => $this->witness2_signature_path,
        ];
    }

    protected function auditPatientId(): ?int
    {
        return $this->patient_id;
    }
}
