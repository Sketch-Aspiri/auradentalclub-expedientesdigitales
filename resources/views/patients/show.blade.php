{{-- El nombre del paciente no va en el <title> del navegador (historial, pestañas, screen-share): CLAUDE.md §5. --}}
<x-app-layout title="Detalle del paciente">
    <div
        class="space-y-8"
        x-data="{
            confirmDelete: false,
            deleting: false,
            closeConfirmDelete() {
                this.confirmDelete = false;
                this.$nextTick(() => this.$refs.deleteTrigger?.focus());
            },
        }"
    >
        <a href="{{ route('patients.index') }}"
           class="inline-flex min-h-11 items-center gap-1.5 rounded text-sm text-aura-gray-dark transition-colors motion-reduce:transition-none hover:text-aura-olive focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
            <x-icon name="chevron-left" class="h-4 w-4" />
            Pacientes
        </a>

        {{-- Encabezado del expediente --}}
        <div class="flex flex-col gap-6 border-b border-aura-gray-light pb-6 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-4">
                <x-patient-avatar :patient="$patient" size="lg" />
                <div class="min-w-0">
                    <h1 class="text-2xl font-light tracking-tight text-aura-gray-dark">{{ $patient->full_name }}</h1>
                    <p class="mt-1 text-sm text-aura-gray">
                        {{ $patient->age }} años · {{ $patient->sex === 'M' ? 'Masculino' : 'Femenino' }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-1" role="group" aria-label="Secciones y acciones del expediente">
                @can('viewAny', App\Models\MedicalHistory::class)
                    <x-icon-action :href="route('patients.medical-history.show', $patient)" label="Historia clínica">
                        <x-icon name="clipboard" />
                    </x-icon-action>
                @endcan

                @can('viewAny', App\Models\Consultation::class)
                    <x-icon-action :href="route('patients.consultations.index', $patient)" label="Consultas">
                        <x-icon name="calendar" />
                    </x-icon-action>
                @endcan

                @can('viewAny', App\Models\OdontogramRecord::class)
                    <x-icon-action :href="route('patients.odontogram', $patient)" label="Odontograma">
                        <x-icon name="tooth" />
                    </x-icon-action>
                @endcan

                @canany(['update', 'delete'], $patient)
                    <span class="mx-1 h-6 w-px bg-aura-gray-light" aria-hidden="true"></span>
                @endcanany

                @can('update', $patient)
                    <x-icon-action :href="route('patients.edit', $patient)" label="Editar paciente">
                        <x-icon name="pencil" />
                    </x-icon-action>
                @endcan

                @can('delete', $patient)
                    <x-icon-action
                        label="Eliminar paciente"
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
            <p class="flex items-start gap-2 rounded-md border border-aura-olive/30 bg-aura-olive/5 px-4 py-2.5 text-sm text-aura-olive" role="status">
                <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0" />
                <span>{{ session('status') }}</span>
            </p>
        @endif

        {{-- Cuerpo del expediente, agrupado por bloques con sentido clínico --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <section aria-labelledby="identification-heading" class="rounded-lg border border-aura-gray-light bg-white p-6">
                    <h2 id="identification-heading" class="mb-4 flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-aura-gray-dark">
                        <span class="h-3 w-0.5 bg-aura-olive" aria-hidden="true"></span>
                        Identificación
                    </h2>
                    <div class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <p class="mb-1 text-xs text-aura-gray">Fecha de nacimiento</p>
                            <p class="text-aura-gray-dark">{{ $patient->birth_date->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs text-aura-gray">Ocupación</p>
                            <p class="text-aura-gray-dark">{{ $patient->occupation ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs text-aura-gray">Estado civil</p>
                            <p class="text-aura-gray-dark">{{ $patient->marital_status ?? '—' }}</p>
                        </div>
                    </div>
                </section>

                <section aria-labelledby="contact-heading" class="rounded-lg border border-aura-gray-light bg-white p-6">
                    <h2 id="contact-heading" class="mb-4 flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-aura-gray-dark">
                        <span class="h-3 w-0.5 bg-aura-olive" aria-hidden="true"></span>
                        Contacto
                    </h2>
                    <div class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <p class="mb-1 text-xs text-aura-gray">Teléfono</p>
                            <p class="text-aura-gray-dark">{{ $patient->phone }}</p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs text-aura-gray">Correo electrónico</p>
                            <p class="text-aura-gray-dark">{{ $patient->email ?? '—' }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="mb-1 text-xs text-aura-gray">Dirección</p>
                            <p class="text-aura-gray-dark">{{ $patient->address ?? '—' }}</p>
                        </div>
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <section aria-labelledby="emergency-heading" class="rounded-lg border border-aura-gray-light bg-white p-6">
                    <h2 id="emergency-heading" class="mb-4 flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-aura-gray-dark">
                        <span class="h-3 w-0.5 bg-aura-olive" aria-hidden="true"></span>
                        Contacto de emergencia
                    </h2>
                    <div class="space-y-4 text-sm">
                        <div>
                            <p class="mb-1 text-xs text-aura-gray">Nombre</p>
                            <p class="text-aura-gray-dark">{{ $patient->emergency_contact_name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs text-aura-gray">Teléfono</p>
                            <p class="text-aura-gray-dark">{{ $patient->emergency_contact_phone ?? '—' }}</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        {{-- Confirmación de eliminación: modal propio de la UI, nunca confirm() del navegador --}}
        @can('delete', $patient)
            <form method="POST" action="{{ route('patients.destroy', $patient) }}" x-ref="deleteForm" class="hidden">
                @csrf
                @method('DELETE')
            </form>

            <x-confirm-modal show="confirmDelete" title-id="delete-patient-title" on-close="closeConfirmDelete()">
                <h2 id="delete-patient-title" class="text-base font-medium text-aura-gray-dark">
                    Eliminar paciente
                </h2>
                <p class="mt-2 text-sm text-aura-gray">
                    Se eliminará el expediente de
                    <span class="font-medium text-aura-gray-dark">{{ $patient->full_name }}</span>.
                    Quedará archivado y se podrá restaurar desde «Ver archivados».
                </p>
                <div class="mt-5 flex justify-end gap-2">
                    <x-button type="button" variant="secondary" x-ref="confirmModalCancel"
                              @click="closeConfirmDelete()"
                              @keydown.tab.prevent="$refs.confirmModalConfirm.focus()">
                        Cancelar
                    </x-button>
                    {{-- Este botón es el "submitter" visible de un <form> distinto y oculto
                         (x-ref="deleteForm", sin botones propios) — button-loader.js no puede
                         detectarlo automáticamente (SubmitEvent.submitter llega vacío en un
                         requestSubmit() sin argumento), así que usa el mecanismo 3 de
                         <x-button> (alpine-loading) en vez del automático. --}}
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
