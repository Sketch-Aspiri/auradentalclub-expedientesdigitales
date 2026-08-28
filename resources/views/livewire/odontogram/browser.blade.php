<div>
    @if ($this->selectedPatient)
        @php $patient = $this->selectedPatient; @endphp

        <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-center gap-3">
                <x-patient-avatar :patient="$patient" size="md" />
                <div>
                    <h1 class="text-lg font-medium text-aura-gray-dark">Odontograma</h1>
                    <p class="text-sm text-aura-gray">
                        {{ $patient->full_name }}
                        <span class="text-aura-gray-light">·</span>
                        {{ $patient->age }} años
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('patients.show', $patient) }}"
                   class="rounded px-1 py-1 text-aura-gray-dark hover:text-aura-olive focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                    Ver expediente
                </a>
                <button type="button" wire:click="clearPatient" wire:target="clearPatient" wire:loading.attr="disabled"
                        class="rounded px-1 py-1 text-aura-gray-dark hover:text-aura-olive focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive disabled:cursor-not-allowed disabled:opacity-60">
                    Cambiar paciente
                </button>
            </div>
        </div>

        <livewire:patients.odontogram :patient="$patient" :key="'odontogram-'.$patient->getKey()" />
    @else
        <div class="mb-6">
            <h1 class="text-lg font-medium text-aura-gray-dark">Odontograma</h1>
            <p class="mt-1 text-sm text-aura-gray">Busca un paciente para ver o editar su odontograma.</p>
        </div>

        <div class="mx-auto max-w-lg rounded-lg border border-aura-gray-light bg-white p-5 sm:p-6">
            <label for="odontogram-patient-search" class="block text-sm font-medium text-aura-gray-dark">
                Paciente
            </label>
            <div class="relative mt-1.5">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-aura-gray" aria-hidden="true">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75">
                        <circle cx="9" cy="9" r="6"/><path stroke-linecap="round" d="m14 14 3.5 3.5"/>
                    </svg>
                </span>
                <input id="odontogram-patient-search" type="search" autocomplete="off"
                       wire:model.live.debounce.350ms="search"
                       placeholder="Nombre o teléfono del paciente..."
                       class="w-full rounded border border-aura-gray-light bg-white py-2 pl-9 pr-9 text-sm text-aura-gray-dark placeholder:text-aura-gray focus:border-aura-olive focus:outline-none focus:ring-1 focus:ring-aura-olive">
                <span wire:loading wire:target="search" class="absolute inset-y-0 right-3 flex items-center text-aura-gray" aria-hidden="true">
                    <svg class="h-4 w-4 animate-spin motion-reduce:animate-none" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"/>
                    </svg>
                </span>
            </div>

            @if (trim($search) !== '')
                <ul class="mt-3 divide-y divide-aura-gray-light overflow-hidden rounded border border-aura-gray-light">
                    @forelse ($this->results as $result)
                        <li wire:key="result-{{ $result->id }}">
                            <button type="button" wire:click="selectPatient({{ $result->id }})"
                                    class="flex w-full items-center gap-3 px-3 py-2.5 text-left transition-colors hover:bg-aura-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-aura-olive">
                                <x-patient-avatar :patient="$result" size="sm" />
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-medium text-aura-gray-dark">{{ $result->full_name }}</span>
                                    <span class="block text-xs text-aura-gray">{{ $result->age }} años · {{ $result->phone }}</span>
                                </span>
                            </button>
                        </li>
                    @empty
                        <li class="px-3 py-3 text-sm text-aura-gray">No se encontraron pacientes para «{{ trim($search) }}».</li>
                    @endforelse
                </ul>
            @endif
        </div>
    @endif
</div>
