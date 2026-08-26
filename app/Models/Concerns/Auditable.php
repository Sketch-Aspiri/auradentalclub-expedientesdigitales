<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Escribe automáticamente en audit_logs cuando el modelo se crea, actualiza o elimina.
 * Para el evento "viewed" (que no es un evento de Eloquent) llama a recordView() explícitamente
 * desde el controlador, ver CLAUDE.md §5.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn (self $model) => $model->recordAuditEvent('created'));
        static::updated(fn (self $model) => $model->recordAuditEvent('updated'));
        static::deleted(fn (self $model) => $model->recordAuditEvent('deleted'));
    }

    public function recordView(): void
    {
        $this->recordAuditEvent('viewed');
    }

    protected function recordAuditEvent(string $action): void
    {
        if (! Auth::check()) {
            return;
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'patient_id' => $this->auditPatientId(),
            'action' => $action,
            'auditable_type' => static::class,
            'auditable_id' => $this->getKey(),
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Los modelos que cuelgan de un paciente (consultas, consentimientos, archivos, etc.)
     * sobreescriben esto para poblar audit_logs.patient_id; por defecto no hay paciente asociado.
     */
    protected function auditPatientId(): ?int
    {
        return null;
    }
}
