@php
    $field = fn (?string $value) => filled($value) ? $value : '—';
@endphp

{{-- El nombre del paciente no va en el <title> del navegador (historial, pestañas, screen-share): CLAUDE.md §5. --}}
<x-app-layout title="Consulta">
    <div class="max-w-3xl">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-lg font-medium">Consulta del {{ $consultation->consultation_date->format('d/m/Y') }}</h1>
                <p class="text-sm text-aura-gray">
                    {{ $patient->full_name }} · Dr(a). {{ $consultation->doctor?->name ?? '—' }}
                </p>
            </div>

            <div class="flex items-center gap-3 text-sm">
                @can('update', $consultation)
                    <a href="{{ route('consultations.edit', $consultation) }}" class="text-aura-olive hover:underline">Editar</a>
                @endcan

                @can('delete', $consultation)
                    <form method="POST" action="{{ route('consultations.destroy', $consultation) }}"
                          onsubmit="return confirm('¿Eliminar esta consulta? Quedará archivada y se podrá restaurar desde la lista de consultas del paciente.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                    </form>
                @endcan
            </div>
        </div>

        @if (session('status'))
            <p class="mb-4 text-sm text-aura-olive">{{ session('status') }}</p>
        @endif

        <div class="space-y-6 text-sm">
            <section class="bg-white border border-aura-gray-light rounded-lg p-6">
                <h2 class="text-xs uppercase tracking-wide text-aura-gray mb-4">Signos vitales</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <p class="text-aura-gray text-xs mb-1">Presión arterial</p>
                        <p>{{ $field($consultation->blood_pressure) }}</p>
                    </div>
                    <div>
                        <p class="text-aura-gray text-xs mb-1">Frecuencia cardiaca</p>
                        <p>{{ $consultation->heart_rate ? $consultation->heart_rate.' lpm' : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-aura-gray text-xs mb-1">Temperatura</p>
                        <p>{{ $consultation->temperature ? $consultation->temperature.' °C' : '—' }}</p>
                    </div>
                </div>
            </section>

            <section class="bg-white border border-aura-gray-light rounded-lg p-6 space-y-4">
                <h2 class="text-xs uppercase tracking-wide text-aura-gray">Exploración bucal</h2>
                <div>
                    <p class="text-aura-gray text-xs mb-1">Nivel de higiene oral</p>
                    <p>{{ $consultation->oral_hygiene_level?->label() ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-aura-gray text-xs mb-1">Tejidos blandos</p>
                    <p class="whitespace-pre-line">{{ $field($consultation->soft_tissues_notes) }}</p>
                </div>
                <div>
                    <p class="text-aura-gray text-xs mb-1">Encías y periodonto</p>
                    <p class="whitespace-pre-line">{{ $field($consultation->gums_periodontium_notes) }}</p>
                </div>
            </section>

            <section class="bg-white border border-aura-gray-light rounded-lg p-6 space-y-4">
                <h2 class="text-xs uppercase tracking-wide text-aura-gray">Diagnóstico y plan</h2>
                @foreach ([
                    ['Motivo de consulta', $consultation->chief_complaint],
                    ['Diagnóstico clínico', $consultation->clinical_diagnosis],
                    ['Plan de tratamiento', $consultation->treatment_plan],
                    ['Pronóstico', $consultation->prognosis],
                    ['Riesgos y complicaciones', $consultation->risks_and_complications],
                    ['Alternativas de tratamiento', $consultation->treatment_alternatives],
                ] as [$label, $value])
                    <div>
                        <p class="text-aura-gray text-xs mb-1">{{ $label }}</p>
                        <p class="whitespace-pre-line">{{ $field($value) }}</p>
                    </div>
                @endforeach
            </section>
        </div>

        <a href="{{ route('patients.consultations.index', $patient) }}" class="inline-block mt-6 text-sm text-aura-gray hover:text-aura-gray-dark">
            &larr; Volver al historial de consultas
        </a>
    </div>
</x-app-layout>
