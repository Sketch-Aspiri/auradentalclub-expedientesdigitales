@php
    $field = fn (?string $value) => filled($value) ? $value : '—';
    $status = $consent->status();
@endphp

{{-- El nombre del paciente no va en el <title> del navegador (historial, pestañas, screen-share): CLAUDE.md §5. --}}
<x-app-layout title="Consentimiento">
    <div
        class="max-w-3xl"
        x-data="{
            confirmDelete: false,
            deleting: false,
            confirmVoid: false,
            closeConfirmDelete() { this.confirmDelete = false; this.$nextTick(() => this.$refs.deleteTrigger?.focus()); },
            closeConfirmVoid() { this.confirmVoid = false; this.$nextTick(() => this.$refs.voidTrigger?.focus()); },
        }"
    >
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="flex items-center gap-2 text-lg font-medium">
                    Consentimiento — {{ $consent->type->label() }}
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $status->badgeClasses() }}">
                        {{ $status->label() }}
                    </span>
                </h1>
                <p class="text-sm text-aura-gray">
                    {{ $patient->full_name }} · Dr(a). {{ $consent->doctor?->name ?? '—' }} · {{ $consent->created_at->format('d/m/Y') }}
                </p>
            </div>

            <div class="flex items-center gap-1" role="group" aria-label="Acciones del consentimiento">
                <x-icon-action :href="route('consents.print', $consent)" label="Imprimir consentimiento" target="_blank" rel="noopener">
                    <x-icon name="printer" />
                </x-icon-action>

                @can('update', $consent)
                    <x-icon-action :href="route('consents.edit', $consent)" label="Editar consentimiento">
                        <x-icon name="pencil" />
                    </x-icon-action>
                @endcan

                @can('sign', $consent)
                    <x-icon-action :href="route('consents.sign', $consent)" label="Firmar consentimiento">
                        <x-icon name="signature" />
                    </x-icon-action>
                @endcan

                @can('void', $consent)
                    <x-icon-action label="Anular consentimiento" tone="danger" x-ref="voidTrigger" @click="confirmVoid = true">
                        <x-icon name="x-circle" />
                    </x-icon-action>
                @endcan

                @can('delete', $consent)
                    <x-icon-action label="Eliminar consentimiento" tone="danger" x-ref="deleteTrigger" @click="confirmDelete = true">
                        <x-icon name="trash" />
                    </x-icon-action>
                @endcan
            </div>
        </div>

        @if (session('status'))
            <p class="mb-4 text-sm text-aura-olive">{{ session('status') }}</p>
        @endif

        @if ($consent->isVoided())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <p class="font-medium">Consentimiento anulado</p>
                <p class="mt-1">
                    {{ $consent->voided_at->format('d/m/Y H:i') }}
                    @if ($consent->voidedBy) · por {{ $consent->voidedBy->name }} @endif
                </p>
                <p class="mt-2 whitespace-pre-line">{{ $consent->void_reason }}</p>
            </div>
        @endif

        <div class="space-y-6 text-sm">
            <section class="bg-white border border-aura-gray-light rounded-lg p-6 space-y-4">
                <h2 class="text-xs uppercase tracking-wide text-aura-gray">Datos del acto autorizado</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-aura-gray text-xs mb-1">Otorgado por</p>
                        <p>{{ $consent->given_by->label() }}{{ $consent->relationship ? ' ('.$consent->relationship.')' : '' }}</p>
                    </div>
                    <div>
                        <p class="text-aura-gray text-xs mb-1">Autoriza fotografías / radiografías</p>
                        <p>{{ $consent->authorizes_photos_xrays ? 'Sí' : 'No' }}</p>
                    </div>
                    <div>
                        <p class="text-aura-gray text-xs mb-1">Acepta el tratamiento</p>
                        <p>{{ $consent->patient_accepts ? 'Sí' : 'No' }}</p>
                    </div>
                </div>
                @foreach ([
                    ['Diagnóstico', $consent->diagnosis],
                    ['Plan de tratamiento', $consent->treatment_plan],
                    ['Pronóstico', $consent->prognosis],
                    ['Riesgos y complicaciones posibles', $consent->risks_complications],
                    ['Alternativas de manejo', $consent->management_alternatives],
                ] as [$label, $value])
                    <div>
                        <p class="text-aura-gray text-xs mb-1">{{ $label }}</p>
                        <p class="whitespace-pre-line">{{ $field($value) }}</p>
                    </div>
                @endforeach
            </section>

            <section class="bg-white border border-aura-gray-light rounded-lg p-6">
                <h2 class="text-xs uppercase tracking-wide text-aura-gray mb-4">
                    Datos de salud del paciente
                    <span class="ml-1 normal-case text-aura-gray">(copia al momento del consentimiento)</span>
                </h2>
                @if (filled($consent->health_snapshot))
                    <x-consent-health-summary :data="$consent->health_snapshot" />
                @else
                    <p class="text-sm text-aura-gray">No se registró historia clínica del paciente al crear este consentimiento.</p>
                @endif
            </section>

            @if ($consent->isSigned() || $consent->isVoided())
                <section class="bg-white border border-aura-gray-light rounded-lg p-6">
                    <h2 class="text-xs uppercase tracking-wide text-aura-gray mb-4">
                        Firmas · {{ $consent->signed_at?->format('d/m/Y H:i') }}
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @foreach ([
                            'patient' => ['Paciente / quien otorga', $consent->patient->full_name],
                            'doctor' => ['Médico', $consent->doctor?->name],
                            'witness1' => ['Testigo 1', $consent->witness1_name],
                            'witness2' => ['Testigo 2', $consent->witness2_name],
                        ] as $party => [$role, $name])
                            @if ($consent->signaturePaths()[$party])
                                <div>
                                    <img src="{{ route('consents.signature', [$consent, $party]) }}" alt="Firma de {{ $role }}"
                                         class="h-24 w-full rounded border border-aura-gray-light bg-white object-contain">
                                    <p class="mt-1 text-xs text-aura-gray">{{ $role }}@if ($name) · {{ $name }} @endif</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <a href="{{ route('patients.consents.index', $patient) }}" class="inline-block mt-6 text-sm text-aura-gray hover:text-aura-gray-dark">
            &larr; Volver a los consentimientos
        </a>

        @can('delete', $consent)
            <form method="POST" action="{{ route('consents.destroy', $consent) }}" x-ref="deleteForm" class="hidden">
                @csrf
                @method('DELETE')
            </form>

            <x-confirm-modal show="confirmDelete" title-id="delete-consent-title" on-close="closeConfirmDelete()">
                <h2 id="delete-consent-title" class="text-base font-medium text-aura-gray-dark">Eliminar consentimiento</h2>
                <p class="mt-2 text-sm text-aura-gray">
                    Se archivará este consentimiento en borrador. Se podrá restaurar desde la lista de consentimientos del paciente.
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

        @can('void', $consent)
            <x-confirm-modal show="confirmVoid" title-id="void-consent-title" on-close="closeConfirmVoid()">
                <h2 id="void-consent-title" class="text-base font-medium text-aura-gray-dark">Anular consentimiento</h2>
                <p class="mt-2 text-sm text-aura-gray">
                    Un consentimiento firmado no se edita: se anula y queda en el expediente. Para corregirlo, crea uno nuevo.
                </p>

                <form method="POST" action="{{ route('consents.void', $consent) }}" class="mt-4">
                    @csrf
                    @method('PUT')

                    <label for="void-reason-input" class="block text-sm text-aura-gray-dark">
                        Motivo de la anulación <span class="text-red-600">*</span>
                    </label>
                    <textarea id="void-reason-input" name="void_reason" rows="3" maxlength="2000" required
                              class="mt-1 w-full rounded border border-aura-gray-light px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-aura-olive">{{ old('void_reason') }}</textarea>
                    @error('void_reason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                    <div class="mt-5 flex justify-end gap-2">
                        <x-button type="button" variant="secondary" x-ref="confirmModalCancel"
                                  @click="closeConfirmVoid()"
                                  @keydown.tab.prevent="$refs.confirmModalConfirm.focus()">
                            Cancelar
                        </x-button>
                        <x-button type="submit" variant="danger" x-ref="confirmModalConfirm" icon="x-circle"
                                  @keydown.tab.prevent="$refs.confirmModalCancel.focus()">
                            Anular consentimiento
                        </x-button>
                    </div>
                </form>
            </x-confirm-modal>
        @endcan
    </div>
</x-app-layout>
