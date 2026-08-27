{{-- El nombre del paciente no va en el <title> del navegador (historial, pestañas, screen-share): CLAUDE.md §5. --}}
<x-app-layout title="Odontograma">
    <div class="mb-8 flex flex-wrap items-start justify-between gap-x-4 gap-y-2">
        <div>
            <h1 class="text-lg font-medium text-aura-gray-dark">Odontograma</h1>
            <p class="mt-0.5 text-sm text-aura-gray">{{ $patient->full_name }}</p>
        </div>

        <a href="{{ route('patients.show', $patient) }}"
           class="inline-flex min-h-6 items-center rounded text-sm text-aura-gray-dark transition-colors hover:text-aura-olive focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-aura-olive focus-visible:ring-offset-2">
            <span aria-hidden="true" class="mr-1">&larr;</span> Volver a la ficha del paciente
        </a>
    </div>

    <livewire:patients.odontogram :patient="$patient" />
</x-app-layout>
