<?php

namespace App\Enums;

enum UserRole: string
{
    case Doctor = 'doctor';
    case Administrador = 'administrador';
    case Superadmin = 'superadmin';

    public function label(): string
    {
        return match ($this) {
            self::Doctor => 'Doctor',
            self::Administrador => 'Administrador',
            self::Superadmin => 'Super administrador',
        };
    }
}
