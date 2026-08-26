<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\MedicalHistory;
use App\Models\User;

/**
 * Mismo acceso que PatientPolicy — decisión confirmada explícitamente para el Sprint 2
 * (ver sprints/sprint-02-historia-clinica.md): los tres roles tienen CRUD completo sobre
 * la historia clínica. forceDelete restringido a superadmin, igual que en Patients.
 */
class MedicalHistoryPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Doctor, UserRole::Administrador, UserRole::Superadmin], true);
    }

    public function view(User $user, MedicalHistory $medicalHistory): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, MedicalHistory $medicalHistory): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, MedicalHistory $medicalHistory): bool
    {
        return $this->viewAny($user);
    }

    public function restore(User $user, MedicalHistory $medicalHistory): bool
    {
        return $this->viewAny($user);
    }

    public function forceDelete(User $user, MedicalHistory $medicalHistory): bool
    {
        return $user->role === UserRole::Superadmin;
    }
}
