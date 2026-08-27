<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Carbon\Carbon;
use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'full_name',
        'birth_date',
        'sex',
        'occupation',
        'marital_status',
        'address',
        'phone',
        'email',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        // El borrado permanente de un paciente elimina cada registro clínico hijo de forma
        // explícita (no por cascada de MySQL) para que el trait Auditable registre su propio
        // evento `deleted` en audit_logs. Ampliar esta lista al añadir nuevos módulos clínicos
        // (consentimientos, archivos, hoja de evolución, odontograma).
        static::forceDeleting(function (Patient $patient) {
            $patient->consultations()->withTrashed()->get()->each->forceDelete();
            $patient->odontogramRecords()->withTrashed()->get()->each->forceDelete();
            $patient->medicalHistory?->delete();
        });
    }

    protected function age(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->birth_date ? Carbon::parse($this->birth_date)->age : null,
        );
    }

    protected function auditPatientId(): ?int
    {
        return $this->getKey();
    }

    public function medicalHistory(): HasOne
    {
        return $this->hasOne(MedicalHistory::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function odontogramRecords(): HasMany
    {
        return $this->hasMany(OdontogramRecord::class);
    }
}
