<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Patient;
use App\Models\User;

/**
 * Los tres roles (doctor, administrador, superadmin) tienen el mismo acceso CRUD sobre
 * la ficha de identificación del paciente — decisión confirmada explícitamente para el
 * Sprint 1 (ver sprints/sprint-01-pacientes.md). forceDelete (borrado permanente, distinto
 * del soft delete de `delete`) se restringe a superadmin como resguardo adicional sobre
 * datos clínicos.
 */
class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Doctor, UserRole::Administrador, UserRole::Superadmin], true);
    }

    public function view(User $user, Patient $patient): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Patient $patient): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $this->viewAny($user);
    }

    public function restore(User $user, Patient $patient): bool
    {
        return $this->viewAny($user);
    }

    public function forceDelete(User $user, Patient $patient): bool
    {
        return $user->role === UserRole::Superadmin;
    }
}
