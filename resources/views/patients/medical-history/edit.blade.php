@php
    $checkbox = function (string $field, string $label) use ($medicalHistory) {
        return [
            'field' => $field,
            'label' => $label,
            'checked' => old($field, $medicalHistory->{$field}),
        ];
    };
@endphp

{{-- El nombre del paciente no va en el <title> del navegador (historial, pestañas, screen-share): CLAUDE.md §5. --}}
<x-app-layout title="Historia clínica">
    <div class="max-w-3xl space-y-8">
        <a href="{{ route('patients.medical-history.show', $patient) }}"
           class="inline-flex min-h-11 items-center gap-1.5 rounded text-sm text-aura-gray-dark transition-colors motion-reduce:transition-none hover:text-aura-olive focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
            <x-icon name="chevron-left" class="h-4 w-4" />
            Volver a la historia clínica
        </a>

        <div>
            <h1 class="text-2xl font-light tracking-tight text-aura-gray-dark">Editar historia clínica</h1>
            <p class="mt-1 text-sm text-aura-gray">{{ $patient->full_name }}</p>
        </div>

        @if (session('status'))
            <p class="flex items-start gap-2 rounded-md border border-aura-olive/30 bg-aura-olive/5 px-4 py-2.5 text-sm text-aura-olive" role="status">
                <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0" />
                <span>{{ session('status') }}</span>
            </p>
        @endif

        <form method="POST" action="{{ route('patients.medical-history.update', $patient) }}" class="space-y-8">
            @csrf
            @method('PUT')

            <section class="bg-white border border-aura-gray-light rounded-lg p-6">
                <h2 class="text-sm font-semibold text-aura-gray-dark mb-4">Antecedentes patológicos</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    @foreach ([
                        $checkbox('has_diabetes', 'Diabetes'),
                        $checkbox('has_hypertension', 'Hipertensión'),
                        $checkbox('has_heart_disease', 'Cardiopatías'),
                        $checkbox('has_hiv_hepatitis', 'VIH / Hepatitis'),
                        $checkbox('has_coagulation_problems', 'Problemas de coagulación'),
                        $checkbox('has_seizures', 'Convulsiones'),
                    ] as $item)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="hidden" name="{{ $item['field'] }}" value="0">
                            <input type="checkbox" name="{{ $item['field'] }}" value="1" @checked($item['checked'])
                                   class="rounded border-aura-gray-light text-aura-olive focus:ring-aura-olive">
                            {{ $item['label'] }}
                        </label>
                    @endforeach
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="allergies" class="block text-sm text-aura-gray-dark mb-1">Alergias (medicamentos, anestesia, látex, etc.)</label>
                        <textarea id="allergies" name="allergies" rows="2"
                                  class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">{{ old('allergies', $medicalHistory->allergies) }}</textarea>
                        @error('allergies')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="current_medications" class="block text-sm text-aura-gray-dark mb-1">Medicamentos actuales</label>
                        <textarea id="current_medications" name="current_medications" rows="2"
                                  class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">{{ old('current_medications', $medicalHistory->current_medications) }}</textarea>
                        @error('current_medications')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="flex items-center gap-2 text-sm mb-2">
                            <input type="hidden" name="has_been_hospitalized_or_operated" value="0">
                            <input type="checkbox" name="has_been_hospitalized_or_operated" value="1"
                                   @checked(old('has_been_hospitalized_or_operated', $medicalHistory->has_been_hospitalized_or_operated))
                                   class="rounded border-aura-gray-light text-aura-olive focus:ring-aura-olive">
                            Hospitalización o cirugía previa
                        </label>
                        <textarea name="hospitalization_details" rows="2" placeholder="Detalles (motivo, fecha aproximada)..."
                                  class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">{{ old('hospitalization_details', $medicalHistory->hospitalization_details) }}</textarea>
                        @error('hospitalization_details')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <section class="bg-white border border-aura-gray-light rounded-lg p-6">
                <h2 class="text-sm font-semibold text-aura-gray-dark mb-4">Antecedentes no patológicos</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="oral_hygiene_times_per_day" class="block text-sm text-aura-gray-dark mb-1">
                            Higiene oral (veces al día)
                        </label>
                        <input id="oral_hygiene_times_per_day" type="number" min="0" max="20"
                               name="oral_hygiene_times_per_day"
                               value="{{ old('oral_hygiene_times_per_day', $medicalHistory->oral_hygiene_times_per_day) }}"
                               class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
                        @error('oral_hygiene_times_per_day')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-end gap-4">
                        @foreach ([$checkbox('smokes', 'Fuma'), $checkbox('drinks_alcohol', 'Consume alcohol')] as $item)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="hidden" name="{{ $item['field'] }}" value="0">
                                <input type="checkbox" name="{{ $item['field'] }}" value="1" @checked($item['checked'])
                                       class="rounded border-aura-gray-light text-aura-olive focus:ring-aura-olive">
                                {{ $item['label'] }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="bg-white border border-aura-gray-light rounded-lg p-6">
                <h2 class="text-sm font-semibold text-aura-gray-dark mb-4">Información adicional</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    @foreach ([
                        $checkbox('prolonged_bleeding_history', 'Antecedente de sangrado prolongado'),
                        $checkbox('weight_loss_products_history', 'Uso de productos para bajar de peso'),
                    ] as $item)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="hidden" name="{{ $item['field'] }}" value="0">
                            <input type="checkbox" name="{{ $item['field'] }}" value="1" @checked($item['checked'])
                                   class="rounded border-aura-gray-light text-aura-olive focus:ring-aura-olive">
                            {{ $item['label'] }}
                        </label>
                    @endforeach
                </div>

                @if ($patient->sex === 'F')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="is_pregnant" class="block text-sm text-aura-gray-dark mb-1">¿Está embarazada?</label>
                            <select id="is_pregnant" name="is_pregnant"
                                    class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
                                @php($current = old('is_pregnant', $medicalHistory->is_pregnant))
                                <option value="" @selected($current === null)>No especificado</option>
                                <option value="1" @selected($current === true || $current === '1')>Sí</option>
                                <option value="0" @selected($current === false || $current === '0')>No</option>
                            </select>
                        </div>
                        <div>
                            <label for="pregnancy_time" class="block text-sm text-aura-gray-dark mb-1">Tiempo de embarazo</label>
                            <input id="pregnancy_time" type="text" name="pregnancy_time"
                                   value="{{ old('pregnancy_time', $medicalHistory->pregnancy_time) }}"
                                   class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">
                        </div>
                    </div>
                @endif

                <div>
                    <label for="additional_health_notes" class="block text-sm text-aura-gray-dark mb-1">Notas adicionales</label>
                    <textarea id="additional_health_notes" name="additional_health_notes" rows="3"
                              class="w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">{{ old('additional_health_notes', $medicalHistory->additional_health_notes) }}</textarea>
                    @error('additional_health_notes')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <div class="flex items-center gap-3">
                <x-button type="submit" variant="primary" icon="check">
                    Guardar historia clínica
                </x-button>
                <a href="{{ route('patients.medical-history.show', $patient) }}"
                   class="inline-flex min-h-11 items-center rounded-md px-2 text-sm text-aura-gray transition-colors motion-reduce:transition-none hover:text-aura-gray-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
