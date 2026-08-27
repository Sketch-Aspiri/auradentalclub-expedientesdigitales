<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Consultation;
use App\Models\User;

/**
 * Mismo acceso que PatientPolicy — decisión confirmada explícitamente para el Sprint 3
 * (ver sprints/sprint-03-consultas.md): los tres roles pueden ver y editar cualquier
 * consulta (es un solo consultorio, la continuidad de atención entre doctores lo requiere).
 * forceDelete (borrado permanente) se restringe a superadmin como resguardo sobre un
 * registro clínico legal (NOM-004).
 */
class ConsultationPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Doctor, UserRole::Administrador, UserRole::Superadmin], true);
    }

    public function view(User $user, Consultation $consultation): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Consultation $consultation): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Consultation $consultation): bool
    {
        return $this->viewAny($user);
    }

    public function restore(User $user, Consultation $consultation): bool
    {
        return $this->viewAny($user);
    }

    public function forceDelete(User $user, Consultation $consultation): bool
    {
        return $user->role === UserRole::Superadmin;
    }
}
