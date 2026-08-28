@php
    $field = fn (?string $value) => filled($value) ? $value : '—';
@endphp

{{-- El nombre del paciente no va en el <title> del navegador (historial, pestañas, screen-share): CLAUDE.md §5. --}}
<x-app-layout title="Consulta">
    <div
        class="max-w-3xl"
        x-data="{
            confirmDelete: false,
            deleting: false,
            closeConfirmDelete() {
                this.confirmDelete = false;
                this.$nextTick(() => this.$refs.deleteTrigger?.focus());
            },
        }"
    >
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-lg font-medium">Consulta del {{ $consultation->consultation_date->format('d/m/Y') }}</h1>
                <p class="text-sm text-aura-gray">
                    {{ $patient->full_name }} · Dr(a). {{ $consultation->doctor?->name ?? '—' }}
                </p>
            </div>

            <div class="flex items-center gap-1" role="group" aria-label="Acciones de la consulta">
                @can('update', $consultation)
                    <x-icon-action :href="route('consultations.edit', $consultation)" label="Editar consulta">
                        <x-icon name="pencil" />
                    </x-icon-action>
                @endcan

                @can('delete', $consultation)
                    <x-icon-action
                        label="Eliminar consulta"
                        tone="danger"
                        x-ref="deleteTrigger"
                        @click="confirmDelete = true"
                    >
                        <x-icon name="trash" />
                    </x-icon-action>
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

        {{-- Confirmación de eliminación: modal propio de la UI, nunca confirm() del navegador
             (antes usaba onsubmit="return confirm(...)" — corregido: viola la regla dura de
             CLAUDE.md §5 / .claude/agents/ux-ui-designer.md). Mismo patrón que patients/show.blade.php. --}}
        @can('delete', $consultation)
            <form method="POST" action="{{ route('consultations.destroy', $consultation) }}" x-ref="deleteForm" class="hidden">
                @csrf
                @method('DELETE')
            </form>

            <x-confirm-modal show="confirmDelete" title-id="delete-consultation-title" on-close="closeConfirmDelete()">
                <h2 id="delete-consultation-title" class="text-base font-medium text-aura-gray-dark">
                    Eliminar consulta
                </h2>
                <p class="mt-2 text-sm text-aura-gray">
                    Se eliminará la consulta del
                    <span class="font-medium text-aura-gray-dark">{{ $consultation->consultation_date->format('d/m/Y') }}</span>.
                    Quedará archivada y se podrá restaurar desde la lista de consultas del paciente.
                </p>
                <div class="mt-5 flex justify-end gap-2">
                    <x-button type="button" variant="secondary" x-ref="confirmModalCancel"
                              @click="closeConfirmDelete()"
                              @keydown.tab.prevent="$refs.confirmModalConfirm.focus()">
                        Cancelar
                    </x-button>
                    <x-button type="button" variant="danger" x-ref="confirmModalConfirm"
                              alpine-loading="deleting" loading-text="Eliminando…"
                              @click="deleting = true; $refs.deleteForm.requestSubmit()"
                              @keydown.tab.prevent="$refs.confirmModalCancel.focus()">
                        Eliminar
                    </x-button>
                </div>
            </x-confirm-modal>
        @endcan
    </div>
</x-app-layout>
