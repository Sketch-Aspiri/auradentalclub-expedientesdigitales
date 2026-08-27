@php
    use App\Enums\OralHygieneLevel;

    $consultation = $consultation ?? null;
    $currentDoctorId = old('doctor_id', $consultation?->doctor_id);
    $currentHygiene = old('oral_hygiene_level', $consultation?->oral_hygiene_level?->value);
    $dateValue = old('consultation_date', $consultation?->consultation_date?->format('Y-m-d') ?? now()->format('Y-m-d'));

    $textarea = function (string $field, string $label, string $rows = '3', bool $required = false) use ($consultation) {
        return [
            'field' => $field,
            'label' => $label,
            'rows' => $rows,
            'required' => $required,
            'value' => old($field, $consultation?->{$field}),
        ];
    };
@endphp

<section class="bg-white border border-aura-gray-light rounded-lg p-6 space-y-4">
    <h2 class="text-sm font-semibold text-aura-gray-dark">Datos de la consulta</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="consultation_date" class="block text-sm text-aura-gray-dark mb-1">Fecha de la consulta</label>
            <input id="consultation_date" type="date" name="consultation_date" value="{{ $dateValue }}"
                   max="{{ now()->format('Y-m-d') }}" required
                   class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
            @error('consultation_date')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if ($doctors->isNotEmpty())
            <div>
                <label for="doctor_id" class="block text-sm text-aura-gray-dark mb-1">Doctor tratante</label>
                <select id="doctor_id" name="doctor_id" required
                        class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
                    <option value="">Selecciona...</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected((string) $currentDoctorId === (string) $doctor->id)>
                            {{ $doctor->name }}
                        </option>
                    @endforeach
                </select>
                @error('doctor_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endif
    </div>
</section>

<section class="bg-white border border-aura-gray-light rounded-lg p-6 space-y-4">
    <h2 class="text-sm font-semibold text-aura-gray-dark">Signos vitales</h2>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label for="blood_pressure" class="block text-sm text-aura-gray-dark mb-1">Presión arterial</label>
            <input id="blood_pressure" type="text" name="blood_pressure" placeholder="120/80"
                   value="{{ old('blood_pressure', $consultation?->blood_pressure) }}"
                   class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
            @error('blood_pressure')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="heart_rate" class="block text-sm text-aura-gray-dark mb-1">Frecuencia cardiaca (lpm)</label>
            <input id="heart_rate" type="number" name="heart_rate" min="20" max="300"
                   value="{{ old('heart_rate', $consultation?->heart_rate) }}"
                   class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
            @error('heart_rate')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="temperature" class="block text-sm text-aura-gray-dark mb-1">Temperatura (°C)</label>
            <input id="temperature" type="number" step="0.1" name="temperature" min="30" max="45"
                   value="{{ old('temperature', $consultation?->temperature) }}"
                   class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
            @error('temperature')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</section>

<section class="bg-white border border-aura-gray-light rounded-lg p-6 space-y-4">
    <h2 class="text-sm font-semibold text-aura-gray-dark">Exploración bucal</h2>

    <div>
        <label for="oral_hygiene_level" class="block text-sm text-aura-gray-dark mb-1">Nivel de higiene oral</label>
        <select id="oral_hygiene_level" name="oral_hygiene_level"
                class="w-full sm:w-1/3 rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
            <option value="">No especificado</option>
            @foreach (OralHygieneLevel::cases() as $level)
                <option value="{{ $level->value }}" @selected($currentHygiene === $level->value)>{{ $level->label() }}</option>
            @endforeach
        </select>
        @error('oral_hygiene_level')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @foreach ([
        $textarea('soft_tissues_notes', 'Tejidos blandos', '2'),
        $textarea('gums_periodontium_notes', 'Encías y periodonto', '2'),
    ] as $item)
        <div>
            <label for="{{ $item['field'] }}" class="block text-sm text-aura-gray-dark mb-1">{{ $item['label'] }}</label>
            <textarea id="{{ $item['field'] }}" name="{{ $item['field'] }}" rows="{{ $item['rows'] }}"
                      class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">{{ $item['value'] }}</textarea>
            @error($item['field'])
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endforeach
</section>

<section class="bg-white border border-aura-gray-light rounded-lg p-6 space-y-4">
    <h2 class="text-sm font-semibold text-aura-gray-dark">Diagnóstico y plan</h2>

    @foreach ([
        $textarea('chief_complaint', 'Motivo de consulta', '2', true),
        $textarea('clinical_diagnosis', 'Diagnóstico clínico', '3', true),
        $textarea('treatment_plan', 'Plan de tratamiento', '3'),
        $textarea('prognosis', 'Pronóstico', '2'),
        $textarea('risks_and_complications', 'Riesgos y complicaciones', '2'),
        $textarea('treatment_alternatives', 'Alternativas de tratamiento', '2'),
    ] as $item)
        <div>
            <label for="{{ $item['field'] }}" class="block text-sm text-aura-gray-dark mb-1">
                {{ $item['label'] }}@if ($item['required']) <span class="text-red-600">*</span>@endif
            </label>
            <textarea id="{{ $item['field'] }}" name="{{ $item['field'] }}" rows="{{ $item['rows'] }}" @required($item['required'])
                      class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">{{ $item['value'] }}</textarea>
            @error($item['field'])
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endforeach
</section>
