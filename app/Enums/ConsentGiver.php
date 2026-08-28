<?php

namespace App\Enums;

/**
 * Quién otorga el consentimiento (`consents.given_by`, CLAUDE.md §6): el propio paciente, su
 * representante legal, o un familiar. Cuando no es el paciente, se captura el parentesco.
 */
enum ConsentGiver: string
{
    case Patient = 'paciente';
    case LegalRepresentative = 'representante_legal';
    case FamilyMember = 'familiar';

    public function label(): string
    {
        return match ($this) {
            self::Patient => 'El paciente',
            self::LegalRepresentative => 'Representante legal',
            self::FamilyMember => 'Familiar',
        };
    }

    public function requiresRelationship(): bool
    {
        return $this !== self::Patient;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function catalog(): array
    {
        return array_map(fn (self $g) => [
            'value' => $g->value,
            'label' => $g->label(),
        ], self::cases());
    }
}
