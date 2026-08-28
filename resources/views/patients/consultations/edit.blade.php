{{-- El nombre del paciente no va en el <title> del navegador (historial, pestañas, screen-share): CLAUDE.md §5. --}}
<x-app-layout title="Editar consulta">
    <div class="max-w-3xl">
        <h1 class="text-lg font-medium mb-1">Editar consulta</h1>
        <p class="text-sm text-aura-gray mb-6">
            {{ $patient->full_name }} · {{ $consultation->consultation_date->format('d/m/Y') }}
        </p>

        <form method="POST" action="{{ route('consultations.update', $consultation) }}" class="space-y-6">
            @csrf
            @method('PUT')

            @include('patients.consultations._form')

            <div class="flex items-center gap-3">
                <x-button type="submit" variant="primary">
                    Guardar cambios
                </x-button>
                <a href="{{ route('consultations.show', $consultation) }}" class="text-sm text-aura-gray hover:text-aura-gray-dark">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
