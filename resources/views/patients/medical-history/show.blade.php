{{-- El nombre del paciente no va en el <title> del navegador (historial, pestañas, screen-share): CLAUDE.md §5. --}}
<x-app-layout title="Historia clínica">
    <div class="max-w-3xl space-y-8">
        <a href="{{ route('patients.show', $patient) }}"
           class="inline-flex min-h-11 items-center gap-1.5 rounded text-sm text-aura-gray-dark transition-colors motion-reduce:transition-none hover:text-aura-olive focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
            <x-icon name="chevron-left" class="h-4 w-4" />
            Volver a la ficha del paciente
        </a>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-light tracking-tight text-aura-gray-dark">Historia clínica</h1>
                <p class="mt-1 text-sm text-aura-gray">{{ $patient->full_name }}</p>
            </div>

            @if ($medicalHistory->exists)
                @can('update', $medicalHistory)
                    <x-icon-action :href="route('patients.medical-history.edit', $patient)" label="Editar historia clínica">
                        <x-icon name="pencil" />
                    </x-icon-action>
                @endcan
            @else
                @can('create', App\Models\MedicalHistory::class)
                    <a href="{{ route('patients.medical-history.edit', $patient) }}"
                       class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-md bg-aura-olive px-4 py-2 text-sm font-medium text-white transition-opacity motion-reduce:transition-none hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive focus-visible:ring-offset-2 focus-visible:ring-offset-aura-cream">
                        <x-icon name="pencil" class="h-4 w-4" />
                        Capturar historia clínica
                    </a>
                @endcan
            @endif
        </div>

        @if (session('status'))
            <p class="flex items-start gap-2 rounded-md border border-aura-olive/30 bg-aura-olive/5 px-4 py-2.5 text-sm text-aura-olive" role="status">
                <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0" />
                <span>{{ session('status') }}</span>
            </p>
        @endif

        @if (! $medicalHistory->exists)
            <div class="overflow-hidden rounded-lg border border-aura-gray-light bg-white">
                <div class="px-4 py-12 text-center">
                    <p class="text-sm text-aura-gray">Este paciente aún no tiene historia clínica registrada.</p>
                    @can('create', App\Models\MedicalHistory::class)
                        <a href="{{ route('patients.medical-history.edit', $patient) }}"
                           class="mt-3 inline-flex min-h-11 items-center gap-1.5 rounded-md px-2 py-1.5 text-sm font-medium text-aura-olive hover:bg-aura-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                            <x-icon name="pencil" class="h-4 w-4" />
                            Capturar historia clínica
                        </a>
                    @endcan
                </div>
            </div>
        @else
            <div class="space-y-6">
                <section aria-labelledby="pathological-heading" class="rounded-lg border border-aura-gray-light bg-white p-6">
                    <h2 id="pathological-heading" class="mb-4 flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-aura-gray-dark">
                        <span class="h-3 w-0.5 bg-aura-olive" aria-hidden="true"></span>
                        Antecedentes patológicos
                    </h2>

                    <x-condition-list
                        :items="[
                            ['label' => 'Diabetes', 'present' => $medicalHistory->has_diabetes],
                            ['label' => 'Hipertensión', 'present' => $medicalHistory->has_hypertension],
                            ['label' => 'Cardiopatías', 'present' => $medicalHistory->has_heart_disease],
                            ['label' => 'VIH / Hepatitis', 'present' => $medicalHistory->has_hiv_hepatitis],
                            ['label' => 'Problemas de coagulación', 'present' => $medicalHistory->has_coagulation_problems],
                            ['label' => 'Convulsiones', 'present' => $medicalHistory->has_seizures],
                        ]"
                        empty-message="Sin antecedentes patológicos registrados."
                    />

                    <dl class="mt-5 space-y-4 border-t border-aura-gray-light pt-5 text-sm">
                        <div>
                            <dt class="mb-1 text-xs text-aura-gray">Alergias (medicamentos, anestesia, látex, etc.)</dt>
                            <dd class="text-aura-gray-dark">{{ $medicalHistory->allergies ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-aura-gray">Medicamentos actuales</dt>
                            <dd class="text-aura-gray-dark">{{ $medicalHistory->current_medications ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-aura-gray">Hospitalización o cirugía previa</dt>
                            <dd class="text-aura-gray-dark">
                                {{ $medicalHistory->has_been_hospitalized_or_operated ? 'Sí' : 'No' }}
                                @if ($medicalHistory->has_been_hospitalized_or_operated && $medicalHistory->hospitalization_details)
                                    — {{ $medicalHistory->hospitalization_details }}
                                @endif
                            </dd>
                        </div>
                    </dl>
                </section>

                <section aria-labelledby="general-health-heading" class="rounded-lg border border-aura-gray-light bg-white p-6">
                    <h2 id="general-health-heading" class="mb-4 flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-aura-gray-dark">
                        <span class="h-3 w-0.5 bg-aura-olive" aria-hidden="true"></span>
                        Estado de salud general
                    </h2>
                    <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="mb-1 text-xs text-aura-gray">¿Cómo describiría su salud?</dt>
                            <dd class="text-aura-gray-dark">{{ $medicalHistory->general_health_rating?->label() ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-aura-gray">Último examen médico</dt>
                            <dd class="text-aura-gray-dark">{{ $medicalHistory->last_medical_exam ?: '—' }}</dd>
                        </div>
                    </dl>
                </section>

                <section aria-labelledby="non-pathological-heading" class="rounded-lg border border-aura-gray-light bg-white p-6">
                    <h2 id="non-pathological-heading" class="mb-4 flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-aura-gray-dark">
                        <span class="h-3 w-0.5 bg-aura-olive" aria-hidden="true"></span>
                        Antecedentes no patológicos
                    </h2>

                    <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-3">
                        <div>
                            <dt class="mb-1 text-xs text-aura-gray">Higiene oral (veces al día)</dt>
                            <dd class="text-aura-gray-dark">{{ $medicalHistory->oral_hygiene_times_per_day ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-aura-gray">Fuma</dt>
                            <dd class="text-aura-gray-dark">{{ $medicalHistory->smokes ? 'Sí' : 'No' }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-aura-gray">Consume alcohol</dt>
                            <dd class="text-aura-gray-dark">{{ $medicalHistory->drinks_alcohol ? 'Sí' : 'No' }}</dd>
                        </div>
                    </dl>
                </section>

                <section aria-labelledby="additional-heading" class="rounded-lg border border-aura-gray-light bg-white p-6">
                    <h2 id="additional-heading" class="mb-4 flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-aura-gray-dark">
                        <span class="h-3 w-0.5 bg-aura-olive" aria-hidden="true"></span>
                        Información adicional
                    </h2>

                    <x-condition-list
                        :items="[
                            ['label' => 'Antecedente de sangrado prolongado', 'present' => $medicalHistory->prolonged_bleeding_history],
                            ['label' => 'Uso de productos para bajar de peso', 'present' => $medicalHistory->weight_loss_products_history],
                        ]"
                        empty-message="Sin antecedentes adicionales registrados."
                    />

                    @if ($patient->sex === 'F')
                        <dl class="mt-5 grid grid-cols-1 gap-4 border-t border-aura-gray-light pt-5 text-sm sm:grid-cols-2">
                            <div>
                                <dt class="mb-1 text-xs text-aura-gray">¿Está embarazada?</dt>
                                <dd class="text-aura-gray-dark">
                                    @if (is_null($medicalHistory->is_pregnant))
                                        No especificado
                                    @else
                                        {{ $medicalHistory->is_pregnant ? 'Sí' : 'No' }}
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="mb-1 text-xs text-aura-gray">Tiempo de embarazo</dt>
                                <dd class="text-aura-gray-dark">{{ $medicalHistory->pregnancy_time ?: '—' }}</dd>
                            </div>
                        </dl>
                    @endif

                    <div class="mt-5 border-t border-aura-gray-light pt-5 text-sm">
                        <p class="mb-1 text-xs text-aura-gray">Notas adicionales</p>
                        <p class="text-aura-gray-dark">{{ $medicalHistory->additional_health_notes ?: '—' }}</p>
                    </div>
                </section>
            </div>
        @endif
    </div>
</x-app-layout>
