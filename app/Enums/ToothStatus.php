<?php

namespace App\Enums;

/**
 * Catálogo de estados de un diente / superficie. Ampliable: agregar un caso aquí y
 * extender el enum de la columna `status` con una migración nueva (CLAUDE.md §8).
 *
 * Cada estado aplica al diente completo, a una superficie concreta, o a ambos:
 *  - Superficie: hallazgos localizados (caries, obturación, sellador, fractura).
 *  - Diente completo: afectan a toda la pieza (corona, endodoncia, extracción...).
 *  - Ambos: "sano" puede registrarse sobre una superficie o sobre el diente entero.
 */
enum ToothStatus: string
{
    case Sano = 'sano';
    case Caries = 'caries';
    case Obturado = 'obturado';
    case Sellador = 'sellador';
    case Fractura = 'fractura';
    case Corona = 'corona';
    case Endodoncia = 'endodoncia';
    case ProtesisFija = 'protesis_fija';
    case Implante = 'implante';
    case Movilidad = 'movilidad';
    case Extraido = 'extraido';
    case Ausente = 'ausente';

    public function label(): string
    {
        return match ($this) {
            self::Sano => 'Sano',
            self::Caries => 'Caries',
            self::Obturado => 'Obturado',
            self::Sellador => 'Sellador',
            self::Fractura => 'Fractura',
            self::Corona => 'Corona',
            self::Endodoncia => 'Endodoncia',
            self::ProtesisFija => 'Prótesis fija',
            self::Implante => 'Implante',
            self::Movilidad => 'Movilidad',
            self::Extraido => 'Extraído',
            self::Ausente => 'Ausente',
        };
    }

    public function appliesToSurface(): bool
    {
        return match ($this) {
            self::Sano, self::Caries, self::Obturado, self::Sellador, self::Fractura => true,
            default => false,
        };
    }

    public function appliesToWholeTooth(): bool
    {
        return match ($this) {
            self::Caries, self::Obturado, self::Sellador, self::Fractura => false,
            default => true,
        };
    }

    /**
     * Un estado de diente completo que implica que la pieza ya no está en boca — el
     * odontograma la dibuja "vacía" e ignora los hallazgos de superficie.
     */
    public function meansToothIsGone(): bool
    {
        return in_array($this, [self::Extraido, self::Ausente], true);
    }

    /**
     * Color funcional para el diagrama y la leyenda. Tonos de UI (fuera de la paleta neutra
     * Aura) porque codifican información clínica, no identidad — CLAUDE.md §7. Todos alcanzan
     * al menos 3:1 de contraste sobre blanco (WCAG 2.2, objetos gráficos) porque se usan como
     * trazo fino del marco / de la cruz de la pieza, no solo como relleno.
     */
    public function color(): string
    {
        return match ($this) {
            self::Sano => '#FFFFFF',
            self::Caries => '#DC2626',
            self::Obturado => '#2563EB',
            self::Sellador => '#0D9488',
            self::Fractura => '#B45309',
            self::Corona => '#A16207',
            self::Endodoncia => '#7C3AED',
            self::ProtesisFija => '#4F46E5',
            self::Implante => '#475569',
            self::Movilidad => '#EA580C',
            self::Extraido => '#57534E',
            self::Ausente => '#78716C',
        };
    }

    /**
     * @return array<int, array{value: string, label: string, color: string, surface: bool, whole: bool}>
     */
    public static function catalog(): array
    {
        return array_map(fn (self $s) => [
            'value' => $s->value,
            'label' => $s->label(),
            'color' => $s->color(),
            'surface' => $s->appliesToSurface(),
            'whole' => $s->appliesToWholeTooth(),
        ], self::cases());
    }
}
