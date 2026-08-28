<div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-light tracking-tight text-aura-gray-dark">
                {{ $archived ? 'Pacientes archivados' : 'Pacientes' }}
            </h1>
            <p class="mt-1 text-sm text-aura-gray">
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

        {{-- Acción primaria de la pantalla: se mantiene icono + texto (no solo-icono). Un "+"
             aislado en una tabla con muchos otros iconos de fila perdería prominencia y podría
             confundirse con una acción secundaria (ej. "agregar filtro"); el personal de la
             clínica debe reconocer de un vistazo dónde dar de alta un expediente nuevo. --}}
        @can('create', App\Models\Patient::class)
            @unless ($archived)
                <a href="{{ route('patients.create') }}"
                   class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-md bg-aura-olive px-4 py-2 text-sm font-medium text-white transition-opacity motion-reduce:transition-none hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive focus-visible:ring-offset-2 focus-visible:ring-offset-aura-cream">
                    <x-icon name="plus" class="h-4 w-4" />
                    Nuevo paciente
                </a>
            @endunless
        @endcan
    </div>

    @if (session('status'))
        <p class="mt-4 flex items-start gap-2 rounded-md border border-aura-olive/30 bg-aura-olive/5 px-4 py-2.5 text-sm text-aura-olive" role="status">
            <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0" />
            <span>{{ session('status') }}</span>
        </p>
    @endif

    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-sm">
            <label for="patient-search" class="sr-only">Buscar pacientes por nombre o teléfono</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-aura-gray" aria-hidden="true">
                    <x-icon name="magnifying-glass" class="h-4 w-4" />
                </span>
                <input id="patient-search" type="search"
                       wire:model.live.debounce.400ms="search"
                       placeholder="Buscar por nombre o teléfono..."
                       autocomplete="off"
                       class="w-full rounded-md border border-aura-gray-light bg-white py-2 pl-9 pr-9 text-sm text-aura-gray-dark placeholder:text-aura-gray focus:border-aura-olive focus:outline-none focus:ring-1 focus:ring-aura-olive">
                <span wire:loading wire:target="search"
                      class="absolute inset-y-0 right-3 flex items-center text-aura-gray" aria-hidden="true">
                    <svg class="h-4 w-4 animate-spin motion-reduce:animate-none" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"/>
                    </svg>
                </span>
            </div>
        </div>

        <div class="flex items-center gap-1">
            @if (trim($search) !== '')
                <button type="button" wire:click="clearSearch"
                        class="min-h-11 rounded-md px-2 py-1 text-sm text-aura-gray-dark transition-colors motion-reduce:transition-none hover:text-aura-olive focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                    Limpiar
                </button>
            @endif

            <x-icon-action
                wire:click="toggleArchived"
                :label="$archived ? 'Ver pacientes activos' : 'Ver archivados'"
            >
                <x-icon :name="$archived ? 'chevron-left' : 'archive-box'" />
            </x-icon-action>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg border border-aura-gray-light bg-white"
         wire:loading.class="opacity-60" wire:target="search, toggleArchived, clearSearch, nextPage, previousPage, gotoPage">
        @if ($patients->isEmpty())
            <div class="px-4 py-12 text-center">
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
                           class="mt-3 inline-flex min-h-11 items-center gap-1.5 rounded-md px-2 py-1.5 text-sm font-medium text-aura-olive hover:bg-aura-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                            <x-icon name="plus" class="h-4 w-4" />
                            Registrar el primer paciente
                        </a>
                    @endcan
                @endif
            </div>
        @else
            {{-- Vista de tabla: solo desde `lg` (≈1024px), donde el contenido (sidebar 256px +
                 padding del shell) deja suficiente ancho real para las columnas sin generar
                 overflow ni scrollbar horizontal. Por debajo de `lg` (móvil y tablet portrait)
                 se usa la vista de tarjetas de abajo. «Último doctor» y «Próxima consulta» se
                 reservan para `xl` (escritorio/tablet horizontal amplia) para no ajustar de más
                 en `lg` (ej. iPad horizontal, 1024px exactos). --}}
            <div class="hidden lg:block">
                <table class="w-full text-sm">
                    <caption class="sr-only">
                        {{ $archived ? 'Listado de pacientes archivados' : 'Listado de pacientes activos' }}
                    </caption>
                    <thead class="bg-aura-olive text-xs uppercase tracking-wide text-white">
                        <tr>
                            <th scope="col" class="w-12 px-3 py-3"><span class="sr-only">Foto</span></th>
                            <th scope="col" class="px-3 py-3 text-left font-medium">Nombre</th>
                            <th scope="col" class="px-3 py-3 text-left font-medium">Edad</th>
                            <th scope="col" class="px-3 py-3 text-left font-medium">Teléfono</th>
                            <th scope="col" class="hidden px-3 py-3 text-left font-medium xl:table-cell">Último doctor</th>
                            {{-- Placeholder: aún no existe módulo de citas en este sistema (vive aparte,
                                 CLAUDE.md §4/§12). Cuando se integre, esta columna debe leer la próxima
                                 cita agendada del paciente desde ese sistema o desde una futura tabla
                                 local — por ahora no hay dato que mostrar y no debe inventarse uno. --}}
                            <th scope="col" class="hidden px-3 py-3 text-left font-medium xl:table-cell">Próxima consulta</th>
                            <th scope="col" class="px-3 py-3 text-right font-medium"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-aura-gray-light">
                        @foreach ($patients as $patient)
                            <tr class="transition-colors motion-reduce:transition-none hover:bg-aura-cream/60" wire:key="patient-row-{{ $patient->id }}">
                                <td class="px-3 py-3">
                                    <x-patient-avatar :patient="$patient" size="sm" />
                                </td>
                                <td class="px-3 py-3">
                                    @if ($archived)
                                        <span class="font-medium text-aura-gray-dark">{{ $patient->full_name }}</span>
                                    @else
                                        <a href="{{ route('patients.show', $patient) }}"
                                           class="rounded font-medium text-aura-gray-dark underline-offset-2 hover:text-aura-olive hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                                            {{ $patient->full_name }}
                                        </a>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-aura-gray-dark">{{ $patient->age }}</td>
                                <td class="whitespace-nowrap px-3 py-3 text-aura-gray-dark">{{ $patient->phone }}</td>
                                <td class="hidden px-3 py-3 text-aura-gray-dark xl:table-cell">
                                    {{ $patient->latestConsultation?->doctor?->name ?? '—' }}
                                </td>
                                <td class="hidden px-3 py-3 xl:table-cell">
                                    <span class="text-aura-gray" title="Aún no disponible: se integrará con el sistema de citas en línea.">
                                        <span aria-hidden="true">—</span>
                                        <span class="sr-only">Próxima consulta aún no disponible</span>
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-right">
                                    @if ($archived)
                                        @can('restore', $patient)
                                            <x-icon-action
                                                wire:click="restore({{ $patient->id }})"
                                                :label="'Restaurar el expediente de ' . $patient->full_name"
                                            >
                                                <x-icon name="arrow-uturn-left" />
                                            </x-icon-action>
                                        @endcan
                                    @else
                                        <x-icon-action
                                            :href="route('patients.show', $patient)"
                                            :label="'Ver expediente de ' . $patient->full_name"
                                        >
                                            <x-icon name="eye" />
                                        </x-icon-action>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Vista de tarjetas: por debajo de `lg` (móvil y tablet en vertical). Mismos datos
                 que la tabla, reflujados en una sola columna legible sin scroll horizontal. --}}
            <ul role="list" class="divide-y divide-aura-gray-light lg:hidden">
                @foreach ($patients as $patient)
                    <li class="flex items-center gap-3 px-4 py-4" wire:key="patient-card-{{ $patient->id }}">
                        <x-patient-avatar :patient="$patient" size="sm" />

                        <div class="min-w-0 flex-1">
                            @if ($archived)
                                <p class="truncate font-medium text-aura-gray-dark">{{ $patient->full_name }}</p>
                            @else
                                <a href="{{ route('patients.show', $patient) }}"
                                   class="block truncate rounded font-medium text-aura-gray-dark underline-offset-2 hover:text-aura-olive hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                                    {{ $patient->full_name }}
                                </a>
                            @endif

                            <dl class="mt-1 flex flex-wrap gap-x-3 gap-y-0.5 text-xs text-aura-gray">
                                <div class="flex gap-1">
                                    <dt class="sr-only">Edad</dt>
                                    <dd>{{ $patient->age }} años</dd>
                                </div>
                                <div class="flex gap-1">
                                    <dt class="sr-only">Teléfono</dt>
                                    <dd>{{ $patient->phone }}</dd>
                                </div>
                                <div class="flex gap-1">
                                    <dt class="sr-only">Último doctor</dt>
                                    <dd>{{ $patient->latestConsultation?->doctor?->name ?? '—' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="shrink-0">
                            @if ($archived)
                                @can('restore', $patient)
                                    <x-icon-action
                                        wire:click="restore({{ $patient->id }})"
                                        :label="'Restaurar el expediente de ' . $patient->full_name"
                                    >
                                        <x-icon name="arrow-uturn-left" />
                                    </x-icon-action>
                                @endcan
                            @else
                                <x-icon-action
                                    :href="route('patients.show', $patient)"
                                    :label="'Ver expediente de ' . $patient->full_name"
                                >
                                    <x-icon name="eye" />
                                </x-icon-action>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    @if ($patients->hasPages())
        <div class="mt-4">
            {{ $patients->links() }}
        </div>
    @endif
</div>
