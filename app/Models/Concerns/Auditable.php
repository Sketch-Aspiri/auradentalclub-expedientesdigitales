<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Escribe automáticamente en audit_logs cuando el modelo se crea, actualiza, elimina o
 * restaura (este último solo si el modelo usa SoftDeletes).
 * Para el evento "viewed" (que no es un evento de Eloquent) llama a recordView() explícitamente
 * desde el controlador, ver CLAUDE.md §5.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn (self $model) => $model->recordAuditEvent('created'));
        static::deleted(fn (self $model) => $model->recordAuditEvent('deleted'));

        static::updated(function (self $model) {
            // SoftDeletes::restore() hace un save() que dispara `updated` antes de `restored`.
            // Esa "actualización" solo pone deleted_at = null; no es una edición del expediente,
            // así que se ignora aquí para que el rastro quede como un único evento `restored`.
            if ($model->isRestoringFromTrash()) {
                return;
            }

            $model->recordAuditEvent('updated');
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::restored(fn (self $model) => $model->recordAuditEvent('restored'));
        }
    }

    protected function isRestoringFromTrash(): bool
    {
        if (! in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            return false;
        }

        $column = $this->getDeletedAtColumn();

        if (! $this->wasChanged($column) || ! is_null($this->{$column})) {
            return false;
        }

        // El único cambio real es deleted_at → null (updated_at se toca siempre en un save()).
        $ignorable = array_filter([$column, $this->getUpdatedAtColumn()]);

        return array_diff(array_keys($this->getChanges()), $ignorable) === [];
    }

    public function recordView(): void
    {
        $this->recordAuditEvent('viewed');
    }

    /**
     * Registra un evento `viewed` a lo sumo una vez por usuario / registro / día. Pensado
     * para accesos de alta frecuencia donde `recordView()` inundaría la tabla — p. ej. la
     * foto de identificación del paciente, que se pide en cada fila de un listado. Conserva
     * el rastro ("este usuario accedió a este expediente hoy") sin una fila por request.
     *
     * El dedup es best-effort: dos requests concurrentes pueden pasar ambas el `exists()` y
     * crear dos filas `viewed` el mismo día. El impacto es cosmético (una fila de más), no
     * una pérdida de rastro; no se usa un índice único para no acoplar el esquema a este caso.
     */
    public function recordViewOncePerDay(): void
    {
        if (! Auth::check()) {
            return;
        }

        $alreadyLoggedToday = AuditLog::query()
            ->where('user_id', Auth::id())
            ->where('auditable_type', static::class)
            ->where('auditable_id', $this->getKey())
            ->where('action', 'viewed')
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($alreadyLoggedToday) {
            return;
        }

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
