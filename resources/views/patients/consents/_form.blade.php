@php
    use App\Enums\ConsentGiver;
    use App\Models\Consent;

    $consent = $consent ?? null;
    $currentType = old('type', $consent?->type?->value);
    $currentGiver = old('given_by', $consent?->given_by?->value);

    $textareas = [
        ['field' => 'diagnosis', 'label' => 'Diagnóstico', 'rows' => 2, 'required' => false],
        ['field' => 'treatment_plan', 'label' => 'Plan de tratamiento', 'rows' => 3, 'required' => true],
        ['field' => 'prognosis', 'label' => 'Pronóstico', 'rows' => 2, 'required' => false],
        ['field' => 'risks_complications', 'label' => 'Riesgos y complicaciones posibles', 'rows' => 3, 'required' => true],
        ['field' => 'management_alternatives', 'label' => 'Alternativas de manejo', 'rows' => 2, 'required' => false],
    ];

    // Vista previa en vivo de las respuestas de salud que se copiarán al guardar (una vez creado
    // el consentimiento la copia queda fija; editar la historia clínica después no la cambia).
    $healthPreview = Consent::snapshotHealthFrom($patient->medicalHistory);
@endphp

<section class="bg-white border border-aura-gray-light rounded-lg p-6 space-y-4"
         x-data="{ giver: @js($currentGiver ?? ConsentGiver::Patient->value) }">
    <h2 class="text-sm font-semibold text-aura-gray-dark">Datos del consentimiento</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="type" class="block text-sm text-aura-gray-dark mb-1">Tipo <span class="text-red-600">*</span></label>
            <select id="type" name="type" required
                    class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
                <option value="">Selecciona...</option>
                @foreach ($types as $type)
                    <option value="{{ $type['value'] }}" @selected($currentType === $type['value'])>{{ $type['label'] }}</option>
                @endforeach
            </select>
            @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        @if ($doctors->isNotEmpty())
            <div>
                <label for="doctor_id" class="block text-sm text-aura-gray-dark mb-1">Cirujano dentista tratante <span class="text-red-600">*</span></label>
                <select id="doctor_id" name="doctor_id" required
                        class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
                    <option value="">Selecciona...</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected((string) old('doctor_id', $consent?->doctor_id) === (string) $doctor->id)>
                            {{ $doctor->name }}
                        </option>
                    @endforeach
                </select>
                @error('doctor_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        @endif

        <div>
            <label for="given_by" class="block text-sm text-aura-gray-dark mb-1">Quién recibe la información y otorga el consentimiento <span class="text-red-600">*</span></label>
            <select id="given_by" name="given_by" required x-model="giver"
                    class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
                <option value="">Selecciona...</option>
                @foreach ($givers as $giver)
                    <option value="{{ $giver['value'] }}" @selected($currentGiver === $giver['value'])>{{ $giver['label'] }}</option>
                @endforeach
            </select>
            @error('given_by') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div x-show="giver !== @js(ConsentGiver::Patient->value)" x-cloak>
            <label for="relationship" class="block text-sm text-aura-gray-dark mb-1">Parentesco con el paciente</label>
            <input id="relationship" type="text" name="relationship" maxlength="255"
                   value="{{ old('relationship', $consent?->relationship) }}"
                   class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
            @error('relationship') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>
</section>

<section class="bg-white border border-aura-gray-light rounded-lg p-6 space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold text-aura-gray-dark">Datos de salud del paciente</h2>
            <p class="mt-1 text-xs text-aura-gray">
                Se toman de la historia clínica y se guardan como copia fija en este consentimiento.
            </p>
        </div>
        @can('viewAny', App\Models\MedicalHistory::class)
            <a href="{{ route('patients.medical-history.edit', $patient) }}"
               class="shrink-0 text-xs font-medium text-aura-olive hover:underline">Editar historia clínica</a>
        @endcan
    </div>

    @if ($patient->medicalHistory)
        <x-consent-health-summary :data="$healthPreview" />
    @else
        <p class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
            Este paciente aún no tiene historia clínica registrada. El consentimiento se puede
            crear igual, pero la sección de salud saldrá en blanco.
        </p>
    @endif
</section>

<section class="bg-white border border-aura-gray-light rounded-lg p-6 space-y-4">
    <h2 class="text-sm font-semibold text-aura-gray-dark">Diagnóstico y plan</h2>

    @foreach ($textareas as $item)
        <div>
            <label for="{{ $item['field'] }}" class="block text-sm text-aura-gray-dark mb-1">
                {{ $item['label'] }}@if ($item['required']) <span class="text-red-600">*</span>@endif
            </label>
            <textarea id="{{ $item['field'] }}" name="{{ $item['field'] }}" rows="{{ $item['rows'] }}" @required($item['required'])
                      class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">{{ old($item['field'], $consent?->{$item['field']}) }}</textarea>
            @error($item['field']) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    @endforeach

    <div class="space-y-2 pt-2">
        <label class="flex items-start gap-2 text-sm text-aura-gray-dark">
            <input type="hidden" name="authorizes_photos_xrays" value="0">
            <input type="checkbox" name="authorizes_photos_xrays" value="1" @checked(old('authorizes_photos_xrays', $consent?->authorizes_photos_xrays))
                   class="mt-0.5 rounded border-aura-gray-light text-aura-olive focus:ring-aura-olive">
            Autoriza la toma de fotografías y radiografías con fines clínicos.
        </label>
        <label class="flex items-start gap-2 text-sm text-aura-gray-dark">
            <input type="hidden" name="patient_accepts" value="0">
            <input type="checkbox" name="patient_accepts" value="1" @checked(old('patient_accepts', $consent?->patient_accepts))
                   class="mt-0.5 rounded border-aura-gray-light text-aura-olive focus:ring-aura-olive">
            Acepta el tratamiento propuesto tras recibir la información anterior.
        </label>
    </div>
</section>

<p class="text-xs text-aura-gray">
    Los procedimientos y costos se registran en la Hoja de evolución y control y se vinculan a este consentimiento.
</p>
