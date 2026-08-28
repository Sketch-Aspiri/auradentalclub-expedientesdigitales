<?php

namespace App\Enums;

/**
 * Autopercepción de salud del paciente ("¿Cómo describiría usted su salud?") — parte de la
 * sección Diagnóstico del consentimiento informado de la clínica y de la historia clínica.
 */
enum GeneralHealthRating: string
{
    case Excelente = 'excelente';
    case Buena = 'buena';
    case Regular = 'regular';
    case Mala = 'mala';

    public function label(): string
    {
        return match ($this) {
            self::Excelente => 'Excelente',
            self::Buena => 'Buena',
            self::Regular => 'Regular',
            self::Mala => 'Mala',
        };
    }
}
