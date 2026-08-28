<div>
    <div class="mb-6">
        <h1 class="text-lg font-medium text-aura-gray-dark">Consentimientos</h1>
        <p class="mt-1 text-sm text-aura-gray">Busca un paciente para ver o crear sus consentimientos informados.</p>
    </div>

    <div class="mx-auto max-w-lg rounded-lg border border-aura-gray-light bg-white p-5 sm:p-6">
        <label for="consent-patient-search" class="block text-sm font-medium text-aura-gray-dark">
            Paciente
        </label>
        <div class="relative mt-1.5">
            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-aura-gray" aria-hidden="true">
                <x-icon name="magnifying-glass" class="h-4 w-4" />
            </span>
            <input id="consent-patient-search" type="search" autocomplete="off"
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
</div>
