<?php

use App\Enums\ToothStatus;
use App\Support\Dentition;

test('los hallazgos localizados aplican a superficie pero no al diente completo', function () {
    foreach ([ToothStatus::Caries, ToothStatus::Obturado, ToothStatus::Sellador, ToothStatus::Fractura] as $status) {
        expect($status->appliesToSurface())->toBeTrue()
            ->and($status->appliesToWholeTooth())->toBeFalse();
    }
});

test('los estados de pieza completa no aplican a una superficie', function () {
    foreach ([ToothStatus::Corona, ToothStatus::Endodoncia, ToothStatus::Implante, ToothStatus::Extraido, ToothStatus::Ausente, ToothStatus::Movilidad, ToothStatus::ProtesisFija] as $status) {
        expect($status->appliesToWholeTooth())->toBeTrue()
            ->and($status->appliesToSurface())->toBeFalse();
    }
});

test('sano se puede registrar tanto en superficie como en diente completo', function () {
    expect(ToothStatus::Sano->appliesToSurface())->toBeTrue()
        ->and(ToothStatus::Sano->appliesToWholeTooth())->toBeTrue();
});

test('extraido y ausente marcan la pieza como fuera de boca', function () {
    expect(ToothStatus::Extraido->meansToothIsGone())->toBeTrue()
        ->and(ToothStatus::Ausente->meansToothIsGone())->toBeTrue()
        ->and(ToothStatus::Corona->meansToothIsGone())->toBeFalse();
});

test('el catálogo expone los 12 estados con color y alcance', function () {
    $catalog = ToothStatus::catalog();

    expect($catalog)->toHaveCount(12)
        ->and($catalog[0])->toHaveKeys(['value', 'label', 'color', 'surface', 'whole']);
});

test('la dentición permanente FDI tiene 32 piezas y valida la numeración', function () {
    expect(Dentition::all())->toHaveCount(32)
        ->and(Dentition::isValid(18))->toBeTrue()
        ->and(Dentition::isValid(41))->toBeTrue()
        ->and(Dentition::isValid(19))->toBeFalse()
        ->and(Dentition::isValid(51))->toBeFalse();
});

test('los incisivos y caninos son anteriores; premolares y molares no', function () {
    expect(Dentition::isAnterior(11))->toBeTrue()
        ->and(Dentition::isAnterior(23))->toBeTrue()
        ->and(Dentition::isAnterior(14))->toBeFalse()
        ->and(Dentition::isAnterior(48))->toBeFalse();
});

test('el reparto de superficies mantiene mesial hacia la línea media', function () {
    expect(Dentition::surfaceLayout(11)['right']->value)->toBe('mesial')
        ->and(Dentition::surfaceLayout(21)['left']->value)->toBe('mesial')
        ->and(Dentition::surfaceLayout(11)['top']->value)->toBe('vestibular')
        ->and(Dentition::surfaceLayout(41)['bottom']->value)->toBe('vestibular');
});
