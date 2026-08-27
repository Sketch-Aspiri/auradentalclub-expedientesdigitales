<?php

namespace App\Support;

use App\Enums\ToothSurface;

/**
 * Dentición permanente en numeración FDI (CLAUDE.md §6, confirmada con la clínica el
 * 2026-08-27 mediante el odontograma de referencia). Cuadrantes: 1 = superior derecho,
 * 2 = superior izquierdo, 3 = inferior izquierdo, 4 = inferior derecho. En cada cuadrante
 * la posición 1 es el incisivo central y la 8 el tercer molar.
 */
final class Dentition
{
    /** Filas superior e inferior, ya ordenadas como se dibujan (de derecha a izquierda del paciente). */
    public const UPPER_RIGHT = [18, 17, 16, 15, 14, 13, 12, 11];

    public const UPPER_LEFT = [21, 22, 23, 24, 25, 26, 27, 28];

    public const LOWER_RIGHT = [48, 47, 46, 45, 44, 43, 42, 41];

    public const LOWER_LEFT = [31, 32, 33, 34, 35, 36, 37, 38];

    /**
     * @return list<int>
     */
    public static function all(): array
    {
        return [
            ...self::UPPER_RIGHT, ...self::UPPER_LEFT,
            ...self::LOWER_RIGHT, ...self::LOWER_LEFT,
        ];
    }

    public static function isValid(int $toothNumber): bool
    {
        return in_array($toothNumber, self::all(), true);
    }

    /**
     * Incisivos y caninos (posiciones 1-3 del cuadrante): sin cara oclusal de masticación,
     * la superficie central se rotula "Incisal".
     */
    public static function isAnterior(int $toothNumber): bool
    {
        return self::isValid($toothNumber) && ($toothNumber % 10) <= 3;
    }

    /**
     * Reparto de las 5 zonas del diente esquemático a superficies anatómicas, orientado
     * para que "mesial" siempre apunte hacia la línea media de la arcada tal como se
     * dibuja el odontograma. Es una convención de presentación, no dato clínico.
     *
     * @return array{top: ToothSurface, bottom: ToothSurface, left: ToothSurface, right: ToothSurface, center: ToothSurface}
     */
    public static function surfaceLayout(int $toothNumber): array
    {
        $quadrant = intdiv($toothNumber, 10);
        $isUpper = in_array($quadrant, [1, 2], true);
        $isRightSide = in_array($quadrant, [1, 4], true);

        return [
            'top' => $isUpper ? ToothSurface::Vestibular : ToothSurface::Lingual,
            'bottom' => $isUpper ? ToothSurface::Lingual : ToothSurface::Vestibular,
            'left' => $isRightSide ? ToothSurface::Distal : ToothSurface::Mesial,
            'right' => $isRightSide ? ToothSurface::Mesial : ToothSurface::Distal,
            'center' => ToothSurface::Oclusal,
        ];
    }
}
