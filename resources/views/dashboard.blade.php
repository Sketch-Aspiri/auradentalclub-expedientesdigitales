<x-app-layout title="Inicio">
    {{-- Banner de bienvenida --}}
    <section class="relative overflow-hidden rounded-lg bg-aura-olive px-6 py-8 text-aura-cream sm:px-8">
        <img src="{{ asset('logos/monograma.png') }}" alt="" aria-hidden="true"
             class="pointer-events-none absolute -bottom-10 -right-8 w-48 select-none opacity-10 brightness-0 invert">

        <p class="relative text-sm">
            {{ ucfirst(now()->locale('es')->translatedFormat('l, d \d\e F')) }}
        </p>

        <h1 class="relative mt-1 text-2xl font-light">
            Hola, {{ auth()->user()->name }}
        </h1>

        <p class="relative mt-2 text-sm">
            Sesión iniciada como
            <span class="ml-1 inline-flex items-center rounded-full border border-aura-cream/40 px-2 py-0.5 text-xs font-medium">
                {{ auth()->user()->role->label() }}
            </span>
        </p>

        <a href="{{ route('patients.index') }}"
           class="relative mt-5 inline-flex items-center gap-2 rounded bg-aura-cream px-4 py-2 text-sm font-medium text-aura-olive transition-opacity motion-reduce:transition-none hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-cream focus-visible:ring-offset-2 focus-visible:ring-offset-aura-olive">
            Ver pacientes
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/>
            </svg>
        </a>
    </section>

    {{-- Módulos --}}
    <section aria-labelledby="modules-heading" class="mt-8">
        <h2 id="modules-heading" class="text-xs font-medium uppercase tracking-wide text-aura-gray">
            Módulos
        </h2>

        <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <a href="{{ route('patients.index') }}"
               class="group rounded-lg border border-aura-gray-light bg-white p-5 transition-colors motion-reduce:transition-none hover:border-aura-olive focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                <span class="flex h-9 w-9 items-center justify-center rounded bg-aura-olive text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1M9.5 10.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM17 10.5a3 3 0 0 0-1.7-5.4M21 19v-1a4 4 0 0 0-3-3.85"/>
                    </svg>
                </span>
                <span class="mt-3 flex items-center gap-2">
                    <span class="font-medium text-aura-gray-dark">Pacientes</span>
                    <svg class="h-4 w-4 text-aura-gray transition-transform motion-reduce:transition-none group-hover:translate-x-0.5 group-hover:text-aura-olive" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/>
                    </svg>
                </span>
                <span class="mt-1 block text-sm text-aura-gray">
                    Expedientes, ficha de identificación, historia clínica y consultas.
                </span>
            </a>

            <a href="{{ route('odontogram') }}"
               class="group rounded-lg border border-aura-gray-light bg-white p-5 transition-colors motion-reduce:transition-none hover:border-aura-olive focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive">
                <span class="flex h-9 w-9 items-center justify-center rounded bg-aura-olive text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 4.5c-1.6 0-2.6.9-4 .9-1.6 0-3-1-4.3.3C2.3 7 2.6 9.6 3.3 12.6c.4 1.7.6 3.4 1 5 .4 1.7 1 2.9 2 2.9 1.3 0 1.7-1.7 2-3.4.3-1.6.6-3 1.7-3s1.4 1.4 1.7 3c.3 1.7.7 3.4 2 3.4 1 0 1.6-1.2 2-2.9.4-1.6.6-3.3 1-5 .7-3 1-5.6-.4-6.9-1.3-1.3-2.7-.3-4.3-.3-1.4 0-2.4-.9-4-.9Z"/>
                    </svg>
                </span>
                <span class="mt-3 flex items-center gap-2">
                    <span class="font-medium text-aura-gray-dark">Odontograma</span>
                    <svg class="h-4 w-4 text-aura-gray transition-transform motion-reduce:transition-none group-hover:translate-x-0.5 group-hover:text-aura-olive" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/>
                    </svg>
                </span>
                <span class="mt-1 block text-sm text-aura-gray">
                    Busca un paciente y consulta o edita su odontograma: estado por diente y superficie en notación FDI, con historial.
                </span>
            </a>

            @foreach ([
                ['name' => 'Consentimientos', 'desc' => 'Formatos informados con procedimientos, costos y firma digital.'],
                ['name' => 'Hoja de evolución', 'desc' => 'Procedimientos, materiales y costos registrados por cita.'],
                ['name' => 'Carga de archivos', 'desc' => 'Radiografías, fotografías y documentos del expediente.'],
            ] as $module)
                <div class="rounded-lg border border-dashed border-aura-gray-light bg-white p-5" aria-disabled="true">
                    <span class="flex h-9 w-9 items-center justify-center rounded border border-aura-gray-light text-aura-gray">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 10V7a5 5 0 0 1 10 0v3M5 10h14v10H5z"/>
                        </svg>
                    </span>
                    <span class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="font-medium text-aura-gray-dark">{{ $module['name'] }}</span>
                        <span class="rounded-full bg-aura-cream px-2 py-0.5 text-[11px] font-medium uppercase tracking-wide text-aura-gray-dark">
                            Próximamente
                        </span>
                    </span>
                    <span class="mt-1 block text-sm text-aura-gray">{{ $module['desc'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Actividad reciente (estado inicial — se poblará en una iteración con datos) --}}
    <section aria-labelledby="activity-heading" class="mt-8">
        <h2 id="activity-heading" class="text-xs font-medium uppercase tracking-wide text-aura-gray">
            Actividad reciente
        </h2>

        <div class="mt-3 rounded-lg border border-aura-gray-light bg-white px-6 py-12">
            <div class="mx-auto flex max-w-sm flex-col items-center text-center">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-aura-cream text-aura-gray" aria-hidden="true">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </span>
                <p class="mt-3 text-sm font-medium text-aura-gray-dark">Aún no hay actividad para mostrar</p>
                <p class="mt-1 text-sm text-aura-gray">
                    Aquí verás los últimos pacientes y consultas registrados cuando el módulo de actividad esté disponible.
                </p>
            </div>
        </div>
    </section>

    <p class="mt-6 text-xs text-aura-gray">
        Los módulos marcados como «Próximamente» se habilitarán en los siguientes sprints.
    </p>
</x-app-layout>
