<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\OdontogramRecord;
use App\Models\User;

/**
 * Mismo acceso que PatientPolicy / ConsultationPolicy — los tres roles ven y editan el
 * odontograma de cualquier paciente (un solo consultorio, la continuidad de atención entre
 * doctores lo exige). El odontograma es de solo-anexar: no hay `update` de una fila; la
 * corrección se hace registrando de nuevo o archivando (`delete`) el hallazgo erróneo.
 * `forceDelete` (borrado permanente de un registro clínico, NOM-004) queda en superadmin.
 */
class OdontogramRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Doctor, UserRole::Administrador, UserRole::Superadmin], true);
    }

    public function view(User $user, OdontogramRecord $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, OdontogramRecord $record): bool
    {
        return $this->viewAny($user);
    }

    public function restore(User $user, OdontogramRecord $record): bool
    {
        return $this->viewAny($user);
    }

    public function forceDelete(User $user, OdontogramRecord $record): bool
    {
        return $user->role === UserRole::Superadmin;
    }
}
