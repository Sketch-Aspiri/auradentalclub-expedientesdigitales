<?php

namespace App\Enums;

enum OralHygieneLevel: string
{
    case Buena = 'buena';
    case Regular = 'regular';
    case Mala = 'mala';

    public function label(): string
    {
        return match ($this) {
            self::Buena => 'Buena',
            self::Regular => 'Regular',
            self::Mala => 'Mala',
        };
    }
}
