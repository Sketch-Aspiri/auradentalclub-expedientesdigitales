<?php

namespace App\Enums;

/**
 * Superficies dentales del odontograma esquemático de 5 zonas. `null` (ausencia de este
 * enum) representa un hallazgo sobre el diente completo, no una superficie.
 */
enum ToothSurface: string
{
    case Mesial = 'mesial';
    case Distal = 'distal';
    case Oclusal = 'oclusal';
    case Vestibular = 'vestibular';
    case Lingual = 'lingual';

    /**
     * Etiqueta para la interfaz. La cara oclusal se llama "Incisal" en dientes anteriores
     * (incisivos y caninos), donde no hay superficie de masticación.
     */
    public function label(bool $anteriorTooth = false): string
    {
        return match ($this) {
            self::Mesial => 'Mesial',
            self::Distal => 'Distal',
            self::Oclusal => $anteriorTooth ? 'Incisal' : 'Oclusal',
            self::Vestibular => 'Vestibular',
            self::Lingual => 'Lingual / Palatina',
        };
    }
}
