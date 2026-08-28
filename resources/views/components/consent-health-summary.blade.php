{{--
    Sección "Diagnóstico" de la hoja de consentimiento: el cuestionario de salud del paciente.
    Recibe un arreglo con la misma forma que App\Models\Consent::snapshotHealthFrom() — sirve
    tanto para la vista previa en vivo (formulario) como para la copia fija (detalle / impresión).

    Props:
    - data: array<string,mixed> | null
    - variant: 'screen' (default) | 'print'
--}}
@props(['data' => [], 'variant' => 'screen'])

@php
    use App\Enums\GeneralHealthRating;

    $data = $data ?: [];
    $yesNo = fn ($v) => $v === null ? '—' : ($v ? 'Sí' : 'No');
    $ratingValue = $data['general_health_rating'] ?? null;
    $rating = $ratingValue ? GeneralHealthRating::tryFrom($ratingValue)?->label() : null;

    // "¿toma algún medicamento? ¿cuál?" y "¿es alérgico? ¿cuál?" son un Sí/No con un detalle.
    // Si el campo trae un texto de negación ("ninguno", "no", "n/a"), se toma como "No".
    $negations = ['no', 'ninguno', 'ninguna', 'ningun', 'n/a', 'na', '-', 'sin'];
    $yesNoDetail = function ($v) use ($negations) {
        $text = trim((string) $v);
        if ($text === '') {
            return 'No';
        }
        return in_array(mb_strtolower($text), $negations, true) ? 'No' : 'Sí — '.$text;
    };

    $rows = [
        ['¿Cómo describiría su salud?', $rating ?: '—'],
        ['¿Cuándo fue su último examen médico?', filled($data['last_medical_exam'] ?? null) ? $data['last_medical_exam'] : '—'],
        ['¿Hemorragia prolongada tras una cirugía?', $yesNo($data['prolonged_bleeding_history'] ?? null)],
        ['¿Ha tomado productos para adelgazar?', $yesNo($data['weight_loss_products_history'] ?? null)],
        ['¿Toma algún medicamento? ¿Cuál?', $yesNoDetail($data['current_medications'] ?? null)],
        ['¿Es alérgico o ha reaccionado a un medicamento? ¿Cuál?', $yesNoDetail($data['allergies'] ?? null)],
        ['Si es mujer, ¿está embarazada? Tiempo', ($data['is_pregnant'] ?? null) ? ('Sí'.(filled($data['pregnancy_time'] ?? null) ? ' — '.$data['pregnancy_time'] : '')) : (($data['is_pregnant'] ?? null) === false ? 'No' : '—')],
        ['¿Otra información sobre su salud?', filled($data['additional_health_notes'] ?? null) ? $data['additional_health_notes'] : '—'],
    ];
@endphp

@if ($variant === 'print')
    <dl class="health-print">
        @foreach ($rows as [$q, $a])
            <div><dt>{{ $q }}</dt><dd>{{ $a }}</dd></div>
        @endforeach
    </dl>
@else
    <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
        @foreach ($rows as [$q, $a])
            <div>
                <dt class="text-xs text-aura-gray">{{ $q }}</dt>
                <dd class="text-aura-gray-dark whitespace-pre-line">{{ $a }}</dd>
            </div>
        @endforeach
    </dl>
@endif
