<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Support\PatientPhoto;
use Carbon\Carbon;
use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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
        // `photo_path` se omite a propósito: lo asigna el controlador vía App\Support\PatientPhoto
        // (nunca por asignación masiva de una ruta arbitraria).
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
            PatientPhoto::delete($patient->photo_path);
        });
    }

    protected function age(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->birth_date ? Carbon::parse($this->birth_date)->age : null,
        );
    }

    public function hasPhoto(): bool
    {
        return $this->photo_path !== null && $this->photo_path !== '';
    }

    /**
     * Iniciales para el avatar de reserva cuando el paciente no tiene foto.
     */
    protected function initials(): Attribute
    {
        return Attribute::make(get: function () {
            $words = preg_split('/\s+/', trim((string) $this->full_name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

            if ($words === []) {
                return '';
            }

            $first = Str::substr($words[0], 0, 1);
            $last = count($words) > 1 ? Str::substr(end($words), 0, 1) : '';

            return Str::upper($first.$last);
        });
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

    /**
     * Última consulta del paciente (por fecha, desempate por id) — para mostrar el doctor
     * tratante más reciente sin traer todo el historial. Respeta el soft delete de consultas.
     */
    public function latestConsultation(): HasOne
    {
        return $this->hasOne(Consultation::class)->latestOfMany(['consultation_date', 'id']);
    }

    public function odontogramRecords(): HasMany
    {
        return $this->hasMany(OdontogramRecord::class);
    }
}
