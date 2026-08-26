<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    /** @use HasFactory<\Database\Factories\PatientFactory> */
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
}
