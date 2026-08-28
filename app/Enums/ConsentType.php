<?php

namespace App\Enums;

/**
 * Tipo de consentimiento informado. Ampliable: agregar un caso aquí y extender el enum de la
 * columna `consents.type` con una migración nueva (CLAUDE.md §8). El texto legal canónico de
 * cada tipo (NOM-004 / NOM-013 para extracción) lo aporta la clínica; ver la vista de impresión.
 */
enum ConsentType: string
{
    case General = 'general';
    case Extraction = 'extraction';

    public function label(): string
    {
        return match ($this) {
            self::General => 'General',
            self::Extraction => 'Extracción dental',
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function catalog(): array
    {
        return array_map(fn (self $t) => [
            'value' => $t->value,
            'label' => $t->label(),
        ], self::cases());
    }
}
