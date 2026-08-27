<?php

namespace App\Models;

use App\Enums\ToothStatus;
use App\Enums\ToothSurface;
use App\Models\Concerns\Auditable;
use Database\Factories\OdontogramRecordFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OdontogramRecord extends Model
{
    /** @use HasFactory<OdontogramRecordFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'recorded_by',
        'tooth_number',
        'surface',
        'status',
        'note',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'tooth_number' => 'integer',
            'surface' => ToothSurface::class,
            'status' => ToothStatus::class,
            'recorded_at' => 'date',
            // Nota clínica de texto libre: cifrada en reposo (CLAUDE.md §5).
            'note' => 'encrypted',
        ];
    }

    /**
     * Historial de una pieza: del hallazgo más reciente al más antiguo.
     *
     * @param  Builder<OdontogramRecord>  $query
     */
    public function scopeOrderedForHistory($query): void
    {
        $query->orderByDesc('recorded_at')->orderByDesc('id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    protected function auditPatientId(): ?int
    {
        return $this->patient_id;
    }
}
