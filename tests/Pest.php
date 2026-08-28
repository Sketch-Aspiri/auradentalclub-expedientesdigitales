<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)->in('Unit');

/**
 * Payload válido para crear/editar un consentimiento (Sprint 5). `doctor_id` se pasa aparte
 * cuando quien actúa no es el propio doctor tratante.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function consentPayload(array $overrides = []): array
{
    return array_merge([
        'type' => 'general',
        'given_by' => 'paciente',
        'diagnosis' => 'Caries en órgano dentario 36',
        'treatment_plan' => 'Remoción de caries y restauración con resina',
        'risks_complications' => 'Sensibilidad postoperatoria, posible necesidad de endodoncia',
        'authorizes_photos_xrays' => '1',
        'patient_accepts' => '1',
    ], $overrides);
}

/**
 * Un dataURL PNG mínimo pero válido (1x1 px), para simular la salida de canvas.toDataURL().
 */
function fakeSignatureDataUrl(): string
{
    return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';
}
