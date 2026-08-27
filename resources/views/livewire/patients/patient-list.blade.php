<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-lg font-medium">{{ $archived ? 'Pacientes archivados' : 'Pacientes' }}</h1>
            <p class="mt-1 text-sm text-aura-gray-dark">
                @if ($archived)
                    Expedientes archivados. Restaurar uno lo devuelve a la lista de pacientes activos.
                @else
                    {{ $patients->total() }} {{ $patients->total() === 1 ? 'expediente' : 'expedientes' }}
                    @if (trim($search) !== '')
                        que coinciden con «{{ trim($search) }}»
                    @endif
                @endif
            </p>
        </div>

        @can('create', App\Models\Patient::class)
            @unless ($archived)
                <a href="{{ route('patients.create') }}"
                   class="inline-flex shrink-0 items-center justify-center rounded bg-aura-olive px-4 py-2 text-sm font-medium text-white transition-opacity hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive focus-visible:ring-offset-2 focus-visible:ring-offset-aura-cream">
                    Nuevo paciente
                </a>
            @endunless
        @endcan
    </div>

    @if (session('status'))
        <p role="status" class="mt-4 text-sm text-aura-olive">{{ session('status') }}</p>
    @endif

    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-sm">
            <label for="patient-search" class="sr-only">Buscar pacientes por nombre o teléfono</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-aura-gray" aria-hidden="true">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75">
                        <circle cx="9" cy="9" r="6"/><path stroke-linecap="round" d="m14 14 3.5 3.5"/>
                    </svg>
                </span>
                <input id="patient-search" type="search"
                       wire:model.live.debounce.400ms="search"
                       placeholder="Buscar por nombre o teléfono..."
                       autocomplete="off"
                       class="w-full rounded border border-aura-gray-light bg-white py-2 pl-9 pr-9 text-sm text-aura-gray-dark placeholder:text-aura-gray focus:border-aura-olive focus:outline-none focus:ring-1 focus:ring-aura-olive">
                <span wire:loading wire:target="search"
                      class="absolute inset-y-0 right-3 flex items-center text-aura-gray" aria-hidden="true">
                    <svg class="h-4 w-4 animate-spin motion-reduce:animate-none" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"/>
                    </svg>
                </span>
            </div>
        </div>

        <div class="flex items-center gap-4 text-sm">
            @if (trim($search) !== '')
                <button type="button" wire:click="clearSearch"
                        class="rounded px-1 py-1 text-aura-gray-dark hover:text-aura-olive focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                    Limpiar
                </button>
            @endif
            <button type="button" wire:click="toggleArchived"
                    class="whitespace-nowrap rounded px-1 py-1 text-aura-gray-dark hover:text-aura-olive focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                {{ $archived ? '← Ver pacientes activos' : 'Ver archivados' }}
            </button>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg border border-aura-gray-light bg-white"
         wire:loading.class="opacity-60" wire:target="search, toggleArchived, clearSearch, nextPage, previousPage, gotoPage">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[38rem] text-sm">
                <caption class="sr-only">
                    {{ $archived ? 'Listado de pacientes archivados' : 'Listado de pacientes activos' }}
                </caption>
                <thead class="border-b border-aura-gray-light bg-aura-cream text-xs uppercase tracking-wide text-aura-gray-dark">
                    <tr>
                        <th scope="col" class="w-14 px-4 py-3"><span class="sr-only">Foto</span></th>
                        <th scope="col" class="px-4 py-3 text-left font-medium">Nombre</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium">Edad</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium">Teléfono</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium"><span class="sr-only">Acciones</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-aura-gray-light">
                    @forelse ($patients as $patient)
                        <tr class="transition-colors hover:bg-aura-cream/60" wire:key="patient-{{ $patient->id }}">
                            <td class="px-4 py-3">
                                <x-patient-avatar :patient="$patient" size="sm" />
                            </td>
                            <td class="px-4 py-3">
                                @if ($archived)
                                    <span class="font-medium text-aura-gray-dark">{{ $patient->full_name }}</span>
                                @else
                                    <a href="{{ route('patients.show', $patient) }}"
                                       class="rounded font-medium text-aura-gray-dark underline-offset-2 hover:text-aura-olive hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                                        {{ $patient->full_name }}
                                    </a>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-aura-gray-dark">{{ $patient->age }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-aura-gray-dark">{{ $patient->phone }}</td>
                            <td class="px-4 py-3 text-right">
                                @if ($archived)
                                    @can('restore', $patient)
                                        <button type="button" wire:click="restore({{ $patient->id }})"
                                                class="inline-flex items-center rounded px-2 py-1.5 font-medium text-aura-olive hover:bg-aura-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                                            Restaurar
                                        </button>
                                    @endcan
                                @else
                                    <a href="{{ route('patients.show', $patient) }}"
                                       class="inline-flex items-center gap-1 rounded px-2 py-1.5 font-medium text-aura-olive hover:bg-aura-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                                        Ver <span aria-hidden="true">→</span>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <p class="text-sm text-aura-gray">
                                    @if (trim($search) !== '')
                                        No se encontraron pacientes para «{{ trim($search) }}».
                                    @elseif ($archived)
                                        No hay pacientes archivados.
                                    @else
                                        Aún no hay pacientes registrados.
                                    @endif
                                </p>
                                @if (trim($search) === '' && ! $archived)
                                    @can('create', App\Models\Patient::class)
                                        <a href="{{ route('patients.create') }}"
                                           class="mt-3 inline-flex items-center rounded px-2 py-1.5 text-sm font-medium text-aura-olive hover:bg-aura-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                                            Registrar el primer paciente
                                        </a>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($patients->hasPages())
        <div class="mt-4">
            {{ $patients->links() }}
        </div>
    @endif
</div>
