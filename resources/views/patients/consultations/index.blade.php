{{-- El nombre del paciente no va en el <title> del navegador (historial, pestañas, screen-share): CLAUDE.md §5. --}}
<x-app-layout title="Consultas">
    <div class="space-y-8">
        <a href="{{ route('patients.show', $patient) }}"
           class="inline-flex min-h-11 items-center gap-1.5 rounded text-sm text-aura-gray-dark transition-colors motion-reduce:transition-none hover:text-aura-olive focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
            <x-icon name="chevron-left" class="h-4 w-4" />
            Volver a la ficha del paciente
        </a>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-light tracking-tight text-aura-gray-dark">Consultas</h1>
                <p class="mt-1 text-sm text-aura-gray">{{ $patient->full_name }}</p>
            </div>

            {{-- Acción primaria de la pantalla: icono + texto (no solo-icono), igual criterio
                 que "Nuevo paciente" en el listado de pacientes. --}}
            @can('create', App\Models\Consultation::class)
                <a href="{{ route('patients.consultations.create', $patient) }}"
                   class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-md bg-aura-olive px-4 py-2 text-sm font-medium text-white transition-opacity motion-reduce:transition-none hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive focus-visible:ring-offset-2 focus-visible:ring-offset-aura-cream">
                    <x-icon name="plus" class="h-4 w-4" />
                    Nueva consulta
                </a>
            @endcan
        </div>

        @if (session('status'))
            <p class="flex items-start gap-2 rounded-md border border-aura-olive/30 bg-aura-olive/5 px-4 py-2.5 text-sm text-aura-olive" role="status">
                <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0" />
                <span>{{ session('status') }}</span>
            </p>
        @endif

        <div class="overflow-hidden rounded-lg border border-aura-gray-light bg-white">
            @if ($consultations->isEmpty())
                <div class="px-4 py-12 text-center">
                    <p class="text-sm text-aura-gray">Este paciente aún no tiene consultas registradas.</p>
                    @can('create', App\Models\Consultation::class)
                        <a href="{{ route('patients.consultations.create', $patient) }}"
                           class="mt-3 inline-flex min-h-11 items-center gap-1.5 rounded-md px-2 py-1.5 text-sm font-medium text-aura-olive hover:bg-aura-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                            <x-icon name="plus" class="h-4 w-4" />
                            Registrar la primera consulta
                        </a>
                    @endcan
                </div>
            @else
                {{-- Vista de tabla: solo desde `lg`, igual criterio que el listado de pacientes.
                     Con solo tres columnas de datos no hace falta reservar ninguna para `xl`. --}}
                <div class="hidden lg:block">
                    <table class="w-full text-sm">
                        <caption class="sr-only">Historial de consultas del paciente</caption>
                        <thead class="bg-aura-olive text-xs uppercase tracking-wide text-white">
                            <tr>
                                <th scope="col" class="px-3 py-3 text-left font-medium">Fecha</th>
                                <th scope="col" class="px-3 py-3 text-left font-medium">Doctor</th>
                                <th scope="col" class="px-3 py-3 text-left font-medium">Diagnóstico</th>
                                <th scope="col" class="px-3 py-3 text-right font-medium"><span class="sr-only">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-aura-gray-light">
                            @foreach ($consultations as $consultation)
                                <tr class="transition-colors motion-reduce:transition-none hover:bg-aura-cream/60">
                                    <td class="whitespace-nowrap px-3 py-3 text-aura-gray-dark">{{ $consultation->consultation_date->format('d/m/Y') }}</td>
                                    <td class="whitespace-nowrap px-3 py-3 text-aura-gray-dark">{{ $consultation->doctor?->name ?? '—' }}</td>
                                    <td class="px-3 py-3">
                                        {{-- Diagnóstico: texto clínico libre, puede ser largo. Se recorta solo
                                             visualmente (CSS truncate) sin perder el dato real del DOM ni
                                             exponerlo en un title/aria-label (sería PHI innecesaria ahí). --}}
                                        <span class="block max-w-xs truncate text-aura-gray-dark">{{ $consultation->clinical_diagnosis }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        <x-icon-action
                                            :href="route('consultations.show', $consultation)"
                                            :label="'Ver la consulta del ' . $consultation->consultation_date->format('d/m/Y')"
                                        >
                                            <x-icon name="eye" />
                                        </x-icon-action>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Vista de tarjetas: por debajo de `lg`. Mismos datos, una sola columna legible,
                     sin scroll horizontal. --}}
                <ul role="list" class="divide-y divide-aura-gray-light lg:hidden">
                    @foreach ($consultations as $consultation)
                        <li class="flex items-start justify-between gap-3 px-4 py-4">
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-aura-gray-dark">{{ $consultation->consultation_date->format('d/m/Y') }}</p>
                                <dl class="mt-1 space-y-1 text-xs text-aura-gray">
                                    <div class="flex gap-1">
                                        <dt class="sr-only">Doctor</dt>
                                        <dd>{{ $consultation->doctor?->name ?? '—' }}</dd>
                                    </div>
                                    <div class="flex gap-1">
                                        <dt class="sr-only">Diagnóstico</dt>
                                        <dd class="truncate">{{ $consultation->clinical_diagnosis }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="shrink-0">
                                <x-icon-action
                                    :href="route('consultations.show', $consultation)"
                                    :label="'Ver la consulta del ' . $consultation->consultation_date->format('d/m/Y')"
                                >
                                    <x-icon name="eye" />
                                </x-icon-action>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if ($consultations->hasPages())
            <div>
                {{ $consultations->links() }}
            </div>
        @endif

        @if ($archivedConsultations->isNotEmpty())
            <section aria-labelledby="archived-consultations-heading">
                <h2 id="archived-consultations-heading" class="text-base font-medium text-aura-gray-dark">
                    Consultas archivadas
                </h2>
                <p class="mt-1 text-sm text-aura-gray">
                    Restaurar una consulta la devuelve al historial activo del paciente.
                </p>

                <div class="mt-3 overflow-hidden rounded-lg border border-aura-gray-light bg-white">
                    <div class="hidden lg:block">
                        <table class="w-full text-sm">
                            <caption class="sr-only">Historial de consultas archivadas del paciente</caption>
                            <thead class="bg-aura-olive text-xs uppercase tracking-wide text-white">
                                <tr>
                                    <th scope="col" class="px-3 py-3 text-left font-medium">Fecha</th>
                                    <th scope="col" class="px-3 py-3 text-left font-medium">Doctor</th>
                                    <th scope="col" class="px-3 py-3 text-left font-medium">Diagnóstico</th>
                                    <th scope="col" class="px-3 py-3 text-right font-medium"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-aura-gray-light">
                                @foreach ($archivedConsultations as $consultation)
                                    <tr class="text-aura-gray transition-colors motion-reduce:transition-none hover:bg-aura-cream/60">
                                        <td class="whitespace-nowrap px-3 py-3">{{ $consultation->consultation_date->format('d/m/Y') }}</td>
                                        <td class="whitespace-nowrap px-3 py-3">{{ $consultation->doctor?->name ?? '—' }}</td>
                                        <td class="px-3 py-3">
                                            <span class="block max-w-xs truncate">{{ $consultation->clinical_diagnosis }}</span>
                                        </td>
                                        <td class="px-3 py-3 text-right">
                                            @can('restore', $consultation)
                                                <form method="POST" action="{{ route('consultations.restore', $consultation) }}" class="inline-block">
                                                    @csrf
                                                    @method('PUT')
                                                    <x-icon-action
                                                        onclick="this.closest('form').requestSubmit()"
                                                        :label="'Restaurar la consulta del ' . $consultation->consultation_date->format('d/m/Y')"
                                                    >
                                                        <x-icon name="arrow-uturn-left" />
                                                    </x-icon-action>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <ul role="list" class="divide-y divide-aura-gray-light lg:hidden">
                        @foreach ($archivedConsultations as $consultation)
                            <li class="flex items-start justify-between gap-3 px-4 py-4 text-aura-gray">
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium">{{ $consultation->consultation_date->format('d/m/Y') }}</p>
                                    <dl class="mt-1 space-y-1 text-xs">
                                        <div class="flex gap-1">
                                            <dt class="sr-only">Doctor</dt>
                                            <dd>{{ $consultation->doctor?->name ?? '—' }}</dd>
                                        </div>
                                        <div class="flex gap-1">
                                            <dt class="sr-only">Diagnóstico</dt>
                                            <dd class="truncate">{{ $consultation->clinical_diagnosis }}</dd>
                                        </div>
                                    </dl>
                                </div>

                                <div class="shrink-0">
                                    @can('restore', $consultation)
                                        <form method="POST" action="{{ route('consultations.restore', $consultation) }}" class="inline-block">
                                            @csrf
                                            @method('PUT')
                                            <x-icon-action
                                                onclick="this.closest('form').requestSubmit()"
                                                :label="'Restaurar la consulta del ' . $consultation->consultation_date->format('d/m/Y')"
                                            >
                                                <x-icon name="arrow-uturn-left" />
                                            </x-icon-action>
                                        </form>
                                    @endcan
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
