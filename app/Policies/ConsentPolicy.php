<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Consent;
use App\Models\User;

/**
 * Mismo criterio base que PatientPolicy / ConsultationPolicy / OdontogramRecordPolicy: los tres
 * roles ven y gestionan cualquier consentimiento (un solo consultorio, la continuidad de
 * atención entre doctores lo exige).
 *
 * Reglas propias del consentimiento (confirmado con el cliente el 2026-08-28):
 *  - `update` / `delete` / `sign` solo mientras está en borrador.
 *  - `void` solo cuando está firmado (un borrador se elimina, no se anula).
 *  - `forceDelete` (borrado permanente de un registro clínico legal, NOM-004) queda en superadmin.
 */
class ConsentPolicy
{
    private function isClinicalStaff(User $user): bool
    {
        return in_array($user->role, [UserRole::Doctor, UserRole::Administrador, UserRole::Superadmin], true);
    }

    public function viewAny(User $user): bool
    {
        return $this->isClinicalStaff($user);
    }

    public function view(User $user, Consent $consent): bool
    {
        return $this->isClinicalStaff($user);
    }

    public function create(User $user): bool
    {
        return $this->isClinicalStaff($user);
    }

    public function update(User $user, Consent $consent): bool
    {
        return $this->isClinicalStaff($user) && $consent->isDraft();
    }

    public function delete(User $user, Consent $consent): bool
    {
        return $this->isClinicalStaff($user) && $consent->isDraft();
    }

    public function restore(User $user, Consent $consent): bool
    {
        return $this->isClinicalStaff($user);
    }

    public function forceDelete(User $user, Consent $consent): bool
    {
        return $user->role === UserRole::Superadmin;
    }

    public function sign(User $user, Consent $consent): bool
    {
        return $this->isClinicalStaff($user) && $consent->isDraft();
    }

    public function void(User $user, Consent $consent): bool
    {
        return $this->isClinicalStaff($user) && $consent->isSigned();
    }
}
