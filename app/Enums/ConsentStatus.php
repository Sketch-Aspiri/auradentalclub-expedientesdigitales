<?php

namespace App\Enums;

/**
 * Estado derivado de un consentimiento (no se almacena; se calcula a partir de signed_at /
 * voided_at en App\Models\Consent). Los colores del chip son tonos funcionales de UI, fuera de
 * la paleta neutra de marca, porque comunican estado y no identidad (CLAUDE.md §7).
 */
enum ConsentStatus: string
{
    case Draft = 'draft';
    case Signed = 'signed';
    case Voided = 'voided';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Signed => 'Firmado',
            self::Voided => 'Anulado',
        };
    }

    /**
     * Clases Tailwind para el chip de estado.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-aura-cream text-aura-gray-dark',
            self::Signed => 'bg-aura-olive/10 text-aura-olive',
            self::Voided => 'bg-red-50 text-red-700',
        };
    }
}
