@php
    use App\Models\Consent;

    $statusChip = fn (Consent $c) => '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium '
        .$c->status()->badgeClasses().'">'.e($c->status()->label()).'</span>';
@endphp

{{-- El nombre del paciente no va en el <title> del navegador (historial, pestañas, screen-share): CLAUDE.md §5. --}}
<x-app-layout title="Consentimientos">
    <div class="space-y-8">
        <a href="{{ route('patients.show', $patient) }}"
           class="inline-flex min-h-11 items-center gap-1.5 rounded text-sm text-aura-gray-dark transition-colors motion-reduce:transition-none hover:text-aura-olive focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
            <x-icon name="chevron-left" class="h-4 w-4" />
            Volver a la ficha del paciente
        </a>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-light tracking-tight text-aura-gray-dark">Consentimientos</h1>
                <p class="mt-1 text-sm text-aura-gray">{{ $patient->full_name }}</p>
            </div>

            @can('create', App\Models\Consent::class)
                <a href="{{ route('patients.consents.create', $patient) }}"
                   class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-md bg-aura-olive px-4 py-2 text-sm font-medium text-white transition-opacity motion-reduce:transition-none hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive focus-visible:ring-offset-2 focus-visible:ring-offset-aura-cream">
                    <x-icon name="plus" class="h-4 w-4" />
                    Nuevo consentimiento
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
            @if ($consents->isEmpty())
                <div class="px-4 py-12 text-center">
                    <p class="text-sm text-aura-gray">Este paciente aún no tiene consentimientos registrados.</p>
                    @can('create', App\Models\Consent::class)
                        <a href="{{ route('patients.consents.create', $patient) }}"
                           class="mt-3 inline-flex min-h-11 items-center gap-1.5 rounded-md px-2 py-1.5 text-sm font-medium text-aura-olive hover:bg-aura-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                            <x-icon name="plus" class="h-4 w-4" />
                            Registrar el primer consentimiento
                        </a>
                    @endcan
                </div>
            @else
                <div class="hidden lg:block">
                    <table class="w-full text-sm">
                        <caption class="sr-only">Historial de consentimientos del paciente</caption>
                        <thead class="bg-aura-olive text-xs uppercase tracking-wide text-white">
                            <tr>
                                <th scope="col" class="px-3 py-3 text-left font-medium">Fecha</th>
                                <th scope="col" class="px-3 py-3 text-left font-medium">Tipo</th>
                                <th scope="col" class="px-3 py-3 text-left font-medium">Estado</th>
                                <th scope="col" class="px-3 py-3 text-left font-medium">Doctor</th>
                                <th scope="col" class="px-3 py-3 text-left font-medium">Tratamiento propuesto</th>
                                <th scope="col" class="px-3 py-3 text-right font-medium"><span class="sr-only">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-aura-gray-light">
                            @foreach ($consents as $consent)
                                <tr class="transition-colors motion-reduce:transition-none hover:bg-aura-cream/60">
                                    <td class="whitespace-nowrap px-3 py-3 text-aura-gray-dark">{{ $consent->created_at->format('d/m/Y') }}</td>
                                    <td class="whitespace-nowrap px-3 py-3 text-aura-gray-dark">{{ $consent->type->label() }}</td>
                                    <td class="whitespace-nowrap px-3 py-3">{!! $statusChip($consent) !!}</td>
                                    <td class="whitespace-nowrap px-3 py-3 text-aura-gray-dark">{{ $consent->doctor?->name ?? '—' }}</td>
                                    <td class="px-3 py-3">
                                        {{-- Texto clínico libre: se recorta solo visualmente (CSS), sin exponerlo en un title/aria-label. --}}
                                        <span class="block max-w-xs truncate text-aura-gray-dark">{{ $consent->treatment_plan }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <x-icon-action :href="route('consents.show', $consent)" label="Ver el consentimiento">
                                                <x-icon name="eye" />
                                            </x-icon-action>
                                            @can('sign', $consent)
                                                <x-icon-action :href="route('consents.sign', $consent)" label="Firmar el consentimiento">
                                                    <x-icon name="signature" />
                                                </x-icon-action>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <ul role="list" class="divide-y divide-aura-gray-light lg:hidden">
                    @foreach ($consents as $consent)
                        <li class="flex items-start justify-between gap-3 px-4 py-4">
                            <div class="min-w-0 flex-1">
                                <p class="flex items-center gap-2 font-medium text-aura-gray-dark">
                                    {{ $consent->type->label() }}
                                    {!! $statusChip($consent) !!}
                                </p>
                                <dl class="mt-1 space-y-1 text-xs text-aura-gray">
                                    <div><dt class="sr-only">Fecha</dt><dd>{{ $consent->created_at->format('d/m/Y') }}</dd></div>
                                    <div><dt class="sr-only">Doctor</dt><dd>{{ $consent->doctor?->name ?? '—' }}</dd></div>
                                    <div><dt class="sr-only">Tratamiento propuesto</dt><dd class="truncate">{{ $consent->treatment_plan }}</dd></div>
                                </dl>
                            </div>
                            <div class="flex shrink-0 items-center gap-1">
                                <x-icon-action :href="route('consents.show', $consent)" label="Ver el consentimiento">
                                    <x-icon name="eye" />
                                </x-icon-action>
                                @can('sign', $consent)
                                    <x-icon-action :href="route('consents.sign', $consent)" label="Firmar el consentimiento">
                                        <x-icon name="signature" />
                                    </x-icon-action>
                                @endcan
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if ($consents->hasPages())
            <div>{{ $consents->links() }}</div>
        @endif

        @if ($archivedConsents->isNotEmpty())
            <section aria-labelledby="archived-consents-heading">
                <h2 id="archived-consents-heading" class="text-base font-medium text-aura-gray-dark">
                    Consentimientos archivados
                </h2>
                <p class="mt-1 text-sm text-aura-gray">
                    Restaurar un consentimiento lo devuelve al historial activo del paciente.
                </p>

                <ul role="list" class="mt-3 divide-y divide-aura-gray-light overflow-hidden rounded-lg border border-aura-gray-light bg-white">
                    @foreach ($archivedConsents as $consent)
                        <li class="flex items-start justify-between gap-3 px-4 py-4 text-aura-gray">
                            <div class="min-w-0 flex-1">
                                <p class="font-medium">{{ $consent->type->label() }} · {{ $consent->created_at->format('d/m/Y') }}</p>
                                <p class="mt-1 text-xs">{{ $consent->doctor?->name ?? '—' }}</p>
                            </div>
                            <div class="shrink-0">
                                @can('restore', $consent)
                                    <form method="POST" action="{{ route('consents.restore', $consent) }}" class="inline-block">
                                        @csrf
                                        @method('PUT')
                                        <x-icon-action onclick="this.closest('form').requestSubmit()" label="Restaurar el consentimiento">
                                            <x-icon name="arrow-uturn-left" />
                                        </x-icon-action>
                                    </form>
                                @endcan
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</x-app-layout>
